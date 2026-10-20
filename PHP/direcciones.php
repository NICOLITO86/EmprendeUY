<?php
// POST con 'accion' = crear | editar | eliminar | principal.
// Todas las operaciones se limitan a las direcciones del usuario con sesión (Cedula de la sesión).
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json; charset=utf-8');

requireRole(['cliente', 'emprendedor', 'administrador', 'usuario'], false);
csrf_validar();

const MAX_DIRECCIONES = 10;

const DEPARTAMENTOS = [
    'Artigas', 'Canelones', 'Cerro Largo', 'Colonia', 'Durazno', 'Flores', 'Florida', 'Lavalleja',
    'Maldonado', 'Montevideo', 'Paysandú', 'Río Negro', 'Rivera', 'Rocha', 'Salto', 'San José',
    'Soriano', 'Tacuarembó', 'Treinta y Tres',
];

function fallo(string $mensaje): void
{
    echo json_encode(['exito' => false, 'mensaje' => $mensaje]);
    exit;
}

/** Valida y limpia los campos del formulario de dirección. Corta con un mensaje si algo está mal. */
function leerDireccion(): array
{
    $c = fn(string $k): string => trim($_POST[$k] ?? '');

    $d = [
        'alias'         => $c('alias_direccion'),
        'calle'         => $c('calle'),
        'numero_puerta' => $c('numero_puerta'),
        'apartamento'   => $c('apartamento'),
        'esquina'       => $c('esquina'),
        'barrio'        => $c('barrio'),
        'ciudad'        => $c('ciudad'),
        'departamento'  => $c('departamento'),
        'codigo_postal' => $c('codigo_postal'),
        'indicaciones'  => $c('indicaciones'),
    ];

    $limites = [
        'alias' => [1, 60, 'El nombre de la dirección'], 'calle' => [1, 100, 'La calle'],
        'numero_puerta' => [1, 10, 'El número de puerta'], 'apartamento' => [0, 20, 'El apartamento'],
        'esquina' => [0, 100, 'La esquina'], 'barrio' => [1, 80, 'El barrio'], 'ciudad' => [1, 80, 'La ciudad'],
        'indicaciones' => [0, 200, 'Las indicaciones'],
    ];
    foreach ($limites as $campo => [$min, $max, $etiqueta]) {
        $largo = mb_strlen($d[$campo]);
        if ($largo < $min) fallo("$etiqueta es obligatorio.");
        if ($largo > $max) fallo("$etiqueta admite hasta $max caracteres.");
    }

    if (!in_array($d['departamento'], DEPARTAMENTOS, true)) fallo('Elegí un departamento válido.');
    if ($d['codigo_postal'] !== '' && !preg_match('/^\d{5}$/', $d['codigo_postal'])) {
        fallo('El código postal debe tener 5 dígitos.');
    }

    $d['es_principal'] = ($_POST['es_principal'] ?? '') === '1';

    // Los opcionales vacíos se guardan como NULL.
    foreach (['apartamento', 'esquina', 'codigo_postal', 'indicaciones'] as $opcional) {
        if ($d[$opcional] === '') $d[$opcional] = null;
    }
    return $d;
}

$cedula = (int) $_SESSION['Cedula'];
$accion = $_POST['accion'] ?? '';
$id     = filter_var($_POST['id_direccion'] ?? null, FILTER_VALIDATE_INT);

try {
    switch ($accion) {

        case 'crear':
            $d = leerDireccion();

            $conexion->beginTransaction();

            $stmt = $conexion->prepare("SELECT COUNT(*) FROM direccion WHERE Cedula = ? FOR UPDATE");
            $stmt->execute([$cedula]);
            $cantidad = (int) $stmt->fetchColumn();
            if ($cantidad >= MAX_DIRECCIONES) {
                $conexion->rollBack();
                fallo('Podés guardar hasta ' . MAX_DIRECCIONES . ' direcciones. Eliminá alguna para agregar otra.');
            }

            // La primera dirección es siempre la principal.
            $principal = $cantidad === 0 || $d['es_principal'];
            if ($principal) {
                $conexion->prepare("UPDATE direccion SET Es_Principal = 0 WHERE Cedula = ?")->execute([$cedula]);
            }

            $conexion->prepare(
                "INSERT INTO direccion (Cedula, Alias, Calle, Numero_Puerta, Apartamento, Esquina, Barrio, Ciudad,
                                        Departamento, Codigo_Postal, Indicaciones, Es_Principal)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $cedula, $d['alias'], $d['calle'], $d['numero_puerta'], $d['apartamento'], $d['esquina'],
                $d['barrio'], $d['ciudad'], $d['departamento'], $d['codigo_postal'], $d['indicaciones'],
                $principal ? 1 : 0,
            ]);
            $conexion->commit();

            echo json_encode(['exito' => true, 'mensaje' => 'Dirección guardada.']);
            break;

        case 'editar':
            if ($id === false || $id === null) fallo('Dirección inválida.');
            $d = leerDireccion();

            $conexion->beginTransaction();

            $stmt = $conexion->prepare("SELECT Es_Principal FROM direccion WHERE id_direccion = ? AND Cedula = ? FOR UPDATE");
            $stmt->execute([$id, $cedula]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                $conexion->rollBack();
                fallo('La dirección no existe o no te pertenece.');
            }

            // Si ya era la principal sigue siéndolo aunque se destilde (para cambiarla se marca otra).
            $principal = $d['es_principal'] || (int) $fila['Es_Principal'] === 1;
            if ($principal) {
                $conexion->prepare("UPDATE direccion SET Es_Principal = 0 WHERE Cedula = ?")->execute([$cedula]);
            }

            $conexion->prepare(
                "UPDATE direccion SET Alias = ?, Calle = ?, Numero_Puerta = ?, Apartamento = ?, Esquina = ?, Barrio = ?,
                        Ciudad = ?, Departamento = ?, Codigo_Postal = ?, Indicaciones = ?, Es_Principal = ?
                 WHERE id_direccion = ? AND Cedula = ?"
            )->execute([
                $d['alias'], $d['calle'], $d['numero_puerta'], $d['apartamento'], $d['esquina'], $d['barrio'],
                $d['ciudad'], $d['departamento'], $d['codigo_postal'], $d['indicaciones'], $principal ? 1 : 0,
                $id, $cedula,
            ]);
            $conexion->commit();

            echo json_encode(['exito' => true, 'mensaje' => 'Dirección actualizada.']);
            break;

        case 'principal':
            if ($id === false || $id === null) fallo('Dirección inválida.');

            $conexion->beginTransaction();
            $stmt = $conexion->prepare("SELECT 1 FROM direccion WHERE id_direccion = ? AND Cedula = ? FOR UPDATE");
            $stmt->execute([$id, $cedula]);
            if (!$stmt->fetch()) {
                $conexion->rollBack();
                fallo('La dirección no existe o no te pertenece.');
            }
            $conexion->prepare("UPDATE direccion SET Es_Principal = 0 WHERE Cedula = ?")->execute([$cedula]);
            $conexion->prepare("UPDATE direccion SET Es_Principal = 1 WHERE id_direccion = ? AND Cedula = ?")->execute([$id, $cedula]);
            $conexion->commit();

            echo json_encode(['exito' => true, 'mensaje' => 'Dirección principal actualizada.']);
            break;

        case 'eliminar':
            if ($id === false || $id === null) fallo('Dirección inválida.');

            $conexion->beginTransaction();
            $stmt = $conexion->prepare("SELECT Es_Principal FROM direccion WHERE id_direccion = ? AND Cedula = ? FOR UPDATE");
            $stmt->execute([$id, $cedula]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                $conexion->rollBack();
                fallo('La dirección no existe o no te pertenece.');
            }

            $conexion->prepare("DELETE FROM direccion WHERE id_direccion = ? AND Cedula = ?")->execute([$id, $cedula]);

            // Si se borró la principal, la más reciente que quede pasa a ser la principal.
            if ((int) $fila['Es_Principal'] === 1) {
                $conexion->prepare(
                    "UPDATE direccion SET Es_Principal = 1 WHERE Cedula = ? ORDER BY FechaCreacion DESC, id_direccion DESC LIMIT 1"
                )->execute([$cedula]);
            }
            $conexion->commit();

            echo json_encode(['exito' => true, 'mensaje' => 'Dirección eliminada.']);
            break;

        default:
            fallo('Acción no válida.');
    }
} catch (PDOException $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    error_log('Error en direcciones.php: ' . $e->getMessage());
    fallo('No se pudo completar la operación. Intentá de nuevo.');
}
