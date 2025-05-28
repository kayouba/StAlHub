<?php
/**
 * CONTRÔLEUR RESPONSABLE PÉDAGOGIQUE
 * ===================================
 * 
 * Ce contrôleur gère toutes les actions liées à la gestion des demandes
 * par le responsable pédagogique : affichage, validation, refus
 * 
 * @author groupe 1
 * @version 1.0
 */

declare(strict_types=1);
namespace App\Controller;
use App\Model\ResponsablePedaModel;
use App\Model\SignModel; // ← ajoute ceci pour accéder au modèle des documents

class ResponsablePedaController
{
    /**
     * Affiche la liste de toutes les demandes
     * 
     * Cette méthode récupère toutes les demandes depuis la base de données
     * et les affiche dans la vue de liste des demandes.
     * 
     * @return void
     */
    public function listeDemandes(): void
    {
        // Instanciation du modèle pour accéder aux données
        $model = new ResponsablePedaModel();
        $docModel = new SignModel();   // pour les documents
        
        // Récupération de toutes les demandes
        $demandes = $model->getAll();
        
        // Inclusion de la vue pour afficher la liste des demandes
        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/responsable/requestList.php';
    }

    /**
     * Affiche le détail d'une demande spécifique
     * 
     * Cette méthode récupère une demande par son ID et affiche
     * ses détails avec la possibilité de l'accepter ou la refuser.
     * 
     * @return void
     */
    public function detailDemande(): void 
    {
        // Récupération de l'ID depuis les paramètres GET
        $id = $_GET['id'] ?? null;
        
        // Vérification que l'ID est présent
        if (!$id) {
            echo "ID de demande manquant.";
            return;
        }
       
        // Instanciation du modèle
        $model = new ResponsablePedaModel();
        
        // Récupération de la demande par ID
        $demande = $model->getById((int)$id);
       
        // Vérification que la demande existe
        if (!$demande) {
            echo "Demande introuvable.";
            return;
        }
       
        // Récupération de la liste des tuteurs pédagogiques disponibles
        $tuteurs = $model->getAllTuteurs();
       
        // Inclusion de la vue de détail
        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/responsable/detailRequest.php';
    }

    /**
     * Traite une demande (validation, refus)
     * 
     * Cette méthode centrale gère toutes les actions possibles sur une demande :
     * - Validation avec affectation de tuteur (manuelle ou automatique)
     * - Refus avec commentaire obligatoire
     * 
     * 
     * WORKFLOW : Action → Notification immédiate → Redirection automatique
     * 
     * @return void
     */
    public function traiter() 
    {
        // ============================================================================
        // RÉCUPÉRATION ET VALIDATION DES DONNÉES POST
        // ============================================================================
        
        $id = (int)$_POST['id'];                                    // ID de la demande
        $action = $_POST['action'];                                 // Action à effectuer
        $commentaire = trim($_POST['commentaire'] ?? '');           // Commentaire optionnel
        $tuteur_id = !empty($_POST['tuteur_id']) ? (int)$_POST['tuteur_id'] : null; // ID du tuteur
       
        // Instanciation du modèle
        $model = new ResponsablePedaModel();
       
        // ============================================================================
        // TRAITEMENT : REFUS DE LA DEMANDE
        // ============================================================================
        
        if ($action === 'refuser') {
            // Vérification que le commentaire est obligatoire pour un refus
            if (empty($commentaire)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Veuillez saisir un motif de refus."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
           
            // Vérifie si la demande était déjà validée avec un tuteur
            $demandeActuelle = $model->getById($id);
            if ($demandeActuelle['status'] === 'VALID_PEDAGO' && !empty($demandeActuelle['tutor_id'])) {
                // Décremente le quota du tuteur qui était assigné
                $model->decrementerQuotaTuteur($demandeActuelle['tutor_id']);
            }
           
            // Traitement du refus dans la base de données
            $model->traiterDemande($id, $action, $commentaire, null);
           
            // Message de succès avec redirection automatique
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "La demande a été refusée avec succès."
            ];
            
            // Redirection vers le détail pour afficher la notification
            // puis redirection automatique vers la liste
            header("Location: /stalhub/responsable/detailRequest?id=$id&processed=success");
            exit;
            
       
        // ============================================================================
        // TRAITEMENT : VALIDATION AVEC AFFECTATION MANUELLE
        // ============================================================================
        
        } else if ($action === 'valider') {
            // Vérification qu'un tuteur a été sélectionné
            if (empty($tuteur_id)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Veuillez affecter un tuteur pédagogique avant de valider la demande."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }

            // Vérification du quota du tuteur sélectionné
            if (!$model->verifierQuotaTuteur($tuteur_id)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Le tuteur sélectionné a atteint son quota maximum d'étudiants."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
           
            // Vérifie si la demande avait déjà un tuteur assigné
            $demandeActuelle = $model->getById($id);
            if ($demandeActuelle['status'] === 'VALID_PEDAGO' && !empty($demandeActuelle['tutor_id'])) {
                // Si on change de tuteur, décrémenter l'ancien
                if ($demandeActuelle['tutor_id'] != $tuteur_id) {
                    $model->decrementerQuotaTuteur($demandeActuelle['tutor_id']);
                }
            }
           
            // Traitement de la validation avec tuteur assigné
            $model->traiterDemande($id, $action, $commentaire, $tuteur_id);
            
            // Incrémente le quota du tuteur
            $model->incrementerQuotaTuteur($tuteur_id);
            
            // Récupération du nom du tuteur pour le message de confirmation
            $tuteurNom = $model->getNomTuteur($tuteur_id);
           
            // Message de succès avec nom du tuteur et redirection automatique
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "La demande a été validée avec succès. Tuteur affecté : " . $tuteurNom
            ];
            
            // Redirection vers le détail pour afficher la notification
            header("Location: /stalhub/responsable/detailRequest?id=$id&processed=success");
            exit;
            
        // ============================================================================
        // TRAITEMENT : VALIDATION AVEC AFFECTATION AUTOMATIQUE
        // ============================================================================
        
        } else if ($action === 'valider_auto') {
            try {
                // Tentative d'affectation automatique d'un tuteur
                $tuteur_id = $model->affecterTuteurAutomatiquement();
                
                // Vérification qu'un tuteur disponible a été trouvé
                if (!$tuteur_id) {
                    $_SESSION['flash_message'] = [
                        'type' => 'error',
                        'text' => "Aucun tuteur disponible pour l'affectation automatique. Tous les tuteurs ont atteint leur quota."
                    ];
                    header("Location: /stalhub/responsable/detailRequest?id=$id");
                    exit;
                }
                
                // Vérifi si la demande avait déjà un tuteur assigné
                $demandeActuelle = $model->getById($id);
                if ($demandeActuelle['status'] === 'VALID_PEDAGO' && !empty($demandeActuelle['tutor_id'])) {
                    // Si on change de tuteur, décrémenter l'ancien
                    if ($demandeActuelle['tutor_id'] != $tuteur_id) {
                        $model->decrementerQuotaTuteur($demandeActuelle['tutor_id']);
                    }
                }
                
                // Traitement de la validation avec le tuteur automatiquement assigné
                $model->traiterDemande($id, 'valider', $commentaire, $tuteur_id);
                
                // Incrémente le quota du tuteur
                $model->incrementerQuotaTuteur($tuteur_id);
                
                // Récupération du nom du tuteur pour le message de confirmation
                $tuteurNom = $model->getNomTuteur($tuteur_id);
                
                // Message de succès avec détail de l'affectation automatique
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => "Demande validée avec affectation automatique. Tuteur affecté : " . $tuteurNom
                ];
                
                //  Redirection vers le détail pour afficher la notification
                header("Location: /stalhub/responsable/detailRequest?id=$id&processed=success");
                exit;
                
            } catch (Exception $e) {
                // Gestion des erreurs lors de l'affectation automatique
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Erreur lors de l'affectation automatique : " . $e->getMessage()
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
        }
       
        // ============================================================================
        // REDIRECTION FINALE (FALLBACK)
        // ============================================================================
        
        // Redirection de sécurité si aucune action n'a été reconnue
        header("Location: /stalhub/responsable/requestList");
        exit;
    }
        /**
     * Traite le changement de tuteur
     */
    public function switchTutor(): void
    {
        $demande_id = (int)$_POST['demande_id'];
        $nouveau_tuteur_id = (int)$_POST['nouveau_tuteur'];
        $motif = trim($_POST['motif']);
        
        if (empty($motif)) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'text' => 'Le motif du changement est obligatoire'
            ];
            header("Location: /stalhub/responsable/detailRequest?id=$demande_id");
            exit;
        }
        
        $model = new ResponsablePedaModel();
        
        if ($model->updateRequestTutor($demande_id, $nouveau_tuteur_id, $motif)) {
            $nouveauTuteurNom = $model->getNomTuteur($nouveau_tuteur_id);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "Tuteur changé avec succès. Nouveau tuteur : $nouveauTuteurNom"
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'text' => 'Erreur lors du changement de tuteur'
            ];
        }
        
        header("Location: /stalhub/responsable/detailRequest?id=$demande_id");
        exit;
    }
/**
 * Génère un lien de signature pour la convention d'entreprise 
 */

    public function genererLienSignatureEntreprise(): void
{
    $requestId = $_GET['id'] ?? null;

    if (!$requestId) {
        echo "ID de la demande manquant.";
        return;
    }

    $model = new \App\Model\SignModel();

    if (!$model->conventionExistePourDemande((int)$requestId)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => "Aucune convention trouvée pour cette demande."
        ];
        header("Location: /stalhub/responsable/detailRequest?id=$requestId");
        return;
    }

    $token = $model->generateCompanySignatureToken((int)$requestId);
    $link = "https://stalhub/signature/convention?token=$token";

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => "Lien de signature généré : <a href=\"$link\" target=\"_blank\">$link</a>"
    ];

    header("Location: /stalhub/responsable/detailRequest?id=$requestId");
    exit;
}

}