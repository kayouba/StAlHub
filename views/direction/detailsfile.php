<?php include __DIR__ . '/../components/sidebar.php'; ?>
<link rel="stylesheet" href="/stalhub/public/css/direction-detailsfile.css">
<script src="/stalhub/public/js/direction-detailsfile.js" defer></script>

<?php
// Fonction utilitaire pour éviter les erreurs avec htmlspecialchars() sur une valeur null
function safe($value, $default = 'Non précisé') {
    return htmlspecialchars($value ?? $default);
}
?>

<div class="main-content">
    <?php if (!$requestDetails): ?>
        <p>Demande introuvable.</p>
    <?php else: ?>
        <h2>Détails de la Demande (Espace Direction)</h2>
        
        <!-- Section Étudiant -->
        <div class="section">
            <h3>Étudiant</h3>
            <p><span class="label">Nom :</span> <span class="value"><?= safe($requestDetails['last_name']) ?></span></p>
            <p><span class="label">Prénom :</span> <span class="value"><?= safe($requestDetails['first_name']) ?></span></p>
            <p><span class="label">Numéro d'Étudiant :</span> <span class="value"><?= safe($requestDetails['student_number']) ?></span></p>
            <p><span class="label">Formation :</span> <span class="value"><?= safe($requestDetails['level']) ?></span></p>
            <p><span class="label">Type de contrat :</span> <span class="value"><?= safe($requestDetails['contract_type']) ?></span></p>
        </div>

        <!-- Section Demande -->
        <div class="section">
            <h3>Demande</h3>
            <p><span class="label">Intitulé du poste :</span> <span class="value"><?= safe($requestDetails['job_title']) ?></span></p>
            <p><span class="label">Mission :</span> <span class="value"><?= safe($requestDetails['mission']) ?></span></p>
            <p><span class="label">Volume Horaire :</span> <span class="value"><?= safe($requestDetails['weekly_hours']) ?></span></p>
            <p><span class="label">Tuteur :</span> <span class="value"><?= safe($requestDetails['supervisor']) ?></span></p>
            <p><span class="label">Numéro e-sup :</span> <span class="value"><?= safe($requestDetails['student_number']) ?></span></p>
            <p><span class="label">Durée :</span> <span class="value">3 mois</span></p>
        </div>

        <!-- Section Conventions et Avenants -->
        <div class="section">
            <h3>Conventions et Avenants</h3>
            <?php if (empty($documents)): ?>
                <p class="no-documents">Aucune convention ou avenant disponible.</p>
            <?php else: ?>
                <div class="documents-container">
                    <?php foreach ($documents as $doc): ?>
                        <div class="document-card" data-document-id="<?= $doc['id'] ?>">
                            <div class="document-header">
                                <h4 class="document-title">
                                    📄 <?= safe($doc['label']) ?>
                                </h4>
                                <span class="document-status">
                                    <?php 
                                    $statusClass = '';
                                    $statusText = '';
                                    switch(strtolower($doc['status'])) {
                                        case 'attente':
                                        case 'en_attente':
                                            $statusClass = 'status-pending';
                                            $statusText = 'En cours';
                                            break;
                                        case 'signee':
                                        case 'accepte':
                                            $statusClass = 'status-signed';
                                            $statusText = 'Accepté';
                                            break;
                                        case 'refuse':
                                        case 'refusee':
                                            $statusClass = 'status-refused';
                                            $statusText = 'Refusé';
                                            break;
                                        case 'validee_finale':
                                            $statusClass = 'status-validated';
                                            $statusText = 'Validé';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusText = 'En cours';
                                    }
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                </span>
                            </div>
                            
                            <div class="document-body">
                                <div class="document-info">
                                    <p><strong>Type :</strong> <?= ucfirst(safe($doc['type'])) ?></p>
                                    <p><strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></p>
                                    <?php if ($doc['updated_at']): ?>
                                        <p><strong>Modifié le :</strong> <?= date('d/m/Y H:i', strtotime($doc['updated_at'])) ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Section Commentaire -->
                                <div class="comment-section">
                                    <label for="comment-<?= $doc['id'] ?>"><strong>Commentaire :</strong></label>
                                    <textarea class="comment-input" 
                                              id="comment-<?= $doc['id'] ?>" 
                                              placeholder="Ajouter un commentaire..."
                                              rows="3"><?= safe($doc['comment'] ?? '') ?></textarea>
                                    <button class="btn btn-save-comment" 
                                            onclick="saveComment(<?= $doc['id'] ?>)">
                                        💾 Sauvegarder le commentaire
                                    </button>
                                </div>

                                <div class="document-actions">
                                    <!-- Bouton pour voir le fichier PDF -->
                                    <div class="attachment-section">
                                        <h5>📎 Pièce jointe :</h5>
                                        <?php if (!empty($doc['file_path'])): ?>
                                            <a href="<?= htmlspecialchars($doc['file_path']) ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="btn btn-view">
                                                📄 Voir la convention
                                            </a>
                                        <?php else: ?>
                                            <span class="no-file">❌ Aucun fichier joint</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <?php if (in_array(strtolower($doc['status']), ['attente', 'en_attente'])): ?>
                                        <div class="action-buttons">
                                            <button class="btn btn-sign" 
                                                    onclick="signDocument(<?= $doc['id'] ?>, 'sign')"
                                                    data-document-id="<?= $doc['id'] ?>">
                                                ✅ Signer
                                            </button>
                                            <button class="btn btn-refuse" 
                                                    onclick="signDocument(<?= $doc['id'] ?>, 'refuse')"
                                                    data-document-id="<?= $doc['id'] ?>">
                                                ❌ Refuser
                                            </button>
                                        </div>
                                    <?php elseif (in_array(strtolower($doc['status']), ['signee', 'accepte'])): ?>
                                        <div class="validation-section">
                                            <button class="btn btn-validate" 
                                                    onclick="validateDocument(<?= $doc['id'] ?>)"
                                                    data-document-id="<?= $doc['id'] ?>">
                                                ✅ Valider définitivement
                                            </button>
                                        </div>
                                    <?php elseif (strtolower($doc['status']) === 'validee_finale'): ?>
                                        <div class="status-final">
                                            <span class="badge badge-success">✅ Validé définitivement</span>
                                        </div>
                                    <?php elseif (in_array(strtolower($doc['status']), ['refuse', 'refusee'])): ?>
                                        <div class="status-refused">
                                            <span class="badge badge-danger">❌ Refusé</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Actions globales -->
                <div class="actions-section">
                    <?php 
                    $hasUnsignedDocuments = false;
                    $hasSignedDocuments = false;
                    $allValidated = true;
                    $hasDocuments = !empty($documents);
                    
                    foreach ($documents as $doc) {
                        if (in_array(strtolower($doc['status']), ['attente', 'en_attente'])) {
                            $hasUnsignedDocuments = true;
                            $allValidated = false;
                        } elseif (in_array(strtolower($doc['status']), ['signee', 'accepte'])) {
                            $hasSignedDocuments = true;
                            $allValidated = false;
                        } elseif (!in_array(strtolower($doc['status']), ['validee_finale'])) {
                            $allValidated = false;
                        }
                    }
                    ?>
                    
                    <?php if ($hasUnsignedDocuments): ?>
                        <button class="btn btn-validate-all" onclick="signAllDocuments()" id="signAllBtn">
                            ✅ Signer toutes les pièces jointes
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($hasSignedDocuments): ?>
                        <button class="btn btn-validate-all" onclick="validateAllDocuments()" id="validateAllBtn">
                            🔒 Valider toutes les pièces jointes
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($allValidated && $hasDocuments): ?>
                        <button class="btn btn-finalize" 
                                onclick="finalizeDossier(<?= $requestDetails['id'] ?>)"
                                id="finalizeBtn">
                            🏁 Finaliser le dossier
                        </button>
                    <?php endif; ?>
                    
                    <div class="global-message" id="globalMessage"></div>
                </div>

                <!-- Messages d'état -->
                <?php if (!$allValidated && $hasDocuments): ?>
                    <div class="pending-validation">
                        <p class="warning-note">
                            ⚠️ Toutes les conventions et avenants doivent être signés et validés avant de pouvoir finaliser le dossier.
                        </p>
                    </div>
                <?php elseif ($allValidated && $hasDocuments): ?>
                    <div class="finalize-section">
                        <p class="finalize-note">
                            ✅ Toutes les conventions et avenants ont été validés. Vous pouvez maintenant finaliser le dossier.
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Section Historique -->
        <div class="section">
            <h3>Historique des Actions</h3>
            <?php if (empty($history)): ?>
                <p class="no-history">Aucun historique disponible.</p>
            <?php else: ?>
                <div class="history-container">
                    <?php foreach ($history as $entry): ?>
                        <div class="history-entry">
                            <div class="history-date">
                                <?= date('d/m/Y H:i', strtotime($entry['created_at'])) ?>
                            </div>
                            <div class="history-content">
                                <div class="history-action">
                                    <?= safe($entry['details']) ?>
                                </div>
                                <?php if ($entry['user_name']): ?>
                                    <div class="history-user">
                                        Par : <?= safe($entry['user_name']) ?> 
                                        (<?= ucfirst(safe($entry['user_role'])) ?>)
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<!-- Modal de confirmation -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3 id="modalTitle">Confirmation</h3>
        <p id="modalMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
        <div class="modal-actions">
            <button class="btn btn-cancel" onclick="closeModal()">Annuler</button>
            <button class="btn btn-confirm" id="confirmButton">Confirmer</button>
        </div>
    </div>
</div>

<!-- Messages de notification -->
<div id="notification" class="notification" style="display: none;"></div>



<script>
</script>
