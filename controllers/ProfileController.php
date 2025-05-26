<?php

namespace App\Controller;

use App\BaseController;
use App\View;
use App\Model\UserModel;
use App\Lib\FileCrypto;

class ProfileController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $userModel = new UserModel();
            $user = $userModel->findById($userId);
            View::render('profile/student_form', ['user' => $user]);
        } else {
            header('Location: /stalhub/login');
            exit;
        }
    }

    public function submit(): void
    {

        $this->requireAuth();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        $isStudent = ($user['role'] === 'student');

        $errors = [];

        $requiredFields = ['prenom', 'nom', 'email'];

        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire";
            }
        }

        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide";
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: /stalhub/profile');
            exit;
        }

        $data = [
            'first_name' => $_POST['prenom'] ?? '',
            'last_name' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
        ];

        if ($isStudent) {
            $month = (int)date('m');
            $year = (int)date('Y');
            $startYear = ($month < 8) ? $year - 1 : $year;
            $endYear = $startYear + 1;
            $currentSchoolYear = "$startYear-$endYear";

            $data['student_number'] = $_POST['num-etudiant'] ?? '';
            $data['program'] = $_POST['program'] ?? '';
            $data['track'] = $_POST['track'] ?? '';
            $data['level'] = $currentSchoolYear;


            $uploadDir = __DIR__ . "/../public/uploads/users/$userId/";

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!empty($_FILES['cv']['tmp_name'])) {
                $cvPath = $uploadDir . 'cv.pdf.enc';
                if (FileCrypto::encrypt($_FILES['cv']['tmp_name'], $cvPath)) {
                    $_SESSION['step4']['cv'] = $userPublicPath . '/cv.pdf.enc';
                }
            }

            // ➤ Assurance
            if (!empty($_FILES['assurance']['tmp_name'])) {
                $assurancePath = $uploadDir . 'assurance.pdf.enc';
                if (FileCrypto::encrypt($_FILES['assurance']['tmp_name'], $assurancePath)) {
                    $data['insurance_filename'] = 'assurance.pdf.enc';
                } else {
                    $errors[] = "Erreur lors du chiffrement de l'attestation d'assurance.";
                }
            }
        }

        try {
            if ($userModel->update($userId, $data)) {
                $_SESSION['success_message'] = "Profil mis à jour avec succès";
                header('Location: /stalhub/dashboard');
                exit;
            } else {
                $_SESSION['form_errors'] = ["Erreur lors de la mise à jour du profil"];
                header('Location: /stalhub/profile');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Erreur mise à jour profil: " . $e->getMessage());
            $_SESSION['form_errors'] = ["Une erreur est survenue lors de la mise à jour"];
            header('Location: /stalhub/profile');
            exit;
        }
    }
}
