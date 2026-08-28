<?php
// api/publicaciones.php
// Devuelve publicaciones en formato JSON. No genera HTML.
// GET params: q (busqueda), categoria, pagina

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$categoriasValidas = ['Ropa', 'Hogar', 'Tecnologia', 'Carpinteria', 'Herreria', 'Higiene', 'Deportes'];

$busqueda  = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? 'Todos');
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 12;
$offset    = ($pagina - 1) * $porPagina;

$where  = ["p.status = 'Activa'"];
$params = [];

if ($categoria !== 'Todos' && in_array($categoria, $categoriasValidas, true)) {
    $where[] = 'p.categoria = :categoria';
    $params[':categoria'] = $categoria;
}

if ($busqueda !== '') {
    $where[] = '(p.titulo LIKE :busqueda OR p.descripcion LIKE :busqueda)';
    $params[':busqueda'] = '%' . $busqueda . '%';
}

$whereSql = implode(' AND ', $where);

// Total de resultados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM publicaciones p WHERE $whereSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

// Publicaciones de la página actual
$sql = "SELECT p.id, p.titulo, p.precio, p.categoria,
               e.Nombre AS emp_nombre, e.Apellido AS emp_apellido,
               u.Localidad AS ciudad
        FROM publicaciones p
        JOIN emprendedor e ON e.Cedula = p.emprendedor_cedula
        JOIN usuario u ON u.Cedula = e.Cedula
        WHERE $whereSql
        ORDER BY p.fecha_publicacion DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$filas = $stmt->fetchAll();

// Armamos la respuesta con datos ya listos para el frontend
$publicaciones = array_map(function ($fila) {
    return [
        'id'            => (int)$fila['id'],
        'titulo'        => $fila['titulo'],
        'precio'        => (float)$fila['precio'],
        'categoria'     => $fila['categoria'],
        'emprendimiento'=> $fila['emp_nombre'] . ' ' . $fila['emp_apellido'],
        'ciudad'        => $fila['ciudad'],
        'foto_url'      => 'foto.php?id=' . (int)$fila['id'],
    ];
}, $filas);

echo json_encode([
    'total'          => $total,
    'pagina'         => $pagina,
    'porPagina'      => $porPagina,
    'hayMas'         => ($offset + $porPagina) < $total,
    'publicaciones'  => $publicaciones,
], JSON_UNESCAPED_UNICODE);
