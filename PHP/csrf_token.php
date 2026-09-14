<?php
require_once __DIR__ . '/csrf.php';
header('Content-Type: application/json');
echo json_encode(['token' => csrf_token()]);
