<?php
// Pago del plan de un emprendedor (Pago.html?plan=basico|destacado|premium).
// Pago simulado: no hay pasarela real, los datos de la tarjeta nunca llegan al servidor.
// El monto sale de esta tabla (el del navegador es solo para mostrarlo en pantalla).
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers_sesion.php';

const PLANES_USD = ['basico' => 1, 'destacado' => 3, 'premium' => 5];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['exito' => false, 'msg' => 'Método no permitido.'], 405);
}

requireRole(['emprendedor'], false);
csrf_validar();

$plan = $_POST['plan'] ?? '';
if (!is_string($plan) || !array_key_exists($plan, PLANES_USD)) {
    responderJson(['exito' => false, 'msg' => 'El plan elegido no es válido.'], 400);
}

$_SESSION['plan_pagado'] = ['plan' => $plan, 'monto_usd' => PLANES_USD[$plan], 'fecha' => time()];

responderJson(['exito' => true, 'plan' => $plan, 'monto_usd' => PLANES_USD[$plan]]);
