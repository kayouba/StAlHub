<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;

class StudentController
{
    public function dashboard(): void
    {
        session_start();


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
    session_start();

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
    session_start();
    $_SESSION['step1'] = $_POST;

    View::render('student/step2');
}

    public function step3(): void
    {
        session_start();

        // Sauvegarde des données du step 2
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['step2'] = $_POST;
        }

        View::render('student/step3');
    }

    public function step4(): void
    {
        session_start();

        // Sauvegarde les fichiers ou données précédentes si nécessaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['step3'] = $_POST;
        }

        View::render('student/step4');
    }

    public function step5(): void
    {
        session_start();
        $_SESSION['step4'] = $_FILES; // si fichiers uploadés
        View::render('student/step5');
    }



}
