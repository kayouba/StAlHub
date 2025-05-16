<?php

namespace App\Controller;

use App\BaseController;
use App\View;
use App\Model\UserModel;

class ProfileController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        View::render('profile/student_form');
    }

    public function submit(): void
    {
        $this->requireAuth();
        session_start();

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        if (!$user) {
            header('Location: /stalhub/login');
            exit;
        }

        // Correspondance entre le formulaire HTML et les noms de colonnes
        $data = [
            'first_name'      => $_POST['prenom'] ?? '',
            'last_name'       => $_POST['nom'] ?? '',
            'email'           => $_POST['email'] ?? '',
            'student_number'  => $_POST['num-etudiant'] ?? '',
            'formation'       => $_POST['formation'] ?? '',
            'parcours'        => $_POST['parcours'] ?? '',
            'annee'           => $_POST['annee'] ?? '',
        ];

        // Gestion du CV
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/cv/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $cvFilename = uniqid() . '_' . basename($_FILES['cv']['name']);
            $uploadPath = $uploadDir . $cvFilename;

            if (move_uploaded_file($_FILES['cv']['tmp_name'], $uploadPath)) {
                $data['cv_filename'] = $cvFilename;
            }
        }

        // Vérifie que l'email correspond à celui déjà présent en base
        if ($user['email'] === $data['email']) {
            $userModel->update($userId, $data);
        }

        header('Location: /stalhub/dashboard');
        exit;
    }
}
