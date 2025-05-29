<?php

namespace App\Controller;

use App\View;
use App\Lib\Database;
use PDO;

class TutorController
{
    /**
     * Affiche le tableau de bord du tuteur connecté.
     *
     * - Vérifie l’authentification et le rôle de l'utilisateur.
     * - Récupère la capacité d'encadrement du tuteur.
     * - Récupère toutes les demandes qui lui sont assignées.
     * - Identifie les conventions à signer.
     * - Rend la vue `dashboard/tutor` avec les données nécessaires.
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $pdo = Database::getConnection(); 
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

    /**
     * Met à jour la capacité d’encadrement du tuteur (nombre d'étudiants qu’il peut suivre).
     *
     * - Vérifie l’authentification et le rôle.
     * - Met à jour la valeur dans la base de données à partir du POST reçu.
     * - Redirige vers le tableau de bord tuteur.
     */
    public function updateCapacity(): void
    {
        session_start();

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $value = (int) ($_POST['students_to_assign'] ?? 0);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET students_to_assign = ? WHERE id = ?");
        $stmt->execute([$value, $_SESSION['user_id']]);

        header('Location: /stalhub/tutor/dashboard');
        exit;
    }

    /**
     * Affiche la liste des étudiants validés pédagogiquement et assignés au tuteur connecté.
     *
     * - Vérifie l’authentification et le rôle.
     * - Récupère les étudiants liés à ce tuteur via leur demande.
     * - Rend la vue `tutor/students`.
     */
    public function assignedStudents(): void
    {
        session_start();

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tutor') {
            header('Location: /stalhub/login');
            exit;
        }

        $pdo = Database::getConnection();
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

    /**
     * Affiche le détail d'une demande d’un étudiant assigné au tuteur connecté.
     *
     * - Vérifie l’authentification et le rôle.
     * - Récupère la demande avec les données liées à l’étudiant et à l’entreprise.
     * - Rend la vue `tutor/student-details`.
     */
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

        $pdo = Database::getConnection();

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


    /**
     * Affiche la page de signature pour la convention de stage du tuteur.
     *
     * - Vérifie que l'utilisateur est un tuteur et que l'ID de demande est valide.
     * - Récupère le document "convention de stage" prêt à être signé.
     * - Rend la vue `tutor/sign-convention` avec les données.
     * - Affiche une erreur si aucun document valide n'est trouvé.
     */
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
        $pdo = Database::getConnection();

        //  Recherche de la convention validée
        $stmt = $pdo->prepare("
            SELECT * FROM request_documents 
            WHERE request_id = ? 
            AND LOWER(label) = 'convention de stage'
            AND status = 'validated'
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

    /**
     * Enregistre la signature du tuteur pour la convention de stage.
     *
     * Étapes :
     * - Vérifie la validité de la requête et les données reçues (JSON).
     * - Sauvegarde temporaire de la signature (image).
     * - Déchiffrement du PDF, ajout de la signature, puis chiffrement.
     * - Met à jour le statut de la convention en base de données.
     * - Supprime les fichiers temporaires.
     * - Répond avec un message de succès ou d’erreur.
     */
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

        $pdo = Database::getConnection();

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
        $statusModel = new \App\Model\StatusHistoryModel();
        $statusModel->logStatusChange($requestId, 'SIGNED_BY_TUTOR', 'Convention signée par le tuteur.');


        @unlink($signaturePath);
        @unlink($decryptedPdf);
        @unlink($signedPdf);

        echo "Signature du tuteur enregistrée avec succès.";
    }


}
