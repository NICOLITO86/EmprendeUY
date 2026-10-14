<?php
// Códigos de un solo uso (6 dígitos) para verificación de correo y recuperación de contraseña.
//
// Reglas (ver requerimientos no funcionales):
//  - El código se guarda hasheado (password_hash), nunca en texto plano.
//  - Vence a los CODIGO_VIGENCIA_MIN minutos y se puede usar una sola vez.
//  - Máximo CODIGO_MAX_INTENTOS intentos fallidos por código; después se invalida.
//  - Un código nuevo invalida los anteriores del mismo tipo.
//  - Límite de envíos: 1 cada CODIGO_REENVIO_SEG segundos y CODIGO_MAX_POR_HORA por hora.
// Las fechas se calculan con el reloj de MariaDB (NOW()), igual que login.php,
// para no depender de la zona horaria de PHP.

const CODIGO_LONGITUD     = 6;
const CODIGO_VIGENCIA_MIN = 10;
const CODIGO_MAX_INTENTOS = 5;
const CODIGO_REENVIO_SEG  = 60;
const CODIGO_MAX_POR_HORA = 5;

const TIPO_VERIFICACION = 'verificacion_correo';
const TIPO_RECUPERACION = 'recuperacion_contrasena';

function generarCodigo(): string
{
    return str_pad((string) random_int(0, 10 ** CODIGO_LONGITUD - 1), CODIGO_LONGITUD, '0', STR_PAD_LEFT);
}

/**
 * ¿Se puede emitir un código nuevo ahora?
 * Devuelve ['ok' => bool, 'mensaje' => string, 'espera' => segundos].
 */
function puedeEmitirCodigo(PDO $conexion, int $cedula, string $tipo): array
{
    $stmt = $conexion->prepare(
        "SELECT TIMESTAMPDIFF(SECOND, MAX(FechaCreacion), NOW()) AS segundos
         FROM codigo_verificacion WHERE Cedula = :c AND Tipo = :t"
    );
    $stmt->execute(['c' => $cedula, 't' => $tipo]);
    $segundos = $stmt->fetchColumn();

    if ($segundos !== null && $segundos !== false && (int) $segundos < CODIGO_REENVIO_SEG) {
        $espera = CODIGO_REENVIO_SEG - (int) $segundos;
        return ['ok' => false, 'espera' => $espera,
                'mensaje' => "Esperá $espera segundos antes de pedir otro código."];
    }

    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM codigo_verificacion
         WHERE Cedula = :c AND Tipo = :t AND FechaCreacion > (NOW() - INTERVAL 1 HOUR)"
    );
    $stmt->execute(['c' => $cedula, 't' => $tipo]);
    if ((int) $stmt->fetchColumn() >= CODIGO_MAX_POR_HORA) {
        return ['ok' => false, 'espera' => 3600,
                'mensaje' => 'Pediste demasiados códigos. Probá de nuevo en una hora.'];
    }

    return ['ok' => true, 'espera' => 0, 'mensaje' => ''];
}

/**
 * Crea un código nuevo (invalidando los anteriores del mismo tipo) y lo devuelve en claro
 * para enviarlo por correo. Es la única vez que existe en texto plano.
 */
function emitirCodigo(PDO $conexion, int $cedula, string $tipo, string $ip): string
{
    $codigo = generarCodigo();
    $hash   = password_hash($codigo, PASSWORD_DEFAULT);

    $conexion->beginTransaction();
    try {
        $stmt = $conexion->prepare(
            "UPDATE codigo_verificacion SET Usado = 1 WHERE Cedula = :c AND Tipo = :t AND Usado = 0"
        );
        $stmt->execute(['c' => $cedula, 't' => $tipo]);

        $stmt = $conexion->prepare(
            "INSERT INTO codigo_verificacion (Cedula, Tipo, CodigoHash, Expira, IP)
             VALUES (:c, :t, :h, NOW() + INTERVAL " . CODIGO_VIGENCIA_MIN . " MINUTE, :ip)"
        );
        $stmt->execute(['c' => $cedula, 't' => $tipo, 'h' => $hash, 'ip' => $ip]);

        $conexion->commit();
    } catch (Throwable $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        throw $e;
    }

    return $codigo;
}

/**
 * Valida un código ingresado.
 * Devuelve ['ok' => bool, 'motivo' => 'ok'|'invalido'|'agotado'|'expirado', 'restantes' => int].
 * Si es correcto lo marca como usado (un solo uso).
 */
function validarCodigo(PDO $conexion, int $cedula, string $tipo, string $codigoIngresado): array
{
    $conexion->beginTransaction();
    try {
        // FOR UPDATE: dos pedidos simultáneos no pueden saltarse el contador de intentos.
        $stmt = $conexion->prepare(
            "SELECT idCodigo, CodigoHash, Intentos FROM codigo_verificacion
             WHERE Cedula = :c AND Tipo = :t AND Usado = 0 AND Expira > NOW()
             ORDER BY idCodigo DESC LIMIT 1 FOR UPDATE"
        );
        $stmt->execute(['c' => $cedula, 't' => $tipo]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            $conexion->commit();
            return ['ok' => false, 'motivo' => 'expirado', 'restantes' => 0];
        }

        if ((int) $fila['Intentos'] >= CODIGO_MAX_INTENTOS) {
            $conexion->prepare("UPDATE codigo_verificacion SET Usado = 1 WHERE idCodigo = ?")
                     ->execute([$fila['idCodigo']]);
            $conexion->commit();
            return ['ok' => false, 'motivo' => 'agotado', 'restantes' => 0];
        }

        if (password_verify($codigoIngresado, $fila['CodigoHash'])) {
            $conexion->prepare("UPDATE codigo_verificacion SET Usado = 1 WHERE idCodigo = ?")
                     ->execute([$fila['idCodigo']]);
            $conexion->commit();
            return ['ok' => true, 'motivo' => 'ok', 'restantes' => CODIGO_MAX_INTENTOS];
        }

        $intentos  = (int) $fila['Intentos'] + 1;
        $agotado   = $intentos >= CODIGO_MAX_INTENTOS;
        $conexion->prepare("UPDATE codigo_verificacion SET Intentos = ?, Usado = ? WHERE idCodigo = ?")
                 ->execute([$intentos, $agotado ? 1 : 0, $fila['idCodigo']]);
        $conexion->commit();

        return ['ok' => false, 'motivo' => $agotado ? 'agotado' : 'invalido',
                'restantes' => max(0, CODIGO_MAX_INTENTOS - $intentos)];
    } catch (Throwable $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        throw $e;
    }
}

/** Mensaje para el usuario según el resultado de validarCodigo(). */
function mensajeCodigoFallido(array $resultado): string
{
    switch ($resultado['motivo']) {
        case 'agotado':
            return 'Superaste los intentos permitidos. Pedí un código nuevo.';
        case 'expirado':
            return 'El código venció o no existe. Pedí un código nuevo.';
        default:
            $r = $resultado['restantes'];
            return 'Código incorrecto. Te ' . ($r === 1 ? 'queda 1 intento.' : "quedan $r intentos.");
    }
}

/** "ni*****@gmail.com" — para mostrar a dónde se envió el código sin exponer el correo completo. */
function enmascararCorreo(string $correo): string
{
    $partes = explode('@', $correo, 2);
    if (count($partes) !== 2) {
        return '***';
    }
    $local = $partes[0];
    $visible = mb_substr($local, 0, min(2, max(1, mb_strlen($local) - 1)));
    return $visible . str_repeat('*', max(3, mb_strlen($local) - mb_strlen($visible))) . '@' . $partes[1];
}
