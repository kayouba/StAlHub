<?php

namespace App\Controller;

use App\View;
use App\Model\RequestModel;
use App\Model\UserModel;
use App\Model\RequestDocumentModel;


class CfaController
{
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();
        $documentModel = new RequestDocumentModel();

        $pendingRequests = $model->getAllWithStatusAndContract('VALID_PEDAGO', 'apprenticeship');
        $validatedRequests = $model->getAllWithStatusAndContract('VALID_CFA', 'apprenticeship');

        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels = $userModel->getDistinctValues('level');

        // Charger les documents pour les demandes en attente
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

        // Charger les documents pour les demandes validées
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

        View::render('/dashboard/cfa', [
            'pendingRequests' => $pendingRequests,
            'validatedRequests' => $validatedRequests,
            'programs' => $programs,
            'tracks' => $tracks,
            'levels' => $levels
        ]);
    }



    public function validate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['request_id'])) {
            $model = new RequestModel();
            $model->updateStatus((int)$_POST['request_id'], 'VALID_CFA');
            $_SESSION['success_message'] = "Demande validée par le CFA.";
        }
        header('Location: /stalhub/cfa/dashboard');
        exit;
    }
}
