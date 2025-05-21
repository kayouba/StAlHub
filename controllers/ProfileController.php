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

        // Vérifier si l'utilisateur est un étudiant
        $isStudent = ($user['role'] === 'student');

        // Validation des données
        $errors = [];
        
        // Vérification des champs obligatoires communs à tous les rôles
        $requiredFields = ['prenom', 'nom', 'email'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire";
            }
        }
        
        // Validation de l'email
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide";
        }
        
        // Si erreurs, rediriger vers le formulaire avec les erreurs
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: /stalhub/profile');
            exit;
        }

        // Initialiser les données de base pour tous les utilisateurs
        $data = [
            'first_name' => $_POST['prenom'] ?? '',
            'last_name' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
        ];
        
        // Ajouter les données spécifiques aux étudiants
        if ($isStudent) {
            // Détermination de l'année scolaire en cours
            $month = (int)date('m');
            $year = (int)date('Y');
            if ($month < 8) { // Si nous sommes entre janvier et juillet
                $startYear = $year - 1;
            } else { // Si nous sommes entre août et décembre
                $startYear = $year;
            }
            $endYear = $startYear + 1;
            $currentSchoolYear = $startYear . '-' . $endYear;
            
            // Ajouter les champs spécifiques aux étudiants
            $data['student_number'] = $_POST['num-etudiant'] ?? '';
            $data['program'] = $_POST['program'] ?? '';
            $data['track'] = $_POST['track'] ?? '';
            $data['level'] = $currentSchoolYear;
            
            // Définir le chemin absolu vers le répertoire uploads/users
            $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/stalhub/public/uploads/';
            $uploadUsersDir = $uploadBaseDir . 'users/';
            
            // Déboguer les chemins
            error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
            error_log("Chemin base uploads: " . $uploadBaseDir);
            error_log("Chemin users: " . $uploadUsersDir);
            
            // Créer le répertoire uploads s'il n'existe pas
            if (!file_exists($uploadBaseDir)) {
                if (!mkdir($uploadBaseDir, 0755, true)) {
                    error_log("ERREUR: Impossible de créer le répertoire " . $uploadBaseDir);
                    $_SESSION['form_errors'] = ["Erreur lors de la création des répertoires de téléversement"];
                    header('Location: /stalhub/profile');
                    exit;
                }
                error_log("Création du répertoire uploads: " . $uploadBaseDir);
            }
            
            // Créer le répertoire uploads/users s'il n'existe pas
            if (!file_exists($uploadUsersDir)) {
                if (!mkdir($uploadUsersDir, 0755, true)) {
                    error_log("ERREUR: Impossible de créer le répertoire " . $uploadUsersDir);
                    $_SESSION['form_errors'] = ["Erreur lors de la création des répertoires de téléversement"];
                    header('Location: /stalhub/profile');
                    exit;
                }
                error_log("Création du répertoire users: " . $uploadUsersDir);
            }
            
            // Créer le répertoire utilisateur s'il n'existe pas
            $userDir = $uploadUsersDir . $userId . '/';
            if (!file_exists($userDir)) {
                if (!mkdir($userDir, 0755, true)) {
                    error_log("ERREUR: Impossible de créer le répertoire " . $userDir);
                    $_SESSION['form_errors'] = ["Erreur lors de la création des répertoires de téléversement"];
                    header('Location: /stalhub/profile');
                    exit;
                }
                error_log("Création du répertoire utilisateur: " . $userDir);
            }
            
            // Gestion du CV (optionnel)
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK && !empty($_FILES['cv']['tmp_name'])) {
                // Déboguer les informations du fichier
                error_log("Fichier CV reçu: " . json_encode($_FILES['cv']));
                
                // Vérifier le type du fichier en utilisant une méthode plus fiable
                $fileInfo = pathinfo($_FILES['cv']['name']);
                $extension = strtolower($fileInfo['extension'] ?? '');
                $allowedExtensions = ['pdf'];
                $allowedTypes = ['application/pdf'];
                
                // Vérifier d'abord l'extension du fichier
                error_log("Extension du fichier CV: " . $extension);
                
                if (!in_array($extension, $allowedExtensions)) {
                    $_SESSION['form_errors'] = ["Le fichier CV doit être un PDF"];
                    header('Location: /stalhub/profile');
                    exit;
                }
                
                // Déplacer le fichier vers le répertoire de l'utilisateur
                $cvPath = $userDir . 'cv.pdf';
                
                if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath)) {
                    $uploadError = error_get_last();
                    error_log("Erreur lors du téléversement du CV: " . ($uploadError ? $uploadError['message'] : 'Inconnu'));
                    error_log("Tentative de déplacement vers: " . $cvPath);
                    $_SESSION['form_errors'] = ["Erreur lors du téléversement du CV"];
                    header('Location: /stalhub/profile');
                    exit;
                } else {
                    // Définir les permissions du fichier de manière plus sécurisée
                    chmod($cvPath, 0644);
                    error_log("CV téléversé avec succès vers: " . $cvPath);
                    
                    // Ajouter un message de succès spécifique pour le téléversement
                    if (!isset($_SESSION['success_message'])) {
                        $_SESSION['success_message'] = '';
                    }
                    $_SESSION['success_message'] .= " CV téléversé.";
                }
            }
            
            // Gestion de l'assurance (optionnel)
            if (isset($_FILES['assurance']) && $_FILES['assurance']['error'] === UPLOAD_ERR_OK && !empty($_FILES['assurance']['tmp_name'])) {
                // Déboguer les informations du fichier
                error_log("Fichier assurance reçu: " . json_encode($_FILES['assurance']));
                
                // Vérifier le type du fichier en utilisant une méthode plus fiable
                $fileInfo = pathinfo($_FILES['assurance']['name']);
                $extension = strtolower($fileInfo['extension'] ?? '');
                $allowedExtensions = ['pdf'];
                $allowedTypes = ['application/pdf'];
                
                // Vérifier d'abord l'extension du fichier
                error_log("Extension du fichier assurance: " . $extension);
                
                if (!in_array($extension, $allowedExtensions)) {
                    $_SESSION['form_errors'] = ["Le fichier d'assurance doit être un PDF"];
                    header('Location: /stalhub/profile');
                    exit;
                }
                
                // Déplacer le fichier vers le répertoire de l'utilisateur
                $assurancePath = $userDir . 'assurance.pdf';
                
                if (!move_uploaded_file($_FILES['assurance']['tmp_name'], $assurancePath)) {
                    $uploadError = error_get_last();
                    error_log("Erreur lors du téléversement de l'assurance: " . ($uploadError ? $uploadError['message'] : 'Inconnu'));
                    error_log("Tentative de déplacement vers: " . $assurancePath);
                    $_SESSION['form_errors'] = ["Erreur lors du téléversement de l'assurance"];
                    header('Location: /stalhub/profile');
                    exit;
                } else {
                    // Définir les permissions du fichier de manière plus sécurisée
                    chmod($assurancePath, 0644);
                    error_log("Assurance téléversée avec succès vers: " . $assurancePath);
                    
                    // Ajouter un message de succès spécifique pour le téléversement
                    if (!isset($_SESSION['success_message'])) {
                        $_SESSION['success_message'] = '';
                    }
                    $_SESSION['success_message'] .= " Assurance téléversée.";
                }
            }
        }

        // Mise à jour de l'utilisateur
        try {
            if ($userModel->update($userId, $data)) {
                if (!isset($_SESSION['success_message']) || empty($_SESSION['success_message'])) {
                    $_SESSION['success_message'] = "Profil mis à jour avec succès";
                } else {
                    $_SESSION['success_message'] = "Profil mis à jour avec succès." . $_SESSION['success_message'];
                }
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