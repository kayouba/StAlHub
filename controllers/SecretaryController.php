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
            $requestDetails = $requestModel->findById((int)$requestId);
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
     * @return void
    */
    public function updateDocumentStatus(): void {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $id = (int)($input['document_id'] ?? 0);
            $status = $input['status'] ?? '';
            $comment = $input['comment'] ?? null;
            
            if ($id <= 0) {
                throw new \Exception('ID du document invalide');
            }
            
            if (empty($status)) {
                throw new \Exception('Statut requis');
            }
            
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
     *
     * @return void
     */
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
     *
     * @return void
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
     *
     * @return void
    **/
    public function saveComment(): void {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $documentId = (int)($input['document_id'] ?? 0);
            $comment = $input['comment'] ?? '';
            
            if ($documentId <= 0) {
                throw new \Exception('ID du document invalide');
            }
            
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