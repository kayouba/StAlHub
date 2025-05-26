<?php

namespace App\Controller;

use App\View;
use PDO;

class TutorController
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        // 1. Récupérer la capacité du tuteur
        $stmt = $pdo->prepare("SELECT students_to_assign, students_assigned FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();

        // 2. Récupérer les demandes assignées avec toutes les infos
        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                u.first_name AS student_first_name,
                u.last_name AS student_last_name,
                u.email AS student_email,
                u.phone_number AS student_phone_number,
                u.level AS student_level,
                u.program AS student_program,
                u.student_number AS student_student_number,

                c.name AS company_name,
                c.email AS company_email,
                c.siret AS company_siret,
                c.address AS company_address,
                c.postal_code AS company_postal_code,
                c.city AS company_city,
                c.country AS company_country,
                c.details AS company_details

            FROM requests r
            JOIN users u ON r.student_id = u.id
            LEFT JOIN companies c ON r.company_id = c.id
            WHERE r.tutor_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('dashboard/tutor', [
            'students_to_assign' => $userData['students_to_assign'],
            'students_assigned' => $userData['students_assigned'],
            'requests' => $requests
        ]);
    }

    public function updateCapacity(): void
    {
        session_start();

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

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("
            SELECT r.id AS request_id, u.first_name, u.last_name, u.email, r.contract_type, r.start_date, r.end_date
            FROM requests r
            JOIN users u ON r.student_id = u.id
            WHERE r.tutor_id = ? AND r.status = 'VALID_PEDAGO'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $students = $stmt->fetchAll();

        View::render('tutor/students', ['students' => $students]);
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

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                u.first_name AS student_first_name,
                u.last_name AS student_last_name,
                u.email AS student_email,
                u.phone_number AS student_phone_number,
                u.level AS student_level,
                u.program AS student_program,
                u.student_number AS student_student_number,

                c.name AS company_name,
                c.email AS company_email,
                c.siret AS company_siret,
                c.address AS company_address,
                c.postal_code AS company_postal_code,
                c.city AS company_city,
                c.country AS company_country,
                c.details AS company_details

            FROM requests r
            JOIN users u ON r.student_id = u.id
            LEFT JOIN companies c ON r.company_id = c.id
            WHERE r.id = ? AND r.tutor_id = ?
        ");
        $stmt->execute([$requestId, $_SESSION['user_id']]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details) {
            echo "Aucune information trouvée.";
            return;
        }

        View::render('tutor/student-details', ['detail' => $details]);
    }
}
