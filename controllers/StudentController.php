<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\RequestDocumentModel;
use App\Model\CompanyModel;
class StudentController
{
    public function dashboard(): void
    {


        $userId = $_SESSION['user_id'] ?? null;
        // echo($userId);

        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

        // Charger l'utilisateur
        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }

        // Appel à la vue du tableau de bord
        View::render('dashboard/student', [
            'user' => $user,
        ]);

    }

    public function newRequest(): void
    {


        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        if (!$user) {
            session_destroy();
            header('Location: /stalhub/login');
            exit;
        }


        // Surcharge les valeurs de l'utilisateur avec les données déjà saisies dans step1
        $formData = $_SESSION['step1'] ?? [];

        View::render('student/new-request', [
            'user' => array_merge($user, $formData),
            'currentStep' => 1
        ]);
    }
    public function step2(): void
    {
        if (!empty($_POST)) {
            $_SESSION['step1'] = $_POST;
        }

        View::render('student/step2', [
            'step2' => $_SESSION['step2'] ?? [],
            'step1' => $_SESSION['step1'] ?? [],
            'currentStep' => 2
        ]);
    }

    public function step3(): void
    {
        if (!empty($_POST)) {
            $_SESSION['step2'] = $_POST;
        }

        View::render('student/step3', [
            'step3' => $_SESSION['step3'] ?? [],
            'step2' => $_SESSION['step2'] ?? [],
            'currentStep' => 3
        ]);
    }

public function step4(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /stalhub/login');
        exit;
    }

    // Sauvegarde des données de step3 si présentes
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
        $_SESSION['step3'] = $_POST;
    }

    $userDir = __DIR__ . "/../public/uploads/users/$userId/";
    $userPublicPath = "/stalhub/uploads/users/$userId";

    if (!file_exists($userDir)) {
        mkdir($userDir, 0777, true);
    }

    // === GESTION UPLOAD ===
    if (!empty($_FILES)) {
        // ➤ CV
        if (!empty($_FILES['cv']['tmp_name'])) {
            $cvPath = $userDir . 'cv.pdf';
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath)) {
                $_SESSION['step4']['cv'] = $userPublicPath . '/cv.pdf';
            }
        }

        // ➤ Assurance
        if (!empty($_FILES['insurance']['tmp_name'])) {
            $assurancePath = $userDir . 'assurance.pdf';
            if (move_uploaded_file($_FILES['insurance']['tmp_name'], $assurancePath)) {
                $_SESSION['step4']['insurance'] = $userPublicPath . '/assurance.pdf';
            }
        }

        // ➤ Justificatif d'identité (toujours spécifique à la demande)
        if (!empty($_FILES['justification']['tmp_name'])) {
            $tempDir = __DIR__ . "/../public/uploads/temp/$userId/";
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $filename = time() . '_' . basename($_FILES['justification']['name']);
            $tempPath = $tempDir . $filename;

            if (move_uploaded_file($_FILES['justification']['tmp_name'], $tempPath)) {
                $_SESSION['step4']['justification'] = "/stalhub/uploads/temp/$userId/$filename";
            }
        }

        // Rediriger après traitement des fichiers
        header('Location: /stalhub/student/request/step5');
        exit;
    }

    // === Préchargement depuis le profil si pas en session ===
    if (empty($_SESSION['step4']['cv']) && file_exists($userDir . 'cv.pdf')) {
        $_SESSION['step4']['cv'] = $userPublicPath . '/cv.pdf';
    }

    if (empty($_SESSION['step4']['insurance']) && file_exists($userDir . 'assurance.pdf')) {
        $_SESSION['step4']['insurance'] = $userPublicPath . '/assurance.pdf';
    }

    // Affichage
    View::render('student/step4', [
        'step4' => $_SESSION['step4'] ?? [],
        'step3' => $_SESSION['step3'] ?? [],
        'currentStep' => 4
    ]);
}



public function step5(): void
{
    View::render('student/step5', [
        'step1' => $_SESSION['step1'] ?? [],
        'step2' => $_SESSION['step2'] ?? [],
        'step3' => $_SESSION['step3'] ?? [],
        'step4' => $_SESSION['step4'] ?? [],
        'currentStep' => 5
    ]);
}



public function submitRequest(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /stalhub/login');
        exit;
    }

    $step3 = $_SESSION['step2'] ?? [];
    $step2 = $_SESSION['step3'] ?? [];

    $step4 = $_SESSION['step4'] ?? [];

    $companyModel = new CompanyModel();
    $requestModel = new RequestModel();
    $documentModel = new RequestDocumentModel();

    // 1. Créer l'entreprise et la demande
    $companyId = $companyModel->findOrCreate($step2);
    $requestId = $requestModel->createRequest($step3, $userId, $companyId, $step2);

    // 2. Créer un dossier spécifique pour cette demande
    $requestFolder = date('Y-m-d_His');
    $uploadDir = __DIR__ . "/../public/uploads/users/$userId/demandes/$requestFolder/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 3. Justificatif (temporaire, spécifique à cette demande)
    if (!empty($step4['justification'])) {

        $srcPath = realpath(__DIR__ . '/../public' . str_replace('/stalhub', '', $step4['justification']));

        if ($srcPath && file_exists($srcPath)) {
            $filename = basename($srcPath);
            $destPath = $uploadDir . $filename;
            rename($srcPath, $destPath);

            $publicPath = "/stalhub/uploads/users/$userId/demandes/$requestFolder/$filename";
            $documentModel->saveDocument($requestId, $publicPath, 'Justificatif');
        } 
    }

    // 4. CV (profil)
    $cvRelative = "/uploads/users/$userId/cv.pdf";
    $cvPath = __DIR__ . '/../public' . $cvRelative;
    if (file_exists($cvPath)) {
        $documentModel->saveDocument($requestId, "/stalhub" . $cvRelative, 'CV');
    } 
    // 5. Assurance (profil)
    $assuranceRelative = "/uploads/users/$userId/assurance.pdf";
    $assurancePath = __DIR__ . '/../public' . $assuranceRelative;
    if (file_exists($assurancePath)) {
        $documentModel->saveDocument($requestId, "/stalhub" . $assuranceRelative, 'Assurance');
    }


    // 6. Nettoyage session
    unset($_SESSION['step1'], $_SESSION['step2'], $_SESSION['step3'], $_SESSION['step4']);

    // 7. Redirection
    $_SESSION['success_message'] = "Votre demande a bien été soumise.";
    header('Location: /stalhub/dashboard');
    exit;
}





    public function viewRequest(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $requestId = $_GET['id'] ?? null;

        if (!$userId || !$requestId) {
            header('Location: /stalhub/dashboard');
            exit;
        }

        $requestModel = new RequestModel();
        $documentModel = new RequestDocumentModel();

        $request = $requestModel->findByIdForUser($requestId, $userId);
        $documents = $documentModel->getDocumentsForRequest($requestId);

        if (!$request) {
            $_SESSION['error'] = "Demande introuvable.";
            header('Location: /stalhub/dashboard');
            exit;
        }

        View::render('student/view-request', [
            'request' => $request,
            'documents' => $documents
        ]);
    }


}
