<?php
namespace App\Controller;

use App\View;
use App\BaseController;
use App\Model\UserModel;
use App\Model\RequestModel;

class DashboardController extends BaseController
{

    
public function index(): void
{
    $this->requireAuth();

    $userId = $_SESSION['user_id'] ?? null;
    $userEntity= new UserModel();

    $role = $_SESSION['role'] ?? null;

    if (!$userId) {
        header('Location: /stalhub/login');
        exit;
    }

    if ($role === 'admin') {
        header('Location: /stalhub/admin/dashboard');
        exit;
    }

    if ($role === 'professional_responsible') {
        header('Location: /stalhub/responsable/requestList');
        exit;
    }


        if ($role === 'tutor') {
        header('Location: /stalhub/tutor/dashboard');
        exit;
    }

    // === Étudiant ===
    $userModel = new UserModel();
    $user = $userModel->findById($userId);

    if (!$user) {
        session_destroy();
        header('Location: /stalhub/login');
        exit;
    }

    $requestModel = new RequestModel();
    $requests = $requestModel->findByStudentId($userId);

    View::render('dashboard/student', [
        'user' => $user,
        'requests' => $requests,
    ]);
}

}
