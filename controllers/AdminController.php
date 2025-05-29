<?php

namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\CompanyModel;

class AdminController
{
    // Affiche le tableau de bord principal pour l'admin
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $requestModel = new RequestModel();

        // Récupère les utilisateurs et les statistiques des demandes
        $users = $userModel->findAll();
        $pendingCount = $requestModel->countByStatus('SOUMISE');
        $validatedCount = $requestModel->countByStatus('VALIDEE');
        $rejectedCount = $requestModel->countByStatus('REFUSEE');

        // Rend la vue du dashboard avec les données
        View::render('dashboard/admin', [
            'users' => $users,
            'pendingCount' => $pendingCount,
            'validatedCount' => $validatedCount,
            'rejectedCount' => $rejectedCount
        ]);
    }

    // Vérifie si l'utilisateur est un administrateur
    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
            header('Location: /stalhub/login');
            exit;
        }
    }

    // Onglet affichant tous les utilisateurs
    public function tabUsers(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $users = $userModel->findAll();

        View::render('admin/tabs/users', [
            'users' => $users
        ]);
    }

    // Onglet affichant toutes les demandes avec leurs documents et tuteurs
    public function tabRequests(): void
    {
        $this->requireAdmin();

        $requestModel = new RequestModel();
        $requests = $requestModel->getAllWithTutors();

        // Ajoute les documents à chaque demande
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

    // Onglet affichant la liste des entreprises
    public function tabCompanies(): void
    {
        $this->requireAdmin();

        $companyModel = new CompanyModel();
        $companies = $companyModel->findAll();

        View::render('admin/tabs/companies', [
            'companies' => $companies
        ]);
    }

    // Mise à jour du rôle et du statut administrateur d'un utilisateur
    public function updateUserRole(): void
    {
        $this->requireAdmin();

        $userId = $_POST['user_id'] ?? null;
        $role   = $_POST['role'] ?? null;
        $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === 'on' ? 1 : 0;

        // Vérifie les paramètres
        if (!$userId || !$role) {
            echo json_encode(['status' => 'error', 'message' => 'Paramètres invalides']);
            exit;
        }

        $userModel = new UserModel();
        $success = $userModel->updateRole((int)$userId, $role, $is_admin);

        // Retourne un message JSON selon le résultat
        if ($success) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Échec de la mise à jour.']);
        }

        exit;
    }

    // Suppression d’un utilisateur par ID
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

    // Active/désactive un utilisateur
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

    // Supprime une entreprise
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

    // Renvoie les demandes liées à une entreprise (JSON)
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

    // Vue détaillée d'une demande avec étudiant et entreprise associés
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

    // Statistiques globales des demandes selon leur statut
    public function stats(): void
    {
        $this->requireAdmin();

        $requestModel = new \App\Model\RequestModel();

        // Comptage des statuts différents de demande
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

        // Rend la vue avec toutes les statistiques
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

    // Mise à jour du tuteur assigné à une demande
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
