<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\CompanyModel;

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

        $users = $userModel->findAll(); // 🔁 Tous les rôles, pas que étudiants
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

    private function requireAdmin(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== 'admin') {
            header('Location: /stalhub/login');
            exit;
        }
    }

    // Onglet Utilisateurs
    public function tabUsers(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $users = $userModel->findAll(); // 🔁 Affiche tous les rôles

        View::render('admin/tabs/users', [
            'users' => $users
        ]);
    }

    // Onglet Demandes
    public function tabRequests(): void
    {
        $this->requireAdmin();

        $requestModel = new RequestModel();
        $requests = $requestModel->findAll();

        View::render('admin/tabs/requests', [
            'requests' => $requests
        ]);
    }

    // Onglet Entreprises
    public function tabCompanies(): void
    {
        $this->requireAdmin();

        $companyModel = new CompanyModel();
        $companies = $companyModel->findAll();

        View::render('admin/tabs/companies', [
            'companies' => $companies
        ]);
    }

    // Mise à jour du rôle utilisateur
    public function updateUserRole(): void
    {
        $this->requireAdmin();

        $userId = $_POST['user_id'] ?? null;
        $role   = $_POST['role'] ?? null;

        if (!$userId || !$role) {
            echo json_encode(['status' => 'error', 'message' => 'Paramètres invalides']);
            exit;
        }

        $userModel = new UserModel();
        $success = $userModel->updateRole((int)$userId, $role);

        if ($success) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Échec de la mise à jour.']);
        }

        exit;
    }
}
