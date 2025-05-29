<?php

namespace App\Controller;

use App\View;
use App\Model\RequestModel;
use App\Model\UserModel;
use App\Model\RequestDocumentModel;

/**
 * Contrôleur dédié aux actions du CFA (Centre de Formation des Apprentis).
 *
 * Gère :
 * - L'affichage du tableau de bord CFA.
 * - La validation des demandes d'apprentissage.
 */
class CfaController
{
    /**
     * Affiche le tableau de bord du CFA.
     *
     * Cette méthode :
     * - Récupère toutes les demandes d'apprentissage en attente de validation CFA (`VALID_PEDAGO`)
     *   et celles déjà validées par le CFA (`VALID_CFA`).
     * - Charge les documents associés à chaque demande.
     * - Récupère les valeurs distinctes des programmes, parcours et niveaux pour les filtres.
     * - Rend la vue `/dashboard/cfa` avec les données nécessaires à l'affichage.
     *
     * @return void
     */
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();
        $documentModel = new RequestDocumentModel();

        $pendingRequests = $model->getAllWithStatusAndContract('VALID_PEDAGO', 'apprenticeship');
        $validatedRequests = $model->getAllWithStatusAndContract('VALID_CFA', 'apprenticeship');

        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels = $userModel->getDistinctValues('level');

        // Charger les documents pour les demandes en attente
        foreach ($pendingRequests as &$req) {
            $req['documents'] = [];
            $documents = $documentModel->getDocumentsForRequest((int)$req['id']);
            foreach ($documents as $doc) {
                $req['documents'][] = [
                    'label' => $doc['label'] ?? basename($doc['file_path']),
                    'file_path' => $doc['file_path']
                ];
            }
        }

        // Charger les documents pour les demandes validées
        foreach ($validatedRequests as &$req) {
            $req['documents'] = [];
            $documents = $documentModel->getDocumentsForRequest((int)$req['id']);
            foreach ($documents as $doc) {
                $req['documents'][] = [
                    'label' => $doc['label'] ?? basename($doc['file_path']),
                    'file_path' => $doc['file_path']
                ];
            }
        }

        View::render('/dashboard/cfa', [
            'pendingRequests' => $pendingRequests,
            'validatedRequests' => $validatedRequests,
            'programs' => $programs,
            'tracks' => $tracks,
            'levels' => $levels
        ]);
    }


    /**
     * Valide une demande côté CFA.
     *
     * Cette méthode est appelée via POST :
     * - Elle récupère l'identifiant de la demande transmis en POST.
     * - Met à jour le statut de la demande à `VALID_CFA`.
     * - Définit un message flash de succès dans la session.
     * - Redirige vers le tableau de bord du CFA.
     *
     * @return void
     */
    public function validate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['request_id'])) {
            $model = new RequestModel();
            $model->updateStatus((int)$_POST['request_id'], 'VALID_CFA');
            $_SESSION['success_message'] = "Demande validée par le CFA.";
        }
        header('Location: /stalhub/cfa/dashboard');
        exit;
    }
}
