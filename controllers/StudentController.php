<?php
namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\RequestDocumentModel;
use App\Model\CompanyModel;
use App\Lib\StepGuard;
use App\Lib\FileCrypto;


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

            // ➤ Justificatif (toujours temporaire)
            if (!empty($_FILES['justification']['tmp_name'])) {
                $tempDir = __DIR__ . "/../public/uploads/temp/$userId/";
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $filename = time() . '_' . basename($_FILES['justification']['name']) . '.enc';
                $tempPath = $tempDir . $filename;

                if (FileCrypto::encrypt($_FILES['justification']['tmp_name'], $tempPath)) {
                    $_SESSION['step4']['justification'] = "/stalhub/uploads/temp/$userId/$filename";
                }
            }

            // Redirection
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
        $cvRelative = "/uploads/users/$userId/cv.pdf.enc";
        $cvPath = __DIR__ . '/../public' . $cvRelative;
        if (file_exists($cvPath)) {
            $documentModel->saveDocument($requestId, "/stalhub" . $cvRelative, 'CV');
        }

        // 5. Assurance (profil)
        $assuranceRelative = "/uploads/users/$userId/assurance.pdf.enc";
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
