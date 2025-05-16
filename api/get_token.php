<?php
function getInseeToken(): string
{
    $client_id = 'tprSP7DlYTnUaAaLbW30rzmYZ5ga';
    $client_secret = '3swR8uGVex121NYUmh1kFLNDySwa';
    $base64 = base64_encode("$client_id:$client_secret");

    $ch = curl_init("https://api.insee.fr/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $base64",
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return '';
    }

    curl_close($ch);
    $data = json_decode($response, true);
    return $data['access_token'] ?? '';
}
