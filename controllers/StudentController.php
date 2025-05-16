<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
// use App\Model\Request;
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

        View::render('student/new-request', [
            'user' => $user
        ]);
    }

    public function step2(): void
    {
        // Ici, tu peux aussi récupérer les données du step1 via $_POST ou session si nécessaire

        $_SESSION['step1'] = $_POST;
        // var_dump($_SESSION);

        View::render('student/step2');
    }

    public function step3(): void
    {

        // Sauvegarde des données du step 2
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['step2'] = $_POST;
        }
            // var_dump($_SESSION);


        View::render('student/step3');
    }

    public function step4(): void
    {

        // Sauvegarde les fichiers ou données précédentes si nécessaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['step3'] = $_POST;
        }
    // var_dump($_SESSION);

        View::render('student/step4');
    }

    // public function step5(): void
    // {

    //     $_SESSION['step4'] = $_FILES; // si fichiers uploadés
    //     // var_dump($_SESSION);

    //     View::render('student/step5');
    // }



    public function step5(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        $step2 = $_SESSION['step2'];
        $step3 = $_SESSION['step3'];
        $step4 = $_FILES;

        $companyModel = new CompanyModel();
        $requestModel = new RequestModel();
        $documentModel = new RequestDocumentModel();

        // 1. Récupère ou crée l'entreprise
        $companyId = $companyModel->findOrCreate($step2);
        // var_dump($companyId);
        // exit;


        // 2. Création de la demande avec company_id
        // $requestId = $requestModel->createRequest($step3, $userId, $companyId);
        $requestId = $requestModel->createRequest($step3, $userId, $companyId, $step2);


        // 3. Upload des fichiers...
        $uploadDir = __DIR__ . '/../../public/uploads/requests/' . $userId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach (['cv', 'insurance', 'justification'] as $field) {
            if (!empty($step4[$field]['tmp_name'])) {
                $tmp = $step4[$field]['tmp_name'];
                $name = time() . '_' . basename($step4[$field]['name']);
                $path = $uploadDir . $name;
                move_uploaded_file($tmp, $path);

                $publicPath = '/uploads/requests/' . $userId . '/' . $name;
                $documentModel->saveDocument($requestId, $publicPath, ucfirst($field));
            }
        }

        header('Location: /stalhub/student/dashboard');
        exit;
    }

}
