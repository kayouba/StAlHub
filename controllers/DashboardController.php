<?php

namespace App\Controller;

use App\View;
use App\BaseController;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Lib\StatusTranslator;

class DashboardController extends BaseController
{
    // Point d'entrée du tableau de bord pour rediriger selon le rôle
    public function index(): void
    {
        // Vérifie que l'utilisateur est authentifié
        $this->requireAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $userEntity = new UserModel();

        $role = $_SESSION['role'] ?? null;

        // Redirige vers la page de login si l'utilisateur n'est pas identifié
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

        // Redirections selon le rôle de l'utilisateur
        if ($role === 'admin') {
            header('Location: /stalhub/admin/dashboard');
            exit;
        }

        if ($role === 'academic_secretary') {
            header('Location: /stalhub/secretary/dashboard');
            exit;
        }

        if ($role === 'professional_responsible') {
            header('Location: /stalhub/responsable/requestList');
            exit;
        }

        if ($role === 'cfa') {
            header('Location: /stalhub/cfa/dashboard');
            exit;
        }

        if ($role === 'director') {
            header('Location: /stalhub/direction/dashboard');
            exit;
        }

        if ($role === 'tutor') {
            header('Location: /stalhub/tutor/dashboard');
            exit;
        }

        // === Si aucun rôle spécial, on suppose un étudiant ===

        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        // Si l'utilisateur n'existe plus, on détruit la session
        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }

        // Récupère les demandes faites par l'étudiant
        $requestModel = new RequestModel();
        $requests = $requestModel->findByStudentId($userId);

        // Traduction des statuts pour affichage plus clair
        foreach ($requests as &$r) {
            $r['translated_status'] = StatusTranslator::translate($r['status'] ?? '');
        }
        unset($r); // bonne pratique pour éviter des effets de bord

        // Affiche le tableau de bord de l'étudiant avec ses données
        View::render('dashboard/student', [
            'user' => $user,
            'requests' => $requests,
        ]);
    }
}
