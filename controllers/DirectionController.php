<?php
namespace App\Controller;
use App\View;
use App\Model\UserModel;
use App\Model\DirectionModel;
use App\Model\RequestModel;

class DirectionController
{
    private function checkAccess(): bool{
        return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'direction';
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
        
        if (!$userId || $role !== 'direction') {
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
     * Signer un document individuel
     */
    public function signerDocument(): void{
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $documentId = $input['document_id'] ?? null;
        $action = $input['action'] ?? null; // 'sign' ou 'refuse'
        $comment = $input['comment'] ?? '';

        if (!$documentId || !$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }

        $directionModel = new DirectionModel();
        
        try {
            $result = $directionModel->updateDocumentStatus(
                (int)$documentId, 
                $action === 'sign' ? 'signee' : 'refuse',
                $_SESSION['user_id'],
                $comment
            );

            if ($result) {
                // Ajouter à l'historique
                $actionText = $action === 'sign' ? 'Document signé par la Direction' : 'Document refusé par la Direction';
                $directionModel->addToHistory($documentId, $actionText, $_SESSION['user_id']);

                echo json_encode(['success' => true, 'message' => 'Action effectuée avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Valider définitivement un document
     */
    public function validerDocument(): void{
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
            $result = $directionModel->updateDocumentStatus(
                (int)$documentId, 
                'validee_finale',
                $_SESSION['user_id'],
                $comment
            );

            if ($result) {
                // Ajouter à l'historique
                $directionModel->addToHistory($documentId, 'Document validé définitivement par la Direction', $_SESSION['user_id']);

                echo json_encode(['success' => true, 'message' => 'Document validé définitivement']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Signer tous les documents en attente
     */
    public function signerTousDocuments(): void{
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $requestId = $input['request_id'] ?? null;

        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de la demande manquant']);
            return;
        }

        $directionModel = new DirectionModel();
        
        try {
            $result = $directionModel->signAllPendingDocuments((int)$requestId, $_SESSION['user_id']);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true, 
                    'message' => $result['count'] . ' document(s) signé(s) avec succès'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Valider tous les documents signés
     */
    public function validerTousDocuments(): void{
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $requestId = $input['request_id'] ?? null;

        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de la demande manquant']);
            return;
        }

        $directionModel = new DirectionModel();
        
        try {
            $result = $directionModel->validateAllSignedDocuments((int)$requestId, $_SESSION['user_id']);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true, 
                    'message' => $result['count'] . ' document(s) validé(s) définitivement'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Finaliser le dossier complet
     */
    public function finaliserDossier(): void{
        if (!$this->checkAccess()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $requestId = $input['request_id'] ?? null;

        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de la demande manquant']);
            return;
        }

        $directionModel = new DirectionModel();
        
        try {
            $result = $directionModel->finalizeDossier((int)$requestId, $_SESSION['user_id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Dossier finalisé avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la finalisation']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
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
        // Définir le type de contenu pour JSON
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide');
            }
            
            $commentId = (int)($input['student_id'] ?? 0);
            $comment = $input['comment'] ?? '';
            
            // Validation des données
            if ($commentId <= 0) {
                throw new \Exception('ID du document invalide');
            }
            
            // Log pour debug
            error_log("Sauvegarde commentaire pour document ID: $commentId");
            
            $model = new DirectionModel();
            $success = $model->saveDocumentComment($commentId, $comment);
            
            if ($success) {
                error_log("Commentaire sauvegardé avec succès pour le document ID: $commentId");
                echo json_encode([
                    'success' => true, 
                    'message' => 'Commentaire sauvegardé'
                ]);
            } else {
                error_log("Échec de sauvegarde du commentaire pour le document ID: $commentId");
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