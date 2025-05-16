<?php
require_once __DIR__ . '/../../api/get_token.php';

header('Content-Type: application/json');

if (!isset($_GET['siret'])) {
    echo json_encode(['success' => false, 'message' => 'SIRET manquant']);
    exit;
}

$siret = trim($_GET['siret']);

if (!preg_match('/^\d{14}$/', $siret)) {
    echo json_encode(['success' => false, 'message' => 'Format SIRET invalide']);
    exit;
}

$token = getInseeToken();

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Impossible d\'obtenir le token']);
    exit;
}

$url = 'https://api.insee.fr/entreprises/sirene/V3.11/siret/' . urlencode($siret);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200 || !isset($data['etablissement'])) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur INSEE HTTP $httpCode",
        'raw' => $response
    ]);
    exit;
}

$etab = $data['etablissement'];

$nom = $etab['uniteLegale']['denominationUniteLegale']
    ?? $etab['uniteLegale']['prenomUsuelUniteLegale'] . ' ' . $etab['uniteLegale']['nomUniteLegale']
    ?? 'Nom non disponible';

$adresse = $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? 'Adresse non disponible';
$siren = $etab['uniteLegale']['siren'] ?? '';
$postal_code = $etab['adresseEtablissement']['codePostalEtablissement'] ?? '';
$city = $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? '';

echo json_encode([
    'success' => true,
    'nom' => $nom,
    'adresse' => $adresse,
    'siren' => $siren,
    'postal_code' => $postal_code,
    'city' => $city
]);
