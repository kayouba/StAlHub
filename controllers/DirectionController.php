<?php
namespace App\Controller;
use App\View;
use App\Model\UserModel;
use App\Model\DirectionModel;
use App\Model\RequestModel;

class DirectionController
{
    private function checkAccess(): bool{
        return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'director';
    }

    private function redirectLogin(): void{
        header('Location: /stalhub/login');
        exit;
    }

    public function dashboard(): void{
        if (!$this->checkAccess()) {
            $this->redirectLogin();
        }

        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user) {
            session_destroy();
            $this->redirectLogin();
        }

        $directionModel = new DirectionModel();
        $demandes = $directionModel->getAll();

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/dashboard/direction.php';
    }

    public function detailsFile(): void{
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;
        
        if (!$userId || $role !== 'director') {
            header('Location: /stalhub/login');
            exit;
        }

        $requestModel = new RequestModel();
        $directionModel = new DirectionModel();
        $requestId = $_GET['id'] ?? null;
        $requestDetails = null;
        $documents = [];
        $history = [];

        if ($requestId) {
            $requestDetails = $requestModel->findById((int)$requestId);
            // Récupérer les documents/conventions
            $documents = $directionModel->getDocumentsByRequestId((int)$requestId);
            // Récupérer l'historique
            $history = $directionModel->getRequestHistory((int)$requestId);
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/direction/detailsfile.php';
    }

    /**
     * Mettre à jour le commentaire d'un document
     */
    public function updateCommentaire(): void{
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $documentId = $input['document_id'] ?? null;
        $comment = $input['comment'] ?? '';

        if (!$documentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID du document manquant']);
            return;
        }

        $directionModel = new DirectionModel();
        
        try {
            $result = $directionModel->updateDocumentComment((int)$documentId, $comment);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Commentaire mis à jour']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }



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
            
            $model = new DirectionModel();
            $success = $model->saveDocumentComment($documentId, $comment);
            
            if ($success) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Commentaire sauvegardé'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erreur lors de la sauvegarde'
                ]);
            }
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }




    /**
     * Signer un document
     */
    public function signDocument(): void {
        header('Content-Type: application/json');
        
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $documentId = (int)($input['document_id'] ?? 0);
            $action = $input['action'] ?? '';

            if ($documentId <= 0) {
                throw new \Exception('ID du document invalide');
            }

            $model = new DirectionModel();
            
            if ($action === 'sign') {
                $success = $model->updateDocumentStatus($documentId, 'validated');
                $message = 'Document signé avec succès';
            } elseif ($action === 'refuse') {
                $success = $model->updateDocumentStatus($documentId, 'rejected');
                $message = 'Document refusé';
            } else {
                throw new \Exception('Action non valide');
            }

            if ($success) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Refuser un document (alias pour compatibilité)
     */
    public function refuseDocument(): void {
        $this->signDocument();
    }

    /**
     * Valider définitivement un document
     */
    public function validateDocument(): void {
        header('Content-Type: application/json');
        
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $documentId = (int)($input['document_id'] ?? 0);

            if ($documentId <= 0) {
                throw new \Exception('ID du document invalide');
            }

            $model = new DirectionModel();
            $success = $model->updateDocumentStatus($documentId, 'validated_final');

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Document validé définitivement']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Signer tous les documents d'une demande
     */
    public function signAllDocuments(): void {
        header('Content-Type: application/json');
        
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($input['request_id'] ?? 0);

            if ($requestId <= 0) {
                throw new \Exception('ID de la demande invalide');
            }

            $model = new DirectionModel();
            $success = $model->signAllDocumentsByRequest($requestId);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Tous les documents ont été signés']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la signature']);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Valider tous les documents d'une demande
     */
    public function validateAllDocuments(): void {
        header('Content-Type: application/json');
        
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($input['request_id'] ?? 0);

            if ($requestId <= 0) {
                throw new \Exception('ID de la demande invalide');
            }

            $model = new DirectionModel();
            $success = $model->validateAllDocumentsByRequest($requestId);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Tous les documents ont été validés']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Finaliser le dossier
     */
    public function finalizeDossier(): void {
        header('Content-Type: application/json');
        
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($input['request_id'] ?? 0);

            if ($requestId <= 0) {
                throw new \Exception('ID de la demande invalide');
            }

            $model = new DirectionModel();
            $success = $model->finalizeRequest($requestId);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Dossier finalisé avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la finalisation']);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

}