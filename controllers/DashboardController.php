<?php
namespace App\Controller;

use App\View;
use App\BaseController;
use App\Model\UserModel;

class DashboardController extends BaseController
{

    
    public function index(): void
    {

        $this->requireAuth();

        session_start();

        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }
        
        // Charger l'utilisateur
        // $userModel = new UserModel();
        $userModel = new \App\Model\UserModel();
        $user = $userModel->findById($userId);

        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }

        // Appel à la vue du tableau de bord
        View::render('dashboard/student', [
            'user' => $user
        ]);
        // $user = $userModel->findById($userId);

        // View::render('dashboard/student');
    }
}
