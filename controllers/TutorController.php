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

    public function assignedStudents(): void
{
    session_start();
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
        header('Location: /stalhub/login');
        exit;
    }

    $pdo = new \PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
    $stmt = $pdo->prepare("
        SELECT r.id AS request_id, u.first_name, u.last_name, u.email, r.contract_type, r.start_date, r.end_date
        FROM requests r
        JOIN users u ON r.student_id = u.id
        WHERE r.tutor_id = ? AND r.status = 'VALID_PEDAGO'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $students = $stmt->fetchAll();

    \App\View::render('tutor/students', ['students' => $students]);
}

    public function viewStudent(): void
    {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $requestId = $_GET['id'] ?? null;
        if (!$requestId) {
            echo "Demande introuvable.";
            return;
        }

        $pdo = new \PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        $stmt = $pdo->prepare("
            SELECT r.*, u.first_name, u.last_name, u.email, u.phone_number, c.name AS company_name
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            WHERE r.id = ? AND r.tutor_id = ?
        ");
        $stmt->execute([$requestId, $_SESSION['user_id']]);
        $details = $stmt->fetch();

        if (!$details) {
            echo "Aucune information trouvée.";
            return;
        }

        \App\View::render('tutor/student-details', ['detail' => $details]);
    }
}