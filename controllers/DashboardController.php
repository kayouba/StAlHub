<?php

namespace App\Controller;

use App\View;
use App\BaseController;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Lib\StatusTranslator;


/**
 * Contrôleur du tableau de bord principal.
 *
 * Redirige l'utilisateur vers le tableau de bord approprié
 * selon son rôle (admin, étudiant, CFA, direction, etc.).
 * Si l'utilisateur est un étudiant, il voit ses demandes avec le statut traduit.
 */
class DashboardController extends BaseController
{


    /**
     * Point d'entrée du tableau de bord.
     *
     * - Vérifie l'authentification de l'utilisateur.
     * - Identifie le rôle de l'utilisateur en session.
     * - Redirige automatiquement selon le rôle :
     *     - admin → /admin/dashboard
     *     - academic_secretary → /secretary/dashboard
     *     - professional_responsible → /responsable/requestList
     *     - cfa → /cfa/dashboard
     *     - director → /direction/dashboard
     *     - tutor → /tutor/dashboard
     * - Si rôle = étudiant :
     *     - Récupère les informations de l'étudiant et ses demandes.
     *     - Traduit les statuts de chaque demande.
     *     - Rend la vue du tableau de bord étudiant.
     *
     * @return void
     */
    public function index(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $userEntity = new UserModel();

        $role = $_SESSION['role'] ?? null;

        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

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

        // Ajout de la traduction des statuts
        foreach ($requests as &$r) {
            $r['translated_status'] = StatusTranslator::translate($r['status'] ?? '');
        }
        unset($r);

        View::render('dashboard/student', [
            'user' => $user,
            'requests' => $requests,
        ]);
    }
}
