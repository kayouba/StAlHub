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


        // 3. Vérifier la signature complète de la convention
        foreach ($requests as &$req) {
            $stmtDocs = $pdo->prepare("SELECT * FROM request_documents WHERE request_id = ?");
            $stmtDocs->execute([$req['id']]);
            $documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            $req['convention_fully_signed'] = false;
            $req['signed_convention_path'] = null;

            foreach ($documents as $doc) {
                if (
                    strtolower($doc['label']) === 'convention de stage' &&
                    strtolower($doc['status']) === 'validated' &&
                    ($doc['signed_by_student'] ?? 0) == 1 &&
                    ($doc['signed_by_direction'] ?? 0) == 1 &&
                    (empty($doc['signed_by_tutor']) || $doc['signed_by_tutor'] == 0)
                ) {
                    $req['can_sign_convention'] = true;
                    $req['signed_convention_path'] = $doc['file_path'] ?? null;
                    break;
                }

            }
        }


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


    public function signConvention(): void
    {
        //  Vérification de session et rôle

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        //  Récupération de l'ID de la demande
        $requestId = $_GET['id'] ?? null;

        if (!$requestId || !ctype_digit($requestId)) {
            http_response_code(400);
            echo "ID de demande invalide.";
            return;
        }

        //  Connexion à la base de données
        $pdo = new \PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        //  Recherche de la convention validée
        $stmt = $pdo->prepare("
            SELECT * FROM request_documents 
            WHERE request_id = ? 
            AND LOWER(label) = 'convention de stage'
            AND status = 'validated'
            AND signed_by_student = 1
            AND (signed_by_tutor IS NULL OR signed_by_tutor = 0)
        ");

        $stmt->execute([$requestId]);
        $convention = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Si aucune convention valide à signer n'est trouvée
        if (!$convention) {
            http_response_code(403);
            echo "Aucune convention à signer ou déjà signée.";
            return;
        }

        //  Affichage de la vue de signature pour le tuteur
        \App\View::render('tutor/sign-convention', [
            'convention' => $convention,
            'requestId' => $requestId
        ]);
    }

public function uploadSignature(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo "Méthode non autorisée.";
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['request_id'], $data['image'], $data['signatory_name']) || !is_numeric($data['request_id'])) {
        http_response_code(400);
        echo "Données invalides.";
        return;
    }

    $tutorId = $_SESSION['user_id'] ?? null;
    $requestId = (int)$data['request_id'];
    $signatoryName = trim($data['signatory_name']);

    if (!$tutorId || !$requestId || !$signatoryName) {
        http_response_code(403);
        echo "Non autorisé ou nom manquant.";
        return;
    }

    $pdo = new \PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM request_documents WHERE request_id = ? AND LOWER(label) = LOWER('convention de stage')");
    $stmt->execute([$requestId]);
    $doc = $stmt->fetch();

    if (!$doc || (int)$doc['signed_by_tutor'] === 1) {
        http_response_code(404);
        echo "Document introuvable ou déjà signé.";
        return;
    }

    // === Étape 1 : Sauvegarde de la signature
    $imageData = explode(',', $data['image'])[1];
    $decoded = base64_decode($imageData);
    $tempDir = __DIR__ . '/../temp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $signaturePath = $tempDir . "signature_tutor_{$tutorId}_{$requestId}.png";
    file_put_contents($signaturePath, $decoded);

    // === Étape 2 : Déchiffrement du PDF original
    $pdfPath = __DIR__ . '/../public' . str_replace('/stalhub', '', $doc['file_path']);
    $decryptedPdf = str_replace('.enc', '_temp.pdf', $pdfPath);

    if (!\App\Lib\FileCrypto::decrypt($pdfPath, $decryptedPdf)) {
        http_response_code(500);
        echo "Échec de déchiffrement.";
        return;
    }

    // === Étape 3 : Ajout de la signature
    $signedPdf = str_replace('.enc', '_signed.pdf', $pdfPath);
    $success = \App\Lib\PdfSigner::addSignatureToPdf(
        $decryptedPdf,
        $signedPdf,
        $signaturePath,
        $signatoryName,
        false,
        true// ← Position en bas à gauche pour le tuteur
    );

    if (!$success) {
        http_response_code(500);
        echo "Erreur lors de l'ajout de la signature.";
        return;
    }

    // === Étape 4 : Réencryption du PDF
    if (!\App\Lib\FileCrypto::encrypt($signedPdf, $pdfPath)) {
        http_response_code(500);
        echo "Erreur lors du chiffrement final.";
        return;
    }

    // === Étape 5 : Mise à jour BDD
    $stmt = $pdo->prepare("UPDATE request_documents SET 
        signed_by_tutor = 1,
        tutor_signed_at = NOW(),
        tutor_signatory_name = :name
        WHERE id = :doc_id
    ");
    $stmt->execute([
        'name' => $signatoryName,
        'doc_id' => $doc['id']
    ]);

    @unlink($signaturePath);
    @unlink($decryptedPdf);
    @unlink($signedPdf);

    echo "Signature du tuteur enregistrée avec succès.";
}


}
