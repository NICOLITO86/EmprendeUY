<?php

try {

    $conexion = new PDO(
        "mysql:host=localhost;dbname=emprendeuy;charset=utf8mb4",
        "root",
        ""
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Usa prepared statements nativos del driver en vez de emularlos.
    $conexion->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    http_response_code(500);
    die("Error interno del servidor.");
}




?>