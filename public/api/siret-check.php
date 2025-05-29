<?php
//  Inclusion du fichier permettant d’obtenir le token d’authentification INSEE
require_once __DIR__ . '/../../api/get_token.php';

//  Réponse JSON attendue
header('Content-Type: application/json');
// Vérifie la présence du paramètre `siret`
if (!isset($_GET['siret'])) {
    echo json_encode(['success' => false, 'message' => 'SIRET manquant']);
    exit;
}

$siret = trim($_GET['siret']);
//  Vérifie le format du SIRET (14 chiffres)
if (!preg_match('/^\d{14}$/', $siret)) {
    echo json_encode(['success' => false, 'message' => 'Format SIRET invalide']);
    exit;
}
//  Récupère un token d’accès pour l’API INSEE
$token = getInseeToken();

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Impossible d\'obtenir le token']);
    exit;
}
//  Prépare l’appel à l’API INSEE pour récupérer les données d’un établissement
$url = 'https://api.insee.fr/entreprises/sirene/V3.11/siret/' . urlencode($siret);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
//  Exécute la requête et récupère la réponse
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
//  Décode la réponse JSON
$data = json_decode($response, true);
//  Gestion des erreurs de l’API (réponse non 200 ou établissement manquant)
if ($httpCode !== 200 || !isset($data['etablissement'])) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur INSEE HTTP $httpCode",
        'raw' => $response
    ]);
    exit;
}
// Données de l’établissement extraites
$etab = $data['etablissement'];

$nom = $etab['uniteLegale']['denominationUniteLegale']
    ?? $etab['uniteLegale']['prenomUsuelUniteLegale'] . ' ' . $etab['uniteLegale']['nomUniteLegale']
    ?? 'Nom non disponible';

$adresse = $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? 'Adresse non disponible';
$siren = $etab['uniteLegale']['siren'] ?? '';
$postal_code = $etab['adresseEtablissement']['codePostalEtablissement'] ?? '';
$city = $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? '';
//  Envoie la réponse JSON finale
echo json_encode([
    'success' => true,
    'nom' => $nom,
    'adresse' => $adresse,
    'siren' => $siren,
    'postal_code' => $postal_code,
    'city' => $city
]);
