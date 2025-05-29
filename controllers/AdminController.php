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
        $this->requireAdmin();

        $userModel = new UserModel();
        $requestModel = new RequestModel();

        $users = $userModel->findAll();
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
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
            header('Location: /stalhub/login');
            exit;
        }
    }



    // Onglet Utilisateurs
    public function tabUsers(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $users = $userModel->findAll();

        View::render('admin/tabs/users', [
            'users' => $users
        ]);
    }

    // Onglet Demandes
    public function tabRequests(): void
    {
        $this->requireAdmin();

        $requestModel = new RequestModel();
        $requests = $requestModel->getAllWithTutors();
        // Ajouter les documents à chaque demande
        foreach ($requests as &$req) {
            $req['documents'] = $requestModel->getDocumentsForRequest($req['id']);
        }

        $userModel = new UserModel();
        $tutors = $userModel->findByRole('tutor');

        View::render('admin/tabs/requests', [
            'requests' => $requests,
            'tutors' => $tutors
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
        $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === 'on' ? 1 : 0;


        if (!$userId || !$role) {
            echo json_encode(['status' => 'error', 'message' => 'Paramètres invalides']);
            exit;
        }

        $userModel = new UserModel();
        $success = $userModel->updateRole((int)$userId, $role, $is_admin);

        if ($success) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Échec de la mise à jour.']);
        }

        exit;
    }

    public function deleteUser(): void
    {
        $this->requireAdmin();

        $userId = $_GET['id'] ?? null;

        if ($userId) {
            $userModel = new \App\Model\UserModel();
            $deleted = $userModel->deleteById((int)$userId);

            if ($deleted) {
                header('Location: /stalhub/admin/dashboard');
                exit;
            } else {
                echo "Échec de la suppression.";
            }
        } else {
            echo "ID invalide.";
        }
    }

    public function toggleActive(): void
    {
        $this->requireAdmin();

        $userId = $_GET['id'] ?? null;

        if ($userId) {
            $userModel = new \App\Model\UserModel();
            $user = $userModel->findById((int)$userId);

            if ($user) {
                $newStatus = $user['is_active'] ? 0 : 1;
                $userModel->update((int)$userId, ['is_active' => $newStatus]);

                header('Location: /stalhub/admin/dashboard');
                exit;
            }
        }

        echo "Utilisateur non trouvé.";
    }

    public function deleteCompany(): void
    {
        $this->requireAdmin();

        $id = $_GET['id'] ?? null;

        if ($id) {
            $companyModel = new \App\Model\CompanyModel();
            $deleted = $companyModel->deleteById((int)$id);

            if ($deleted) {
                header('Location: /stalhub/admin/dashboard');
                exit;
            } else {
                echo "Échec de la suppression. L'entreprise est peut-être liée à une ou plusieurs demandes.";
            }
        } else {
            echo "ID d'entreprise manquant.";
        }
    }
    public function getCompanyRequests(): void
    {
        header('Content-Type: application/json');

        $companyId = $_GET['company_id'] ?? null;

        if (!$companyId) {
            echo json_encode([]);
            return;
        }

        $model = new \App\Model\RequestModel();
        $requests = $model->findByCompanyId((int)$companyId);
        echo json_encode($requests);
    }



    public function viewRequest(): void
    {
        $this->requireAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "Demande introuvable.";
            exit;
        }

        $requestModel = new \App\Model\RequestModel();

        $userModel = new \App\Model\UserModel();
        $companyModel = new \App\Model\CompanyModel();

        $request = $requestModel->findById((int)$id);

        if (!$request) {
            echo "Demande non trouvée.";
            exit;
        }

        $student = $userModel->findById((int)$request['student_id']);
        $company = $companyModel->findById((int)$request['company_id']);

        \App\View::render('admin/requests/view', [
            'request' => $request,
            'student' => $student,
            'company' => $company
        ]);
    }
    public function stats(): void
    {
        $this->requireAdmin();

        $requestModel = new \App\Model\RequestModel();

        $soumise    = $requestModel->countByStatus('SOUMISE');
        $validPeda  = $requestModel->countByStatus('VALID_PEDAGO');
        $refusPeda   = $requestModel->countByStatus('REFUSEE_PEDAGO');
        $attendSecret  = $requestModel->countByStatus('EN_ATTENTE_SECRETAIRE');
        $validSecret  = $requestModel->countByStatus('VALID_SECRETAIRE');
        $refusSecret   = $requestModel->countByStatus('REFUSEE_SECRETAIRE');
        $attendCFA  = $requestModel->countByStatus('EN_ATTENTE_CFA');
        $validCFA  = $requestModel->countByStatus('VALID_CFA');
        $refusCFA   = $requestModel->countByStatus('REFUSEE_CFA');
        $attendDirection  = $requestModel->countByStatus('EN_ATTENTE_DIRECTION');
        $validDirection  = $requestModel->countByStatus('VALID_DIRECTION');
        $refusDirection   = $requestModel->countByStatus('REFUSEE_DIRECTION');
        $validFinal  = $requestModel->countByStatus('VALIDE');


        \App\View::render('admin/stats', [
            'soumise' => $soumise,
            'validPeda' => $validPeda,
            'refusPeda' => $refusPeda,
            'attendSecret' => $attendSecret,
            'validSecret' => $validSecret,
            'refusSecret' => $refusSecret,
            'attendCFA' => $attendCFA,
            'validCFA' => $validCFA,
            'refusCFA' => $refusCFA,
            'attendDirection' => $attendDirection,
            'validDirection' => $validDirection,
            'refusDirection' => $refusDirection,
            'validFinal' => $validFinal,
        ]);
    }

    public function updateTutor(): void
    {

        $this->requireAdmin();

        $requestId = $_POST['request_id'] ?? null;
        $tutorId   = $_POST['tutor_id'] ?? null;

        if (!$requestId || !$tutorId) {
            echo json_encode(['status' => 'error', 'message' => 'Champs manquants']);
            return;
        }

        $model = new \App\Model\RequestModel();
        $success = $model->updateTutor((int)$requestId, (int)$tutorId);

        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? null : 'Échec de mise à jour.'
        ]);
    }
}
