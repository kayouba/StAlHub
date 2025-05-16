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
        
        // Récupérer les informations actuelles de l'utilisateur pour pré-remplir le formulaire
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            $userModel = new UserModel();
            $user = $userModel->findById($userId);
            // Passer les données utilisateur à la vue
            View::render('profile/student_form', ['user' => $user]);
        } else {
            header('Location: /stalhub/login');
            exit;
        }
    }

    public function submit(): void
    {
        $this->requireAuth();
        
        // Ne pas redémarrer session si déjà active
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

        // Validation des données
        $errors = [];
        
        // Vérification des champs obligatoires
        $requiredFields = ['prenom', 'nom', 'email', 'num-etudiant', 'formation', 'parcours', 'annee'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire";
            }
        }
        
        // Notez que le CV n'est pas dans la liste des champs obligatoires
        
        // Validation de l'email
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide";
        }
        
        // Si erreurs, rediriger vers le formulaire avec les erreurs
        if (!empty($errors)) {
            // Idéalement, sauvegardez les erreurs en session et redirigez
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: /stalhub/profile');
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
            'annee'           => $_POST['annee'] ?? ''
        ];

        // Gestion du CV (optionnel)
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            // Vérifier le type MIME du fichier
            $allowedTypes = ['application/pdf'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['cv']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['form_errors'] = ["Le fichier doit être un PDF"];
                header('Location: /stalhub/profile');
                exit;
            }
            
            $uploadDir = __DIR__ . '/../../public/uploads/cv/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $cvFilename = uniqid() . '_' . basename($_FILES['cv']['name']);
            $uploadPath = $uploadDir . $cvFilename;

            if (move_uploaded_file($_FILES['cv']['tmp_name'], $uploadPath)) {
                $data['cv_filename'] = $cvFilename;
            } else {
                $_SESSION['form_errors'] = ["Erreur lors du téléversement du CV"];
                header('Location: /stalhub/profile');
                exit;
            }
        }

        // Mise à jour de l'utilisateur - Ne pas vérifier que l'email soit identique
        // L'email peut changer si nécessaire
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
            // Log l'erreur
            error_log("Erreur mise à jour profil: " . $e->getMessage());
            $_SESSION['form_errors'] = ["Une erreur est survenue lors de la mise à jour"];
            header('Location: /stalhub/profile');
            exit;
        }
    }
}