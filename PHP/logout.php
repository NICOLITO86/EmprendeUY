<?php
require_once __DIR__ . '/session_config.php';

session_unset();
session_destroy();

header("Location: ../HTML/iniciar_sesion.html");
exit();
?>
