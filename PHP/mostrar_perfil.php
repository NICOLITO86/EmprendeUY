<?php
// GET -> datos del usuario con sesión iniciada + sus direcciones. Lo usa JS/perfil.js.
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Cualquier usuario con sesión puede ver su propio perfil (los datos salen siempre de la sesión).
requireRole(['cliente', 'emprendedor', 'administrador', 'usuario'], false);

$cedula = (int) $_SESSION['Cedula'];

try {
    $stmt = $conexion->prepare(
        "SELECT Cedula, Nombre, Apellido, Correo, Fecha_Nacimiento, Genero, Num_Telefono, Rol, Sobre_Mi, Foto_Perfil
         FROM usuario WHERE Cedula = ? AND Activo = 1 LIMIT 1"
    );
    $stmt->execute([$cedula]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
        echo json_encode(['exito' => false, 'mensaje' => 'No se encontró tu cuenta.']);
        exit;
    }

    $foto = null;
    if (!empty($u['Foto_Perfil'])) {
        $archivo = basename($u['Foto_Perfil']);
        $ruta    = __DIR__ . '/../Imagenes/perfiles/' . $archivo;
        if (is_file($ruta)) {
            $foto = '../Imagenes/perfiles/' . rawurlencode($archivo);
        }
    }

    $stmt = $conexion->prepare(
        "SELECT id_direccion AS id, Alias AS alias, Calle AS calle, Numero_Puerta AS numero_puerta,
                Apartamento AS apartamento, Esquina AS esquina, Barrio AS barrio, Ciudad AS ciudad,
                Departamento AS departamento, Codigo_Postal AS codigo_postal, Indicaciones AS indicaciones,
                Es_Principal AS es_principal
         FROM direccion WHERE Cedula = ?
         ORDER BY Es_Principal DESC, FechaCreacion DESC"
    );
    $stmt->execute([$cedula]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($direcciones as &$d) {
        $d['id']           = (int) $d['id'];
        $d['es_principal'] = (int) $d['es_principal'] === 1;
    }
    unset($d);

    echo json_encode([
        'exito' => true,
        'usuario' => [
            'cedula'           => str_pad((string) $u['Cedula'], 8, '0', STR_PAD_LEFT),
            'nombre'           => $u['Nombre'],
            'apellido'         => $u['Apellido'],
            'correo'           => $u['Correo'],
            'Fecha_Nacimiento' => $u['Fecha_Nacimiento'],
            'genero'           => strtolower($u['Genero']),
            'Num_Telefono'     => $u['Num_Telefono'],
            'rol'              => $u['Rol'],
            'sobre_mi'         => $u['Sobre_Mi'] ?? '',
            'foto'             => $foto,
        ],
        'direcciones' => $direcciones,
    ]);
} catch (PDOException $e) {
    error_log('Error en mostrar_perfil.php: ' . $e->getMessage());
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo cargar tu perfil. Intentá de nuevo.']);
}
