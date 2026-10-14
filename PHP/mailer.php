<?php
// Envío de correos transaccionales (códigos de verificación, avisos de seguridad).
// Usa PHPMailer (incluido en PHP/lib/PHPMailer, no hace falta Composer).

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Configuración de correo: variables de entorno > config_correo.php.
 */
function configCorreo(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $archivo = __DIR__ . '/config_correo.php';
    $base = is_file($archivo) ? (require $archivo) : [];
    if (!is_array($base)) {
        $base = [];
    }

    $env = static function (string $nombre, $porDefecto) {
        $v = getenv($nombre);
        return ($v === false || $v === '') ? $porDefecto : $v;
    };

    $cfg = [
        'host'             => (string) $env('SMTP_HOST', $base['host'] ?? ''),
        'puerto'           => (int)    $env('SMTP_PORT', $base['puerto'] ?? 587),
        'cifrado'          => (string) $env('SMTP_CIFRADO', $base['cifrado'] ?? 'tls'),
        'usuario'          => (string) $env('SMTP_USUARIO', $base['usuario'] ?? ''),
        // Las contraseñas de aplicación de Google se muestran con espacios ("abcd efgh ..."): se quitan.
        'clave'            => preg_replace('/\s+/', '', (string) $env('SMTP_CLAVE', $base['clave'] ?? '')),
        'remitente'        => (string) $env('MAIL_REMITENTE', $base['remitente'] ?? 'no-reply@emprendeuy.uy'),
        'remitente_nombre' => (string) $env('MAIL_REMITENTE_NOMBRE', $base['remitente_nombre'] ?? 'EmprendeUY'),
        'modo'             => (string) $env('MAIL_MODO', $base['modo'] ?? 'smtp'),
    ];
    return $cfg;
}

/** Último error de envío (para mostrarlo en pantalla cuando se trabaja en localhost). */
function ultimoErrorCorreo(?string $nuevo = null): string
{
    static $ultimo = '';
    if ($nuevo !== null) {
        $ultimo = $nuevo;
    }
    return $ultimo;
}

/** ¿La petición viene de la propia PC del desarrollador? Solo ahí se muestran detalles técnicos. */
function esPeticionLocal(): bool
{
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

/**
 * Envía un correo. Devuelve true si se entregó al servidor SMTP, false si falló.
 * Nunca lanza excepciones: el error queda en error_log y quien llama decide qué decirle al usuario.
 */
function enviarCorreo(string $para, string $nombrePara, string $asunto, string $html, string $texto): bool
{
    $cfg = configCorreo();

    if ($cfg['modo'] === 'log') {
        error_log("[EmprendeUY MAIL_MODO=log] Para: $para | Asunto: $asunto | Texto: " . str_replace(["\r", "\n"], ' ', $texto));
        return true;
    }

    if ($cfg['host'] === '') {
        ultimoErrorCorreo('SMTP no configurado (falta host en config_correo.php)');
        error_log('[EmprendeUY] SMTP no configurado (falta SMTP_HOST / config_correo.php). No se envió el correo a ' . $para);
        return false;
    }

    if ($cfg['usuario'] !== '' && $cfg['clave'] === '') {
        ultimoErrorCorreo('Falta la clave SMTP en config_correo.php');
        error_log('[EmprendeUY] Falta la clave SMTP: pegá la "contraseña de aplicación" de Gmail en PHP/config_correo.php (campo clave). No se envió el correo a ' . $para);
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host     = $cfg['host'];
        $mail->Port     = $cfg['puerto'];
        $mail->Timeout  = 10;
        $mail->CharSet  = 'UTF-8';

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
        } else {
            $mail->SMTPAuth = false;
        }

        // XAMPP en Windows suele no tener certificados raíz configurados y falla con
        // "certificate verify failed": se usa el paquete incluido en lib/cacert.pem.
        $cacert = __DIR__ . '/lib/cacert.pem';
        if (is_file($cacert)) {
            $mail->SMTPOptions = ['ssl' => ['cafile' => $cacert, 'verify_peer' => true, 'verify_peer_name' => true]];
        }

        $mail->setFrom($cfg['remitente'], $cfg['remitente_nombre']);
        $mail->addAddress($para, $nombrePara);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $html;
        $mail->AltBody = $texto;

        $mail->send();
        return true;
    } catch (MailException $e) {
        ultimoErrorCorreo($e->getMessage());
        error_log('[EmprendeUY] Error enviando correo a ' . $para . ': ' . $e->getMessage());
        return false;
    } catch (Throwable $e) {
        ultimoErrorCorreo($e->getMessage());
        error_log('[EmprendeUY] Error inesperado enviando correo: ' . $e->getMessage());
        return false;
    }
}

/** Marco HTML común de los correos (estilos en línea: los clientes de correo ignoran el CSS externo). */
function plantillaCorreo(string $titulo, string $contenidoHtml): string
{
    $t = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    return <<<HTML
<!DOCTYPE html>
<html lang="es"><body style="margin:0;padding:0;background:#f3f4f8;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f8;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;max-width:92%;">
        <tr><td style="background:#6381e6;padding:18px 24px;font-size:24px;font-weight:bold;color:#17245c;">
          Emprende<span style="color:#ffffff;">UY</span>
        </td></tr>
        <tr><td style="padding:24px;color:#222;font-size:15px;line-height:1.5;">
          <h2 style="margin:0 0 14px 0;color:#17245c;font-size:20px;">{$t}</h2>
          {$contenidoHtml}
        </td></tr>
        <tr><td style="padding:14px 24px;background:#f3f4f8;color:#777;font-size:12px;">
          Este es un mensaje automático de EmprendeUY. No respondas a este correo.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
HTML;
}

function bloqueCodigoHtml(string $codigo, int $minutos): string
{
    $c = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
    return '<p style="margin:0 0 8px 0;">Tu código es:</p>'
         . '<p style="margin:0 0 16px 0;font-size:34px;letter-spacing:8px;font-weight:bold;color:#17245c;">' . $c . '</p>'
         . '<p style="margin:0 0 8px 0;">Vence en <b>' . $minutos . ' minutos</b> y solo se puede usar una vez.</p>';
}

function enviarCorreoVerificacion(string $correo, string $nombre, string $codigo, int $minutos): bool
{
    $n = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $html = plantillaCorreo(
        'Verificá tu correo',
        '<p style="margin:0 0 14px 0;">Hola ' . $n . ', gracias por registrarte en EmprendeUY. Ingresá este código para confirmar tu correo:</p>'
        . bloqueCodigoHtml($codigo, $minutos)
        . '<p style="margin:14px 0 0 0;color:#777;font-size:13px;">Si vos no creaste esta cuenta, ignorá este mensaje.</p>'
    );
    $texto = "Hola $nombre, gracias por registrarte en EmprendeUY.\n\n"
           . "Tu código de verificación es: $codigo\n"
           . "Vence en $minutos minutos y solo se puede usar una vez.\n\n"
           . "Si vos no creaste esta cuenta, ignorá este mensaje.";
    return enviarCorreo($correo, $nombre, 'Verificá tu correo en EmprendeUY', $html, $texto);
}

function enviarCorreoRecuperacion(string $correo, string $nombre, string $codigo, int $minutos): bool
{
    $n = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $html = plantillaCorreo(
        'Recuperá tu contraseña',
        '<p style="margin:0 0 14px 0;">Hola ' . $n . ', recibimos un pedido para restablecer la contraseña de tu cuenta. Usá este código:</p>'
        . bloqueCodigoHtml($codigo, $minutos)
        . '<p style="margin:14px 0 0 0;color:#777;font-size:13px;">Si no fuiste vos, ignorá este mensaje: tu contraseña actual sigue siendo la misma. No compartas este código con nadie.</p>'
    );
    $texto = "Hola $nombre, recibimos un pedido para restablecer la contraseña de tu cuenta de EmprendeUY.\n\n"
           . "Tu código es: $codigo\n"
           . "Vence en $minutos minutos y solo se puede usar una vez.\n\n"
           . "Si no fuiste vos, ignorá este mensaje: tu contraseña actual no cambió. No compartas este código con nadie.";
    return enviarCorreo($correo, $nombre, 'Código para recuperar tu contraseña de EmprendeUY', $html, $texto);
}

function enviarAvisoCambioClave(string $correo, string $nombre): bool
{
    $n = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $html = plantillaCorreo(
        'Tu contraseña fue cambiada',
        '<p style="margin:0 0 14px 0;">Hola ' . $n . ', te avisamos que la contraseña de tu cuenta de EmprendeUY se cambió hace un momento.</p>'
        . '<p style="margin:0;">Si fuiste vos, no tenés que hacer nada. Si no reconocés este cambio, recuperá tu contraseña de inmediato desde la pantalla de inicio de sesión.</p>'
    );
    $texto = "Hola $nombre, la contraseña de tu cuenta de EmprendeUY se cambió hace un momento.\n"
           . "Si no fuiste vos, recuperá tu contraseña de inmediato desde la pantalla de inicio de sesión.";
    return enviarCorreo($correo, $nombre, 'Tu contraseña de EmprendeUY fue cambiada', $html, $texto);
}
