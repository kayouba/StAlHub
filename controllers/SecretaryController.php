<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\SecretaryModel;
use App\Model\RequestModel;

class SecretaryController {
    /**
     * Affiche le tableau de bord de la secrétaire académique.
     * 
     * Cette méthode :
     * - Vérifie si l'utilisateur connecté est bien une secrétaire académique.
     * - Redirige vers la page de connexion en cas d'accès non autorisé.
     * - Récupère les informations de l'utilisateur via le UserModel.
     * - Charge la liste de toutes les demandes via le SecretaryModel.
     * - Rend la vue du tableau de bord de la secrétaire.
    **/
    public function dashboard(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== 'academic_secretary') {
            header('Location: /stalhub/login');
            exit;
        }

        // Charger l'utilisateur depuis la base via UserModel
        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }
                
        $model = new SecretaryModel();
        $demandes = $model->getAll();

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/dashboard/secretary.php';
    }

    /**
     * Affiche les détails d'une demande spécifique pour la secrétaire académique.
     * 
     * Cette méthode :
     * - Vérifie si l'utilisateur est une secrétaire académique, sinon redirige vers la page de connexion.
     * - Récupère l'ID de la demande depuis les paramètres GET.
     * - Charge les informations détaillées de la demande (étudiant, poste, etc.) via le RequestModel.
     * - Récupère les documents associés à la demande via le SecretaryModel.
     * - Affiche la vue correspondante contenant les détails de la demande et les pièces jointes.
    **/
    public function detailsFile(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== 'academic_secretary') {
            header('Location: /stalhub/login');
            exit;
        }

        $requestModel = new RequestModel();
        $secretaryModel = new SecretaryModel();

        $requestId = $_GET['id'] ?? null;
        $requestDetails = null;
        $documents = [];

        if ($requestId) {
            $requestDetails = $requestModel->findRequestInfoById((int)$requestId);
            $documents = $secretaryModel->getDocumentsByRequestId((int)$requestId);
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/secretary/detailsfile.php';
    }

    /**
     * Met à jour le statut d’un document et son commentaire associé.
     *
     * Cette méthode :
     * - Lit les données JSON reçues depuis une requête POST (via fetch).
     * - Vérifie la validité du JSON et des champs essentiels (`document_id`, `status`, `comment`).
     * - Appelle le modèle `SecretaryModel` pour mettre à jour le statut et le commentaire du document.
     * - Retourne une réponse JSON indiquant le succès ou l’échec de l’opération.
     * - Log les actions et les erreurs pour le debug.
     *
    **/
    public function updateDocumentStatus(): void {
        // Définir le type de contenu pour JSON
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $id = (int)($input['document_id'] ?? 0);
            $status = $input['status'] ?? '';
            $comment = $input['comment'] ?? null;
            
            // Validation des données
            if ($id <= 0) {
                throw new \Exception('ID du document invalide');
            }
            
            if (empty($status)) {
                throw new \Exception('Statut requis');
            }
            
            // Log pour debug
            error_log("Mise à jour document ID: $id vers statut: $status");
            
            $model = new SecretaryModel();
            $success = $model->updateDocumentStatus($id, $status, $comment);
            
            if ($success) {
                error_log("Mise à jour réussie pour le document ID: $id");
            } else {
                error_log("Échec de mise à jour pour le document ID: $id");
            }
            
            echo json_encode(['success' => $success]);
            
        } catch (\Exception $e) {
            error_log("Erreur dans updateDocumentStatus: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Valide en masse plusieurs documents à partir de leurs IDs.
     *
     * Cette méthode :
     * - Reçoit une liste d'identifiants de documents via une requête POST en JSON.
     * - Vérifie que les données reçues sont valides.
     * - Parcourt les identifiants et utilise le modèle `SecretaryModel` pour mettre à jour chaque document avec le statut "validée".
     * - Retourne une réponse JSON indiquant le succès global de l'opération et les éventuelles erreurs individuelles.
     * - Log chaque erreur de mise à jour pour faciliter le débogage.
    **/
    public function validateAllDocuments(): void {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $documentIds = $input['document_ids'] ?? [];
            
            if (empty($documentIds) || !is_array($documentIds)) {
                throw new \Exception('Liste des IDs de documents requise');
            }

            $model = new SecretaryModel();
            $success = true;
            $errors = [];

            foreach ($documentIds as $id) {
                $result = $model->updateDocumentStatus((int)$id, 'validée');
                if (!$result) {
                    $errors[] = "Erreur de validation du document ID $id";
                    error_log("Erreur de validation du document ID $id");
                    $success = false;
                }
            }

            echo json_encode([
                'success' => $success,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur dans validateAllDocuments: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Rejette en masse plusieurs documents à partir de leurs identifiants.
     *
     * Cette méthode :
     * - Reçoit une requête JSON contenant une liste d'IDs de documents à rejeter et un commentaire optionnel.
     * - Valide les données reçues.
     * - Utilise le modèle `SecretaryModel` pour mettre à jour chaque document avec le statut "refusée" et le commentaire fourni.
     * - Enregistre les erreurs individuelles et les loggue si une mise à jour échoue.
     * - Retourne une réponse JSON avec le statut global de l'opération et les éventuelles erreurs.
    **/
    public function rejectAllDocuments(): void {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $documentIds = $input['document_ids'] ?? [];
            $comment = $input['comment'] ?? 'Rejeté par le secrétaire';
            
            if (empty($documentIds) || !is_array($documentIds)) {
                throw new \Exception('Liste des IDs de documents requise');
            }

            $model = new SecretaryModel();
            $success = true;
            $errors = [];

            foreach ($documentIds as $id) {
                $result = $model->updateDocumentStatus((int)$id, 'refusée', $comment);
                if (!$result) {
                    $errors[] = "Erreur de rejet du document ID $id";
                    error_log("Erreur de rejet du document ID $id");
                    $success = false;
                }
            }

            echo json_encode([
                'success' => $success,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur dans rejectAllDocuments: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    
    /**
     * Sauvegarde le commentaire d'un document spécifique.
     *
     * Cette méthode :
     * - Reçoit une requête JSON contenant l'ID d'un document et un commentaire à enregistrer.
     * - Valide l'entrée (vérifie la validité du JSON et de l'ID du document).
     * - Utilise le modèle `SecretaryModel` pour mettre à jour le commentaire dans la base de données.
     * - Journalise les étapes pour faciliter le débogage.
     * - Retourne une réponse JSON indiquant le succès ou l'échec de l'opération.
    **/
    public function saveComment(): void {
        // Définir le type de contenu pour JSON
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $documentId = (int)($input['document_id'] ?? 0);
            $comment = $input['comment'] ?? '';
            
            // Validation des données
            if ($documentId <= 0) {
                throw new \Exception('ID du document invalide');
            }
            
            // Log pour debug
            error_log("Sauvegarde commentaire pour document ID: $documentId");
            
            $model = new SecretaryModel();
            $success = $model->saveDocumentComment($documentId, $comment);
            
            if ($success) {
                error_log("Commentaire sauvegardé avec succès pour le document ID: $documentId");
                echo json_encode([
                    'success' => true, 
                    'message' => 'Commentaire sauvegardé'
                ]);
            } else {
                error_log("Échec de sauvegarde du commentaire pour le document ID: $documentId");
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erreur lors de la sauvegarde'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erreur dans saveComment: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    public function uploadConvention(): void
{
    // Vérification de la session secretary
    $secretaryId = $_SESSION['secretary_id'] ?? null;
    if (!$secretaryId) {
        $this->jsonResponse(['success' => false, 'message' => 'Session expirée']);
        return;
    }

    // Vérification de la méthode POST et présence du fichier et demande_id
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['convention']) || empty($_POST['demande_id'])) {
        $this->jsonResponse(['success' => false, 'message' => 'Données manquantes']);
        return;
    }

    try {
        $demandeId = (int)$_POST['demande_id'];
        $file = $_FILES['convention'];
        
        // Récupérer les informations de la demande pour obtenir l'user_id
        $demande = $this->getDemandeById($demandeId);
        if (!$demande) {
            $this->jsonResponse(['success' => false, 'message' => 'Demande non trouvée']);
            return;
        }

        $userId = $demande['user_id'];
        
        // Validation du fichier
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
            return;
        }

        // Création du répertoire utilisateur
        $userDir = __DIR__ . "/../public/uploads/users/$userId/";
        $userPublicPath = "/stalhub/uploads/users/$userId";

        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }

        // Nom du fichier avec l'ID de la demande
        $conventionPath = $userDir . "convention_demande_{$demandeId}.pdf.enc";
        
        if (FileCrypto::encrypt($file['tmp_name'], $conventionPath)) {
            // Sauvegarder dans la table request_documents
            $this->saveConventionToDatabase($demandeId, $userId, $userPublicPath . "/convention_demande_{$demandeId}.pdf.enc");
            
            $this->jsonResponse([
                'success' => true, 
                'message' => 'Convention téléchargée avec succès',
                'demande_id' => $demandeId
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors du chiffrement du fichier']);
        }

    } catch (Exception $e) {
        error_log("Erreur upload convention: " . $e->getMessage());
        $this->jsonResponse(['success' => false, 'message' => 'Erreur serveur lors de l\'upload']);
    }
}

// Méthode pour récupérer une demande par ID
private function getDemandeById(int $demandeId): ?array
{
    try {
        $stmt = $this->db->prepare("SELECT * FROM demandes WHERE id = ?");
        $stmt->execute([$demandeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("Erreur récupération demande: " . $e->getMessage());
        return null;
    }
}

// Méthode pour sauvegarder la convention dans request_documents
private function saveConventionToDatabase(int $demandeId, int $userId, string $conventionPath): void
{
    try {
        // Vérifier si une convention existe déjà pour cette demande
        $stmt = $this->db->prepare("
            SELECT id FROM request_documents 
            WHERE demande_id = ? AND document_type = 'convention'
        ");
        $stmt->execute([$demandeId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Mettre à jour l'existant
            $stmt = $this->db->prepare("
                UPDATE request_documents 
                SET file_path = ?, updated_at = NOW() 
                WHERE demande_id = ? AND document_type = 'convention'
            ");
            $stmt->execute([$conventionPath, $demandeId]);
        } else {
            // Créer un nouveau record
            $stmt = $this->db->prepare("
                INSERT INTO request_documents (demande_id, user_id, document_type, file_path, created_at, updated_at) 
                VALUES (?, ?, 'convention', ?, NOW(), NOW())
            ");
            $stmt->execute([$demandeId, $userId, $conventionPath]);
        }
    } catch (Exception $e) {
        error_log("Erreur sauvegarde convention en base: " . $e->getMessage());
        throw $e;
    }
}

// Méthode pour "envoyer" la convention à l'étudiant
public function sendConventionToStudent(): void
{
    $secretaryId = $_SESSION['secretary_id'] ?? null;
    if (!$secretaryId) {
        $this->jsonResponse(['success' => false, 'message' => 'Session expirée']);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['demande_id'])) {
        $this->jsonResponse(['success' => false, 'message' => 'ID de demande manquant']);
        return;
    }

    try {
        $demandeId = (int)$_POST['demande_id'];
        
        // Vérifier que la convention existe
        $stmt = $this->db->prepare("
            SELECT * FROM request_documents 
            WHERE demande_id = ? AND document_type = 'convention'
        ");
        $stmt->execute([$demandeId]);
        $convention = $stmt->fetch();

        if (!$convention) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucune convention trouvée pour cette demande']);
            return;
        }

        // Marquer la convention comme envoyée
        $stmt = $this->db->prepare("
            UPDATE request_documents 
            SET status = 'sent', sent_at = NOW(), updated_at = NOW() 
            WHERE demande_id = ? AND document_type = 'convention'
        ");
        $stmt->execute([$demandeId]);

        // Optionnel : mettre à jour le statut de la demande
        $stmt = $this->db->prepare("
            UPDATE demandes 
            SET status = 'convention_sent', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$demandeId]);

        $this->jsonResponse([
            'success' => true, 
            'message' => 'Convention envoyée à l\'étudiant avec succès'
        ]);

    } catch (Exception $e) {
        error_log("Erreur envoi convention: " . $e->getMessage());
        $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
    }
}

// Fonction de validation du fichier
private function validateFile($file): array
{
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Le fichier est trop volumineux (max 2 Mo)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Le fichier n\'a été que partiellement téléchargé';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Aucun fichier n\'a été téléchargé';
                break;
            default:
                $errors[] = 'Erreur lors du téléchargement du fichier';
        }
        return $errors;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'Le fichier ne doit pas dépasser 2 Mo';
    }

    $allowedTypes = ['application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Seuls les fichiers PDF sont autorisés';
    }

    return $errors;
}

// Fonction pour envoyer une réponse JSON
private function jsonResponse($data): void
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Fonction de validation du fichier
/*private function validateFile($file): array
{
    $errors = [];
    
    // Vérification des erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Le fichier est trop volumineux (max 2 Mo)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Le fichier n\'a été que partiellement téléchargé';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Aucun fichier n\'a été téléchargé';
                break;
            default:
                $errors[] = 'Erreur lors du téléchargement du fichier';
        }
        return $errors;
    }

    // Vérification de la taille (2 Mo max)
    if ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'Le fichier ne doit pas dépasser 2 Mo';
    }

    // Vérification du type MIME
    $allowedTypes = ['application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Seuls les fichiers PDF sont autorisés';
    }

    return $errors;
}*/

// Fonction pour envoyer une réponse JSON
/*private function jsonResponse($data): void
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}*/

// Fonction pour télécharger/voir la convention
public function downloadConvention(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('HTTP/1.0 403 Forbidden');
        exit('Accès non autorisé');
    }

    $conventionPath = __DIR__ . "/../public/uploads/users/$userId/convention.pdf.enc";
    
    if (!file_exists($conventionPath)) {
        header('HTTP/1.0 404 Not Found');
        exit('Convention non trouvée');
    }

    // Déchiffrement et affichage
    $tempFile = tempnam(sys_get_temp_dir(), 'convention_');
    
    if (FileCrypto::decrypt($conventionPath, $tempFile)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="convention.pdf"');
        readfile($tempFile);
        unlink($tempFile);
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        exit('Erreur lors de la lecture du fichier');
    }
}
}