<?php
namespace Core;

use Twilio\Rest\Client;

class SMSService
{
    private Client $client;
    private string $from;

    public function __construct()
    {
        $sid   = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $this->from = getenv('TWILIO_FROM');
        $this->client = new Client($sid, $token);
    }

    public function send(string $to, string $body): void
    {
        $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $body,
        ]);
    }
}