<?php

namespace App\Controller;

use App\View;
use PDO;

class TutorController
{
    public function index(): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("SELECT students_to_assign, students_assigned FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $data = $stmt->fetch();

        View::render('dashboard/tutor', $data);
    }

    public function updateCapacity(): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $value = (int) ($_POST['students_to_assign'] ?? 0);

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("UPDATE users SET students_to_assign = ? WHERE id = ?");
        $stmt->execute([$value, $_SESSION['user_id']]);

        header('Location: /stalhub/tutor/dashboard');
        exit;
    }
}