<?php
// Mail helper: uses PHPMailer (if installed via Composer) or falls back to a lightweight SMTP sender.
// Call smtp_mail_send($to, $subject, $body, $fromEmail = null, $fromName = null, $isHtml = false)

function smtp_mail_send($to, $subject, $body, $fromEmail = null, $fromName = null, $isHtml = false) {
    // try PHPMailer via Composer first
    $composerAutoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        try {
            require_once $composerAutoload;
            // use PHPMailer\PHPMailer\PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // server settings
            if (defined('SMTP_ENABLED') && SMTP_ENABLED) {
                $mail->isSMTP();
                $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
                $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
                if (defined('SMTP_USERNAME') && SMTP_USERNAME) {
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                }
                // use TLS when port 587
                if (defined('SMTP_PORT') && SMTP_PORT == 587) {
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                }
            }

            $fromEmail = $fromEmail ?: (defined('SENDER_EMAIL') ? SENDER_EMAIL : 'no-reply@localhost');
            $fromName = $fromName ?: (defined('SENDER_NAME') ? SENDER_NAME : 'Website');

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            if ($isHtml) {
                $mail->isHTML(true);
                $mail->Body = $body;
            } else {
                $mail->Body = $body;
            }
            $mail->send();
            return true;
        } catch (Exception $e) {
            // fall through to fallback sender
        }
    }

    // Fallback: minimal SMTP over fsockopen
    $host = defined('SMTP_HOST') ? SMTP_HOST : null;
    $port = defined('SMTP_PORT') ? SMTP_PORT : 25;
    $user = defined('SMTP_USERNAME') ? SMTP_USERNAME : null;
    $pass = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : null;
    $use_tls = ($port == 587);

    if (!$host) {
        // last resort: use PHP mail()
        $headers = 'From: ' . ($fromName ?: (defined('SENDER_NAME') ? SENDER_NAME : 'Website')) . ' <' . ($fromEmail ?: (defined('SENDER_EMAIL') ? SENDER_EMAIL : 'no-reply@localhost')) . ">\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    $fp = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$fp) return false;

    $send = function($cmd) use ($fp) {
        fputs($fp, $cmd . "\r\n");
        return fgets($fp, 512);
    };

    $ehlo = 'EHLO ' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
    $send($ehlo);
    if ($use_tls) {
        $send('STARTTLS');
        @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $send($ehlo);
    }

    if ($user && $pass) {
        $send('AUTH LOGIN');
        $send(base64_encode($user));
        $send(base64_encode($pass));
    }

    $fromEmail = $fromEmail ?: (defined('SENDER_EMAIL') ? SENDER_EMAIL : 'no-reply@localhost');
    $fromName = $fromName ?: (defined('SENDER_NAME') ? SENDER_NAME : 'Website');

    $send('MAIL FROM: <' . $fromEmail . '>');
    $send('RCPT TO: <' . $to . '>');
    $send('DATA');
    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8';
    $msg = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
    $send($msg);
    $send('QUIT');
    fclose($fp);
    return true;
}

?>
