<?php

namespace App\Controller;

use App\View;
use App\Model\RequestModel;
use App\Model\UserModel;

class CfaController
{
    public function dashboard(): void
{
    $model = new RequestModel();
    $userModel = new UserModel();

    $pendingRequests = $model->getAllWithStatusAndContract('VALID_PEDAGO','apprenticeship');
    $validatedRequests = $model->getAllWithStatusAndContract('VALID_CFA','apprenticeship');

    $programs = $userModel->getDistinctValues('program');
    $tracks = $userModel->getDistinctValues('track');
    $levels = $userModel->getDistinctValues('level');

    // Charger les fichiers du profil pour chaque demande
    foreach ($pendingRequests as &$req) {
        $userId = $req['student_id']; // <- assure-toi que ce champ existe
        $userPath = "/uploads/users/$userId/";
        $absolute = __DIR__ . "/../public" . $userPath;

        $req['documents'] = [];

        $files = [
            'cv.pdf.enc' => 'CV',
            'assurance.pdf.enc' => "Attestation d'assurance",
            'pstage_summary.pdf.enc' => 'Résumé de stage',
        ];

        foreach ($files as $filename => $label) {
            if (file_exists($absolute . $filename)) {
                $req['documents'][] = [
                    'label' => $label,
                    'file_path' => "/stalhub{$userPath}{$filename}"
                ];
            }
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
