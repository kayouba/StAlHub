<?php

namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\RequestDocumentModel;
use App\Model\CompanyModel;
use App\Lib\StepGuard;
use App\Lib\FileCrypto;
use App\Lib\PdfGenerator;
use App\Lib\PdfSigner;


class StudentController
{
    /**
     * Affiche le tableau de bord de l'étudiant connecté.
     *
     * - Vérifie la présence d'un utilisateur en session.
     * - Récupère les informations de l'utilisateur via son ID.
     * - Redirige vers la page de login si l'utilisateur est invalide ou non connecté.
     * - Rend la vue du tableau de bord avec les données utilisateur.
     */
    public function dashboard(): void
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
        View::render('dashboard/student', [
            'user' => $user,
        ]);
    }

    /**
     * Affiche l'étape 1 du formulaire de nouvelle demande (informations personnelles).
     *
     * - Vérifie que l'utilisateur est connecté.
     * - Récupère les données de l'utilisateur depuis la base de données.
     * - Redirige vers la page de login si l'utilisateur est invalide ou absent.
     * - Pré-remplit le formulaire avec les données déjà saisies en session (step1), si existantes.
     * - Rend la vue de l'étape 1 avec les données combinées.
     */
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
        $formData = $_SESSION['step1'] ?? [];

        View::render('student/new-request', [
            'user' => array_merge($user, $formData),
            'currentStep' => 1
        ]);
    }

    /**
     * Gère l'affichage de l'étape 2 du formulaire de demande (informations sur le poste).
     *
     * - Si le formulaire précédent (étape 1) a été soumis, les données sont stockées en session.
     * - Récupère les données éventuellement saisies pour l'étape 2 afin de pré-remplir le formulaire.
     * - Rend la vue associée à l'étape 2 du processus de création de demande.
     */
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

    /**
     * Gère l'affichage de l'étape 3 du formulaire de demande (informations sur l’entreprise).
     *
     * - Sauvegarde les données postées de l'étape 2 (poste) en session.
     * - Utilise un garde de sécurité (StepGuard) pour empêcher l'accès direct à l'étape sans avoir validé l'étape précédente.
     * - Pré-remplit le formulaire avec les éventuelles données existantes.
     * - Rend la vue associée à l’étape 3 du processus de création de demande.
     */
    public function step3(): void
    {
        if (!empty($_POST)) {
            $_SESSION['step2'] = $_POST;
        }
        StepGuard::require('step2', '/stalhub/student/request/step2');

        View::render('student/step3', [
            'step3' => $_SESSION['step3'] ?? [],
            'step2' => $_SESSION['step2'] ?? [],
            'currentStep' => 3
        ]);
    }

    /**
     * Gère l’étape 4 du formulaire de demande (téléversement des documents).
     *
     * - Vérifie que l’utilisateur est authentifié.
     * - Sauvegarde les données postées de l’étape 3 si présentes.
     * - Empêche l’accès direct si l’étape 3 n’a pas été complétée (via StepGuard).
     * - Gère les téléversements de fichiers (CV, assurance, justificatif) :
     *     - Sauvegarde dans le dossier utilisateur.
     *     - Création du dossier si nécessaire.
     *     - Mise à jour des données en session.
     * - Pré-remplit les fichiers si déjà présents dans le profil.
     * - Rend la vue associée à l’étape 4.
     */
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
        StepGuard::require('step3', '/stalhub/student/request/step3');

        $userDir = __DIR__ . "/../public/uploads/users/$userId/";
        $userPublicPath = "/stalhub/uploads/users/$userId";

        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }

        // === GESTION UPLOAD AVEC CHIFFREMENT ===
        if (!empty($_FILES)) {
            // var_dump($_FILES);die();
            $errors = [];

            // Liste des fichiers obligatoires (toujours requis)
            $requiredFiles = [
                'cv' => 'CV',
                'insurance' => "Attestation d'assurance",
                'recap_pstage' => "Récapitulatif PStage"
            ];

            // Ajouter les fichiers spécifiques si stage à l'étranger
            $country = $_SESSION['step3']['country'] ?? 'France';
            if ($country === 'Étranger') {
                $requiredFiles += [
                    'social_security' => 'Attestation de sécurité sociale',
                    'cpam' => 'Attestation CPAM',
                    'data_collection_form' => 'Personal data collection form'
                    // Pas obligatoire : accident_protection
                ];
            }

            // Vérification de la présence des fichiers (nouveau ou existant)
            foreach ($requiredFiles as $field => $label) {
                if (empty($_FILES[$field]['tmp_name']) && empty($_SESSION['step4'][$field])) {
                    $errors[] = "Le document obligatoire \"$label\" est manquant.";
                }
            }


            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                header('Location: /stalhub/student/request/step4');
                exit;
            }

            // ➤ CV
            if (!empty($_FILES['cv']['tmp_name'])) {
                $cvPath = $userDir . 'cv.pdf.enc';
                if (FileCrypto::encrypt($_FILES['cv']['tmp_name'], $cvPath)) {
                    $_SESSION['step4']['cv'] = $userPublicPath . '/cv.pdf.enc';
                }
            }

            // ➤ Assurance
            if (!empty($_FILES['insurance']['tmp_name'])) {
                $assurancePath = $userDir . 'assurance.pdf.enc';
                if (FileCrypto::encrypt($_FILES['insurance']['tmp_name'], $assurancePath)) {
                    $_SESSION['step4']['insurance'] = $userPublicPath . '/assurance.pdf.enc';
                }
            }
            // ➤ Récapitulatif PStage (stocké en temporaire pour être déplacé au submit)
            if (!empty($_FILES['recap_pstage']['tmp_name'])) {
                $recapDir = __DIR__ . "/../public/uploads/users/$userId/temp/";
                $recapPublic = "/stalhub/uploads/users/$userId/temp";

                if (!file_exists($recapDir)) mkdir($recapDir, 0777, true);

                $filename = 'recap_pstage.pdf.enc';
                $tempPath = $recapDir . $filename;

                if (FileCrypto::encrypt($_FILES['recap_pstage']['tmp_name'], $tempPath)) {
                    $_SESSION['step4']['recap_pstage'] = $recapPublic . '/' . $filename;
                }
            }

            // ➤ Docs pour l’étranger (stockés temporairement)
            $tmpDir = __DIR__ . "/../public/uploads/users/$userId/temp/";
            $tmpPublic = "/stalhub/uploads/users/$userId/temp";
            if (!file_exists($tmpDir)) mkdir($tmpDir, 0777, true);


            if ($country === 'Étranger') {
                $foreignDocs = [
                    'social_security',
                    'cpam',
                    'data_collection_form',
                    'accident_protection' // facultatif
                ];

                foreach ($foreignDocs as $field) {
                    if (!empty($_FILES[$field]['tmp_name'])) {
                        $filename = $field . '.pdf.enc';
                        $tmpPath = $tmpDir . $filename;
                        if (FileCrypto::encrypt($_FILES[$field]['tmp_name'], $tmpPath)) {
                            $_SESSION['step4'][$field] = $tmpPublic . '/' . $filename;
                        }
                    }
                }
            }


            // Redirection vers l'étape suivante
            header('Location: /stalhub/student/request/step5');
            exit;
        }


        // === Préchargement depuis le profil si pas en session ===
        if (empty($_SESSION['step4']['cv']) && file_exists($userDir . 'cv.pdf.enc')) {
            $_SESSION['step4']['cv'] = $userPublicPath . '/cv.pdf.enc';
        }

        if (empty($_SESSION['step4']['insurance']) && file_exists($userDir . 'assurance.pdf.enc')) {
            $_SESSION['step4']['insurance'] = $userPublicPath . '/assurance.pdf.enc';
        }


        // Affichage
        View::render('student/step4', [
            'step4' => $_SESSION['step4'] ?? [],
            'step3' => $_SESSION['step3'] ?? [],
            'currentStep' => 4
        ]);
    }


    /**
     * Gère l’affichage de l’étape 5 (récapitulatif de la demande).
     *
     * - Vérifie que toutes les étapes précédentes ont été complétées (via StepGuard).
     * - Récupère les données des étapes 1 à 4 depuis la session.
     * - Rend la vue récapitulative de la demande avant soumission.
     */
    public function step5(): void
    {
        StepGuard::requireAll(['step1', 'step2', 'step3', 'step4'], '/stalhub/student/new-request');

        View::render('student/step5', [
            'step1' => $_SESSION['step1'] ?? [],
            'step2' => $_SESSION['step2'] ?? [],
            'step3' => $_SESSION['step3'] ?? [],
            'step4' => $_SESSION['step4'] ?? [],
            'currentStep' => 5
        ]);
    }


    /**
     * Soumet la demande de stage/apprentissage.
     *
     * Étapes :
     * 1. Vérifie que l'utilisateur est connecté.
     * 2. Vérifie que toutes les étapes précédentes ont bien été remplies (via StepGuard).
     * 3. Récupère les données en session (étapes 2 à 4).
     * 4. Crée l'entreprise (ou la retrouve) et enregistre la demande en base.
     * 5. Crée un dossier unique pour les fichiers de la demande.
     * 6. Déplace les documents temporaires (justificatif, CV, assurance) vers le bon dossier.
     * 7. Enregistre les chemins des documents en base.
     * 8. Nettoie les données de session.
     * 9. Redirige vers le tableau de bord avec un message de succès.
     */
    public function submitRequest(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }
        StepGuard::requireAll(['step1', 'step2', 'step3', 'step4'], '/stalhub/student/new-request');

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
        $pdfPath = PdfGenerator::generateFromDatabase($requestId, $uploadDir);
        $publicPdfPath = "/stalhub/uploads/users/$userId/demandes/$requestFolder/summary.pdf.enc";
        $documentModel->saveDocument($requestId, $publicPdfPath, 'Résumé de la demande');


        // 3. Documents obligatoires (CV + assurance)
        $documentFields = [
            'cv' => 'CV',
            'insurance' => 'Assurance',
            'recap_pstage' => 'Récapitulatif PStage'
        ];

        if (($step2['country'] ?? '') === 'Étranger') {
            $documentFields += [
                'social_security' => 'Attestation sécurité sociale',
                'cpam' => 'Attestation CPAM',
                'data_collection_form' => 'Personal Data Collection Form',
                'accident_protection' => 'Formulaire protection accidents du travail'
            ];
        }

        foreach ($documentFields as $field => $label) {
            $sessionPath = $_SESSION['step4'][$field] ?? null;

            if ($sessionPath) {
                $srcPath = realpath(__DIR__ . '/../public' . str_replace('/stalhub', '', $sessionPath));

                if ($srcPath && file_exists($srcPath)) {
                    $filename = $field . '.pdf.enc';
                    $destPath = $uploadDir . $filename;

                    // CV et Assurance doivent être copiés, les autres déplacés
                    if (in_array($field, ['cv', 'insurance'])) {
                        copy($srcPath, $destPath);
                    } else {
                        rename($srcPath, $destPath);
                    }

                    $publicPath = "/stalhub/uploads/users/$userId/demandes/$requestFolder/" . $filename;
                    $documentModel->saveDocument($requestId, $publicPath, $label);
                }
            }
        }


        // 5. Nettoyage session
        unset($_SESSION['step1'], $_SESSION['step2'], $_SESSION['step3'], $_SESSION['step4']);


        // Nettoyer les fichiers temporaires
        $tempDir = __DIR__ . "/../public/uploads/users/$userId/temp/";
        if (file_exists($tempDir)) {
            foreach (glob($tempDir . '*.pdf.enc') as $file) {
                unlink($file);
            }
            rmdir($tempDir);
        }


        // 6. Redirection
        $_SESSION['success_message'] = "Votre demande a bien été soumise.";
        header('Location: /stalhub/dashboard');
        exit;
    }
    /**
     * Affiche les détails d'une demande spécifique pour l'étudiant connecté.
     *
     * Étapes :
     * 1. Vérifie que l'utilisateur est connecté et qu'un ID de demande est fourni.
     * 2. Récupère la demande depuis la base de données, en s'assurant qu'elle appartient bien à l'utilisateur.
     * 3. Récupère les documents associés à cette demande.
     * 4. Si la demande est introuvable, redirige avec un message d'erreur.
     * 5. Sinon, rend la vue avec les données de la demande et ses documents.
     */
    public function viewRequest(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $requestId = $_GET['id'] ?? null;

        if (!$userId || !$requestId) {
            header('Location: /stalhub/dashboard');
            exit;
        }

        $requestModel = new \App\Model\RequestModel();
        $documentModel = new \App\Model\RequestDocumentModel();
        $statusModel = new \App\Model\StatusHistoryModel();

        $request = $requestModel->findByIdForUser($requestId, $userId);
        $documents = $documentModel->getDocumentsForRequest($requestId);
        $statusHistory = $statusModel->getHistoryForRequest($requestId);

        if (!$request) {
            $_SESSION['error'] = "Demande introuvable.";
            header('Location: /stalhub/dashboard');
            exit;
        }

        // Gestion de la convention
        $conventionTo = null;
        $hasSignedConvention = false;

        foreach ($documents as $doc) {
            if (strtolower($doc['label']) === 'convention de stage') {
                if (
                    ( $doc['signed_by_student'] == 0)
                ) {
                    $conventionTo = $doc;
                    break;
                } elseif (
                    isset($doc['signed_by_student']) &&
                    $doc['signed_by_student'] == 1
                ) {
                    $hasSignedConvention = true;
                }
            }
        }

        \App\View::render('student/view-request', [
            'request' => $request,
            'documents' => $documents,
            'statusHistory' => $statusHistory,
            'conventionTo' => $conventionTo,
            'hasSignedConvention' => $hasSignedConvention,
        ]);
    }


    /**
     * Permet à l'étudiant de téléverser une nouvelle version de documents corrigés.
     *
     * - Vérifie que l'utilisateur est authentifié.
     * - Récupère les documents envoyés via POST.
     * - Chiffre et enregistre chaque document corrigé dans un nouveau répertoire horodaté.
     * - Met à jour les documents dans la base avec leur nouveau chemin.
     * - Redirige vers la page de visualisation de la demande avec un message de succès.
     */
    public function uploadCorrection(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /stalhub/login');
            exit;
        }

        $requestId = $_POST['request_id'] ?? null;
        if (!$requestId || empty($_FILES['documents'])) {
            $_SESSION['form_errors'] = ['Aucun document à mettre à jour.'];
            header("Location: /stalhub/student/request/view?id=$requestId");
            exit;
        }

        $documentModel = new RequestDocumentModel();
        $userDir = __DIR__ . "/../public/uploads/users/$userId/demandes/" . date('Y-m-d_His') . "/";
        if (!file_exists($userDir)) mkdir($userDir, 0777, true);

        foreach ($_FILES['documents']['tmp_name'] as $docId => $tmpFile) {
            if (!is_uploaded_file($tmpFile)) continue;

            $filename = $userDir . $docId . '_' . basename($_FILES['documents']['name'][$docId]) . '.enc';
            if (FileCrypto::encrypt($tmpFile, $filename)) {
                $publicPath = str_replace(__DIR__ . '/../public', '/stalhub', $filename);
                $documentModel->replaceDocument($docId, $publicPath, 'submitted');
            }
        }

        $_SESSION['success_message'] = "Documents mis à jour avec succès.";
        header("Location: /stalhub/student/request/view?id=$requestId");
        exit;
    }


    /**
     * Affiche la page de signature de la convention de stage pour l’étudiant.
     *
     * - Vérifie que la demande appartient bien à l’étudiant connecté.
     * - Récupère le document "convention de stage" s’il est prêt à être signé.
     * - Affiche la vue contenant le formulaire de signature.
     * - Affiche une erreur en cas de problème (demande introuvable, pas de convention à signer, etc.).
     */
    public function signConvention(): void
    {
        $requestId = $_GET['id'] ?? null;

        if (!$requestId || !ctype_digit($requestId)) {
            // Gérer erreur ici
            http_response_code(400);
            echo "ID invalide.";
            return;
        }

        $studentId = $_SESSION['user']['id'];
        $requestModel = new RequestModel();
        $request = $requestModel->getRequestWithDocumentsForStudent($requestId, $studentId);

        if (!$request) {
            http_response_code(404);
            echo "Demande introuvable.";
            return;
        }

        // Identifier la convention à signer
        $convention = null;
        foreach ($request['documents'] as $doc) {
            if (
                strtolower($doc['label']) === 'convention de stage' &&
                strtolower($doc['status']) === 'validated' &&
                (empty($doc['signed_by_student']) || $doc['signed_by_student'] == 0)
            ) {
                $convention = $doc;
                break;
            }
        }

        if (!$convention) {
            http_response_code(403);
            echo "Aucune convention à signer.";
            return;
        }

        require __DIR__ . '/../views/student/sign-convention.php';
    }
    
    /**
     * Reçoit et enregistre la signature de l'étudiant pour la convention de stage.
     *
     * Étapes :
     * - Vérifie que la requête est bien POST et que les données sont valides.
     * - Vérifie l'appartenance de la demande à l'étudiant.
     * - Déchiffre la convention, appose la signature sur le PDF, puis le chiffre à nouveau.
     * - Enregistre la signature et met à jour le statut de la demande.
     * - Nettoie les fichiers temporaires utilisés pour l'opération.
     * - Répond avec un message de succès ou d'erreur.
     */
    public function uploadSignature(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Méthode non autorisée.";
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['request_id'], $data['image']) || !is_numeric($data['request_id'])) {
            http_response_code(400);
            echo "Données invalides.";
            return;
        }

        $studentId = $_SESSION['user']['id'] ?? null;
        $requestId = (int) $data['request_id'];
        if (!$studentId || !$requestId) {
            http_response_code(403);
            echo "Non autorisé.";
            return;
        }

        $requestModel = new RequestModel();
        $request = $requestModel->getRequestWithDocumentsForStudent($requestId, $studentId);
        if (!$request) {
            http_response_code(404);
            echo "Demande non trouvée.";
            return;
        }

        $documentModel = new RequestDocumentModel();
        $convention = null;

        foreach ($request['documents'] as $doc) {
            if (
                strtolower($doc['label']) === 'convention de stage' &&
                strtolower($doc['status']) === 'validated' &&
                (empty($doc['signed_by_student']) || $doc['signed_by_student'] == 0)
            ) {
                $convention = $doc;
                break;
            }
        }

        if (!$convention) {
            http_response_code(403);
            echo "Aucune convention valide à signer.";
            return;
        }


        // Sauvegarde temporaire de la signature
        $imageData = explode(',', $data['image'])[1];
        $decoded = base64_decode($imageData);

        $signaturePath = __DIR__ . "/../temp/signature_{$studentId}_{$requestId}.png";
        $tempDir = __DIR__ . '/../temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        file_put_contents($signaturePath, $decoded);

        // Déchiffrer le PDF si crypté
        $pdfPath = __DIR__ . '/../public' . str_replace('/stalhub', '', $convention['file_path']);
        $decryptedPdf = str_replace('.enc', '_temp.pdf', $pdfPath);
        if (!FileCrypto::decrypt($pdfPath, $decryptedPdf)) {
            echo "Échec de déchiffrement.";
            return;
        }

        // Ajouter la signature
        $signatoryName = trim($data['signatory_name'] ?? '');
        if (!$signatoryName) {
            http_response_code(400);
            echo "Nom requis.";
            return;
        }
        $signedPdf = str_replace('.enc', '_signed.pdf', $pdfPath);
        if (!PdfSigner::addSignatureToPdf($decryptedPdf, $signedPdf, $signaturePath, $signatoryName,false )) {
            echo "Échec ajout signature.";
            return;
        }


        // Réencrypter et écraser l’ancien fichier
        if (!FileCrypto::encrypt($signedPdf, $pdfPath)) {
            echo "Erreur de chiffrement.";
            return;
        }

        // Supprimer fichiers temporaires
        @unlink($signaturePath);
        @unlink($decryptedPdf);
        @unlink($signedPdf);

        $documentModel->markAsSignedByStudent($convention['id'], $signatoryName, date('Y-m-d H:i:s'));
        $statusModel = new \App\Model\StatusHistoryModel();
        $statusModel->logStatusChange($requestId, 'SOUMISE', 'Convention signée par l\'étudiant.');


        echo "Signature ajoutée et convention mise à jour.";
    }
}
