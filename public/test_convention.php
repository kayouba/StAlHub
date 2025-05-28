<?php
require_once __DIR__ . '/../vendor/autoload.php'; // adapte le chemin si besoin
use App\Model\RequestDocumentModel;

$testPath = '/stalhub/public/uploads/tests/convention_test.pdf';
$requestId = 2; // à adapter selon la demande cible

$model = new RequestDocumentModel();
$model->saveDocument($requestId, $testPath, 'Convention');

echo "Document de test ajouté avec succès.";

