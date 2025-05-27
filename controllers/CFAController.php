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

        $pendingRequests = $model->getAllWithStatus('VALID_PEDAGO');
        $validatedRequests = $model->getAllWithStatus('VALID_CFA');

        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels   = $userModel->getDistinctValues('level');

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
