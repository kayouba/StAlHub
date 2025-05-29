<?php
namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Service d’envoi d’e-mails via PHPMailer avec configuration SMTP.
 *
 * - Récupère les informations de configuration depuis les variables d’environnement.
 * - Prend en charge l’envoi d’e-mails HTML.
 */
class EmailService
{
    private PHPMailer $mailer;

    /**
     * Initialise PHPMailer avec les paramètres SMTP définis dans l'environnement.
     *
     * - Configure l’hôte, le port, les identifiants, le type de chiffrement et l’expéditeur.
     * - Active le mode HTML par défaut.
     */
    public function __construct()
    {
        $host = getenv('MAIL_HOST');
        $port = (int)getenv('MAIL_PORT');
        $user = getenv('MAIL_USER') ?: null;
        $pass = getenv('MAIL_PASS') ?: null;

        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = $host;
        $this->mailer->Port       = $port;
        $this->mailer->SMTPAuth   = (bool)$user;
        if ($user) {
            $this->mailer->Username   = $user;
            $this->mailer->Password   = $pass;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $this->mailer->setFrom(
            getenv('MAIL_FROM'),
            getenv('MAIL_FROM_NAME') ?: 'StalHub'
        );
        $this->mailer->isHTML(true);
    }

    /**
     * Envoie un email.
     *
     * @param string $to      Destinataire
     * @param string $subject Sujet
     * @param string $body    Contenu HTML
     */
    public function send(string $to, string $subject, string $body): void
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->send();
        } catch (MailException $e) {
            error_log('[EmailService] Mailer Error: ' . $e->getMessage());
        }
    }
}
