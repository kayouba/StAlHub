<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Configuration serveur
    $mail->isSMTP();
    $mail->Host       = 'localhost';
    $mail->Port       = 1025; // Port utilisé par MailHog
    $mail->SMTPAuth   = false; // pas d’authentification
    $mail->SMTPSecure = false; // pas de TLS

    // Destinataires
    $mail->setFrom('no-reply@stalhub.local', 'StalHub');
    $mail->addAddress('test@example.com', 'Test');

    // Contenu
    $mail->isHTML(false);
    $mail->Subject = 'Test PHPMailer via MailHog';
    $mail->Body    = 'Ceci est un test de PHPMailer vers MailHog.';

    $mail->send();
    echo '✅ Mail envoyé avec succès via PHPMailer';
} catch (Exception $e) {
    echo "❌ Échec de l’envoi du mail. Erreur : {$mail->ErrorInfo}";
}
