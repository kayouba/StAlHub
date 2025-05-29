<?php
namespace Core;

use Twilio\Rest\Client;

/**
 * Service d'envoi de SMS via l'API Twilio.
 */
class SMSService
{
    private Client $client;
    private string $from;

    /**
     * Initialise le client Twilio avec les identifiants d'environnement.
     */
    public function __construct()
    {
        $sid   = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $this->from = getenv('TWILIO_FROM');
        $this->client = new Client($sid, $token);
    }

    /**
     * Envoie un SMS à un numéro donné.
     *
     * @param string $to   Numéro de téléphone du destinataire (au format international)
     * @param string $body Contenu du message
     */
    public function send(string $to, string $body): void
    {
        $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $body,
        ]);
    }
}