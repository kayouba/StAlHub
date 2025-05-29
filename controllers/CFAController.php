<?php

namespace App\Controller;

use App\View;
use App\Model\RequestModel;
use App\Model\UserModel;
use App\Model\RequestDocumentModel;

class CfaController
{
    // Affiche le tableau de bord du CFA avec les demandes en attente et validées
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();
        $documentModel = new RequestDocumentModel();

        // Récupère les demandes en attente de validation CFA pour contrat d'apprentissage
        $pendingRequests = $model->getAllWithStatusAndContract('VALID_PEDAGO', 'apprenticeship');

        // Récupère les demandes déjà validées par le CFA
        $validatedRequests = $model->getAllWithStatusAndContract('VALID_CFA', 'apprenticeship');

        // Récupère les valeurs distinctes pour programme, parcours et niveau
        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels = $userModel->getDistinctValues('level');

        // Ajoute les documents associés à chaque demande en attente
        foreach ($pendingRequests as &$req) {
            $req['documents'] = [];
            $documents = $documentModel->getDocumentsForRequest((int)$req['id']);
            foreach ($documents as $doc) {
                $req['documents'][] = [
                    'label' => $doc['label'] ?? basename($doc['file_path']),
                    'file_path' => $doc['file_path']
                ];
            }
        }

        // Ajoute les documents associés à chaque demande validée
        foreach ($validatedRequests as &$req) {
            $req['documents'] = [];
            $documents = $documentModel->getDocumentsForRequest((int)$req['id']);
            foreach ($documents as $doc) {
                $req['documents'][] = [
                    'label' => $doc['label'] ?? basename($doc['file_path']),
                    'file_path' => $doc['file_path']
                ];
            }
        }

        // Rend la vue du tableau de bord CFA avec toutes les données collectées
        View::render('/dashboard/cfa', [
            'pendingRequests' => $pendingRequests,
            'validatedRequests' => $validatedRequests,
            'programs' => $programs,
            'tracks' => $tracks,
            'levels' => $levels
        ]);
    }

    // Valide une demande spécifique via POST (validation CFA)
    public function validate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['request_id'])) {
            $model = new RequestModel();
            // Met à jour le statut de la demande
            $model->updateStatus((int)$_POST['request_id'], 'VALID_CFA');

            // Stocke un message flash de succès
            $_SESSION['success_message'] = "Demande validée par le CFA.";
        }

        // Redirige vers le tableau de bord CFA
        header('Location: /stalhub/cfa/dashboard');
        exit;
    }
}
