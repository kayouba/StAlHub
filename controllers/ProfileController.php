<?php
// Ajuster le namespace pour correspondre à votre structure
// Supprimez ou modifiez la ligne namespace si vous n'utilisez pas de namespaces

use View;
use PDO;

class ProfileController extends BaseController
{
    private $pdo;
    
    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function showStudentForm()
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();
        $userId = $this->getUserId();
        
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }
        
        // Récupérer les informations actuelles de l'étudiant (si elles existent)
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si le profil est déjà rempli complètement
        $isProfileComplete = !empty($userData['nom']) && 
                            !empty($userData['prenom']) && 
                            !empty($userData['numero_etudiant']) && 
                            !empty($userData['formation']) && 
                            !empty($userData['parcours']) && 
                            !empty($userData['annee']) && 
                            !empty($userData['cv_path']);
        
        // Afficher le formulaire
        View::render('profile/student_form', [
            'userData' => $userData,
            'isProfileComplete' => $isProfileComplete
        ]);
    }
    
    public function submitStudentForm()
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();
        $userId = $this->getUserId();
        
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }
        
        // Récupérer les données du formulaire
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $numeroEtudiant = $_POST['numero_etudiant'] ?? '';
        $formation = $_POST['formation'] ?? '';
        $parcours = $_POST['parcours'] ?? '';
        $annee = $_POST['annee'] ?? '';
        
        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email) || empty($numeroEtudiant) || 
            empty($formation) || empty($parcours) || empty($annee)) {
            View::render('profile/student_form', [
                'error' => 'Tous les champs sont obligatoires.',
                'userData' => $_POST
            ]);
            return;
        }
        
        // Vérification de l'email
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            View::render('profile/student_form', [
                'error' => 'Cette adresse email est déjà utilisée par un autre compte.',
                'userData' => $_POST
            ]);
            return;
        }
        
        // Gestion du téléchargement du CV
        $cvPath = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $tempFile = $_FILES['cv']['tmp_name'];
            $fileType = $_FILES['cv']['type'];
            
            // Vérification du type de fichier
            if ($fileType !== 'application/pdf') {
                View::render('profile/student_form', [
                    'error' => 'Le CV doit être au format PDF.',
                    'userData' => $_POST
                ]);
                return;
            }
            
            // Création du répertoire d'upload si nécessaire
            $uploadDir = __DIR__ . '/../uploads/cv/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Génération d'un nom de fichier unique
            $fileName = $userId . '_' . time() . '.pdf';
            $cvPath = 'uploads/cv/' . $fileName;
            
            // Déplacement du fichier téléchargé
            if (!move_uploaded_file($tempFile, $uploadDir . $fileName)) {
                View::render('profile/student_form', [
                    'error' => 'Erreur lors du téléchargement du CV.',
                    'userData' => $_POST
                ]);
                return;
            }
        } else {
            // Si aucun CV n'a été fourni, vérifier s'il existe déjà un CV
            $stmt = $this->pdo->prepare("SELECT cv_path FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($user['cv_path'])) {
                View::render('profile/student_form', [
                    'error' => 'Veuillez télécharger votre CV.',
                    'userData' => $_POST
                ]);
                return;
            }
            
            $cvPath = $user['cv_path'];
        }
        
        try {
            // Mise à jour des informations utilisateur
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET nom = ?, prenom = ?, email = ?, numero_etudiant = ?, 
                    formation = ?, parcours = ?, annee = ?, cv_path = ?,
                    profile_completed = 1
                WHERE id = ?
            ");
            
            $stmt->execute([
                $nom, $prenom, $email, $numeroEtudiant,
                $formation, $parcours, $annee, $cvPath,
                $userId
            ]);
            
            // Redirection vers le tableau de bord avec un message de succès
            $_SESSION['success_message'] = 'Votre profil a été mis à jour avec succès.';
            header('Location: /stalhub/dashboard');
            exit;
            
        } catch (\PDOException $e) {
            View::render('profile/student_form', [
                'error' => 'Une erreur est survenue lors de l\'enregistrement de vos informations : ' . $e->getMessage(),
                'userData' => $_POST
            ]);
        }
    }
}