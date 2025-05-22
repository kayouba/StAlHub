<?php

namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\SecretaryModel;
use App\Model\RequestModel; 

class SecretaryController
{
    public function dashboard(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== 'secretaire') {
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

    public function detailsFile(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== 'secretaire') {
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

    // NOUVELLE MÉTHODE : Mise à jour du statut d'un document via AJAX
    public function updateDocumentStatus(): void
    {
        // Ajouter du debugging
        error_log("updateDocumentStatus appelée");
        
        // Vérifier que l'utilisateur est bien secrétaire
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        error_log("User ID: $userId, Role: $role");

        if (!$userId || $role !== 'secretaire') {
            error_log("Accès refusé - User ID: $userId, Role: $role");
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        // Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        // Récupérer les données JSON
        $rawInput = file_get_contents('php://input');
        error_log("Raw input: " . $rawInput);
        
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            error_log("Erreur décodage JSON: " . json_last_error_msg());
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Données invalides: ' . json_last_error_msg()]);
            exit;
        }

        $documentId = $input['document_id'] ?? null;
        $status = $input['status'] ?? null;
        $comment = $input['comment'] ?? null;

        error_log("Document ID: $documentId, Status: $status, Comment: $comment");

        // Validation des données
        if (!$documentId || !$status) {
            error_log("Paramètres manquants");
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }

        if (!in_array($status, ['validée', 'refusée'])) {
            error_log("Statut invalide: $status");
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Statut invalide']);
            exit;
        }

        // Mettre à jour le document
        try {
            $secretaryModel = new SecretaryModel();
            $success = $secretaryModel->updateDocumentStatus((int)$documentId, $status, $comment);

            error_log("Résultat mise à jour: " . ($success ? 'SUCCESS' : 'FAILURE'));

            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Document mis à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    public function traiter(): void
{
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? null;

    if (!$userId || $role !== 'secretaire') {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Accès non autorisé.'
        ];
        header('Location: /stalhub/login');
        exit;
    }

    $documentId = (int) ($_POST['document_id'] ?? 0);
    $status = $_POST['status'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    if (!$documentId || !in_array($status, ['validée', 'refusée'])) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Paramètres invalides.'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $model = new SecretaryModel();
    $success = $model->updateDocumentStatus($documentId, $status, $comment);

    $_SESSION['flash_message'] = [
        'type' => $success ? 'success' : 'error',
        'text' => $success ? 'Statut mis à jour avec succès.' : 'Échec de la mise à jour du statut.'
    ];

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
public function validateAllDocuments()
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $requestId = $data['request_id'] ?? null;

    if (!$requestId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID manquant']);
        return;
    }

    // Exemple de mise à jour du statut dans le modèle
    $model = new \App\Model\SecretaryModel();
    $success = $model->validerTousLesDocuments($requestId);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Échec lors de la mise à jour']);
    }
}


}