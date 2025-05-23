<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\SecretaryModel;
use App\Model\RequestModel;

class SecretaryController {
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
        $user = $userModel->findRequestInfoById($userId);

        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }
                
        $model = new SecretaryModel();
        $demandes = $model->getAll();

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/dashboard/secretary.php';
    }

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

    public function validateAllDocuments(): void {
        // Définir le type de contenu pour JSON
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

    // NOUVELLE MÉTHODE : Rejeter tous les documents
    public function rejectAllDocuments(): void {
        // Définir le type de contenu pour JSON
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
     * NOUVELLE MÉTHODE : Sauvegarder uniquement le commentaire d'un document
     */
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
}