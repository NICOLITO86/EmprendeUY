<?php
// Herramienta de diagnóstico del correo. Solo funciona desde tu propia PC (localhost).
// Uso: abrí  http://localhost/EmprendeUY/PHP/probar_correo.php
//      (opcional) ?para=otro@correo.com  para enviar la prueba a otra dirección.
// Muestra la configuración que se está usando y la conversación completa con el servidor SMTP,
// así se ve exactamente por qué no llega el correo. BORRÁ este archivo antes de publicar el sitio.

$ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
if (PHP_SAPI !== 'cli' && !in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Solo disponible desde localhost.');
}

require_once __DIR__ . '/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: text/plain; charset=utf-8');

$cfg  = configCorreo();
$para = filter_var($_GET['para'] ?? '', FILTER_VALIDATE_EMAIL) ?: ($cfg['usuario'] !== '' ? $cfg['usuario'] : '');

echo "== Configuración en uso ==\n";
echo "archivo config_correo.php: " . (is_file(__DIR__ . '/config_correo.php') ? 'existe' : 'NO EXISTE') . "\n";
echo "modo:       {$cfg['modo']}\n";
echo "host:       {$cfg['host']}\n";
echo "puerto:     {$cfg['puerto']}\n";
echo "cifrado:    {$cfg['cifrado']}\n";
echo "usuario:    {$cfg['usuario']}\n";
echo "clave:      " . ($cfg['clave'] === '' ? 'VACÍA  <-- falta pegar la contraseña de aplicación' : str_repeat('*', strlen($cfg['clave'])) . ' (' . strlen($cfg['clave']) . ' caracteres; la de Gmail tiene 16 sin espacios)') . "\n";
echo "remitente:  {$cfg['remitente']}\n";
echo "openssl:    " . (extension_loaded('openssl') ? 'activo' : 'NO ACTIVO <-- habilitá extension=openssl en php.ini') . "\n\n";

if ($cfg['modo'] === 'log') {
    exit("El modo es 'log': no se envía nada, el código queda en el error_log de Apache.\nCambiá 'modo' a 'smtp' en config_correo.php.\n");
}
if ($cfg['host'] === '' || $para === '') {
    exit("Falta el host SMTP o un destinatario.\n");
}
if ($cfg['usuario'] !== '' && $cfg['clave'] === '') {
    exit("Falta la clave en config_correo.php (campo 'clave').\n");
}

echo "== Enviando prueba a $para ==\n";
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $cfg['host'];
    $mail->Port       = $cfg['puerto'];
    $mail->Timeout    = 15;
    $mail->CharSet    = 'UTF-8';
    $mail->SMTPDebug  = 2;
    $mail->Debugoutput = static function ($str) { echo trim($str) . "\n"; };

    if ($cfg['cifrado'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($cfg['cifrado'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure  = '';
        $mail->SMTPAutoTLS = false;
    }
    if ($cfg['usuario'] !== '') {
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['usuario'];
        $mail->Password = $cfg['clave'];
    }

    if (is_file(__DIR__ . '/lib/cacert.pem')) {
        $mail->SMTPOptions = ['ssl' => ['cafile' => __DIR__ . '/lib/cacert.pem', 'verify_peer' => true, 'verify_peer_name' => true]];
    }

    $mail->setFrom($cfg['remitente'], $cfg['remitente_nombre']);
    $mail->addAddress($para);
    $mail->Subject = 'Prueba de correo EmprendeUY';
    $mail->Body    = 'Si estás leyendo esto, el envío de correos funciona. Código de ejemplo: 123456';
    $mail->send();

    echo "\n>>> RESULTADO: ENVIADO. Revisá la bandeja de entrada y también SPAM/Promociones de $para.\n";
} catch (Throwable $e) {
    echo "\n>>> RESULTADO: FALLÓ -> " . $e->getMessage() . "\n";
    echo "Pistas:\n";
    echo " - 'Could not authenticate' / 535: la clave no es una contraseña de aplicación válida (o no está activa la verificación en 2 pasos).\n";
    echo " - 'Could not connect' / timeout: firewall, antivirus o red bloquea el puerto {$cfg['puerto']}. Probá cifrado 'ssl' con puerto 465.\n";
    echo " - 'certificate verify failed': falta o está mal el certificado CA en php.ini (openssl.cafile / curl.cainfo).\n";
}
