<?php
declare(strict_types=1);
namespace App\Controller;
use App\Model\ResponsablePedaModel;

class ResponsablePedaController
{
    public function listeDemandes(): void
    {
        $model = new ResponsablePedaModel();
        $demandes = $model->getAll();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/responsable/requestList.php';
    }

    public function detailDemande(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID de demande manquant.";
            return;
        }
       
        $model = new ResponsablePedaModel();
        $demande = $model->getById((int)$id);
       
        if (!$demande) {
            echo "Demande introuvable.";
            return;
        }
       
        // Récupérer la liste des tuteurs pédagogiques
        $tuteurs = $model->getAllTuteurs();
       
        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/responsable/detailRequest.php';
    }

    public function traiter() {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        $commentaire = trim($_POST['commentaire'] ?? '');
        $tuteur_id = !empty($_POST['tuteur_id']) ? (int)$_POST['tuteur_id'] : null;
       
        $model = new ResponsablePedaModel();
       
        if ($action === 'refuser') {
            if (empty($commentaire)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Veuillez saisir un motif de refus."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
           
            $model->traiterDemande($id, $action, $commentaire, null);
           
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "La demande a été refusée avec succès."
            ];
            
        } else if ($action === 'demander_modifications') {
            if (empty($commentaire)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Veuillez préciser les modifications à apporter."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
           
            $model->traiterDemande($id, $action, $commentaire, null);
           
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "Une demande de modifications a été envoyée à l'étudiant."
            ];
            
        } else if ($action === 'valider') {
            // Validation avec tuteur sélectionné manuellement
            if (empty($tuteur_id)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Veuillez affecter un tuteur pédagogique avant de valider la demande."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }

            // Vérifier le quota du tuteur sélectionné
            if (!$model->verifierQuotaTuteur($tuteur_id)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Le tuteur sélectionné a atteint son quota maximum d'étudiants."
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
           
            $model->traiterDemande($id, $action, $commentaire, $tuteur_id);
            
            // Récupérer le nom du tuteur pour le message
            $tuteurNom = $model->getNomTuteur($tuteur_id);
           
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => "La demande a été validée avec succès. Tuteur affecté : " . $tuteurNom
            ];
            
        } else if ($action === 'valider_auto') {
            // Validation avec affectation automatique
            try {
                $tuteur_id = $model->affecterTuteurAutomatiquement();
                
                if (!$tuteur_id) {
                    $_SESSION['flash_message'] = [
                        'type' => 'error',
                        'text' => "Aucun tuteur disponible pour l'affectation automatique. Tous les tuteurs ont atteint leur quota."
                    ];
                    header("Location: /stalhub/responsable/detailRequest?id=$id");
                    exit;
                }
                
                $model->traiterDemande($id, 'valider', $commentaire, $tuteur_id);
                
                // Récupérer le nom du tuteur pour le message
                $tuteurNom = $model->getNomTuteur($tuteur_id);
                
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => "Demande validée avec affectation automatique. Tuteur affecté : " . $tuteurNom
                ];
                
            } catch (Exception $e) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'text' => "Erreur lors de l'affectation automatique : " . $e->getMessage()
                ];
                header("Location: /stalhub/responsable/detailRequest?id=$id");
                exit;
            }
        }
       
        header("Location: /stalhub/responsable/requestList");
        exit;
    }
}