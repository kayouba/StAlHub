<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;

class AdminController
{
    public function dashboard(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /stalhub/login');
            exit;
        }

        $userModel = new UserModel();
        $requestModel = new RequestModel();

        $users = $userModel->findAllStudents(); // méthode à créer si non faite
        $pendingCount = $requestModel->countByStatus('SOUMISE');
        $validatedCount = $requestModel->countByStatus('VALIDEE');
        $rejectedCount = $requestModel->countByStatus('REFUSEE');

        View::render('dashboard/admin', [
            'users' => $users,
            'pendingCount' => $pendingCount,
            'validatedCount' => $validatedCount,
            'rejectedCount' => $rejectedCount
        ]);
    }
}
