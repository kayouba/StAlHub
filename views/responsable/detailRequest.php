<!--le tuteur doit etre affecté lors de la validation du responsbale pedago -->

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Détail de la demande</title>
  <link rel="stylesheet" href="/stalhub/public/css/responsable.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>

    <!-- Inclusion de la sidebar commune -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="flash-message <?= $_SESSION['flash_message']['type'] ?>">
            <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
        </div>

        <?php if ($_SESSION['flash_message']['type'] === 'success'): ?>
            <script>
                setTimeout(() => {
                    window.location.href = "/stalhub/responsable/requestList";
                }, 10000);   
            </script>
        <?php endif; ?>

        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Contenu principal avec marge pour la sidebar -->
    <div class="main-content-with-sidebar">
        <div class="container">
            <div class="title-section">
                <h1><i class="fas fa-file-alt"></i> Détail de la demande</h1>
            </div>

           

            <!-- Cards pliables -->
            <div class="card collapsible-card">
                <div class="card-header" onclick="toggleCard('etudiant')">
                    <h2><i class="fas fa-user"></i> Étudiant</h2>
                    <span class="toggle-icon" id="icon-etudiant"><i class="fas fa-plus"></i></span>
                </div>
                <div class="card-content" id="content-etudiant" style="display: none;">
                    <p><strong> Nom :</strong> <?= htmlspecialchars($demande['etudiant'] ?? 'N/A') ?></p>
                    <p><strong> Email :</strong> <?= htmlspecialchars($demande['email'] ?? 'N/A') ?></p>
                    <p><strong> Numéro étudiant :</strong> <?= htmlspecialchars($demande['student_id'] ?? 'N/A') ?></p>
                    <p><strong> Téléphone :</strong> <?= htmlspecialchars($demande['telephone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="card collapsible-card">
                <div class="card-header" onclick="toggleCard('demande')">
                    <h2><i class="fas fa-clipboard-list"></i> Information sur la demande</h2>
                    <span class="toggle-icon" id="icon-demande"><i class="fas fa-plus"></i></span>
                </div>
                <div class="card-content" id="content-demande" style="display: none;">
                    <p><strong> Entreprise :</strong> <?= htmlspecialchars($demande['entreprise']) ?></p>
                    <p><strong> Type :</strong> <?= htmlspecialchars($demande['type'] ?? 'N/A') ?></p>
                    <p><strong> Dates :</strong>
                        <?= htmlspecialchars($demande['start_date'] ?? '') ?> - <?= htmlspecialchars($demande['end_date'] ?? '') ?>
                    </p>
                    <p><strong> Intitulé du poste :</strong> <?= htmlspecialchars($demande['job_title'] ?? $demande['mission']) ?></p>
                    <p><strong> Rémunération :</strong> <?= htmlspecialchars($demande['salary_value'] ?? 0) ?>€/<?= htmlspecialchars($demande['salary_duration'] ?? 'mois') ?></p>
                    
                    <!-- Contact en entreprise -->
                    <p><strong> Contact en entreprise :</strong> <?= htmlspecialchars($demande['referent_email'] ?? 'N/A') ?></p>
                    
                    <!-- Afficher le tuteur pédagogique seulement s'il a été affecté -->
                    <?php if (!empty($demande['tutor_id']) && !empty($demande['tutor_name'])): ?>
                    <p><strong> Tuteur pédagogique :</strong> <?= htmlspecialchars($demande['tutor_name']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card collapsible-card">
                <div class="card-header" onclick="toggleCard('mission')">
                    <h2><i class="fas fa-tasks"></i> Description de la mission</h2>
                    <span class="toggle-icon" id="icon-mission"><i class="fas fa-plus"></i></span>
                </div>
                <div class="card-content" id="content-mission" style="display: none;">
                    <p><?= nl2br(htmlspecialchars($demande['mission'])) ?></p>
                </div>
            </div>

           <div class="card collapsible-card always-open">
    <div class="card-header">
        <h2><i class="fas fa-cogs"></i> Action pédagogique</h2>
    </div>
    <div class="card-content">
        <?php if ($demande['status'] === 'SOUMISE'): ?>
            <?php
            // Récupérer les quotas dynamiques depuis la base de données
            $tuteursQuotas = $model->getTuteursAvecQuotas();
            ?>

            <!-- 1. AFFICHAGE DES QUOTAS EN PREMIER -->
            <div class="quota-info">
                <h4><i class="fas fa-chart-bar"></i> État des quotas des tuteurs pédagogiques :</h4>
                <?php if (!empty($tuteursQuotas)): ?>
                    <div class="quota-grid">
                        <?php foreach ($tuteursQuotas as $tuteur): ?>
                            <div class="quota-item <?= $tuteur['quota_actuel'] >= $tuteur['quota_max'] ? 'quota-complet' : 'quota-ok' ?>">
                                <span class="tuteur-nom"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($tuteur['nom']) ?></span>
                                <span class="quota-badge"><?= $tuteur['quota_actuel'] ?>/<?= $tuteur['quota_max'] ?></span>
                                <div class="quota-barre">
                                    <div class="quota-progression" style="width: <?= $tuteur['quota_max'] > 0 ? ($tuteur['quota_actuel'] / $tuteur['quota_max']) * 100 : 0 ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert info">
                        <p><i class="fas fa-info-circle"></i> Aucun tuteur pédagogique disponible dans le système.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($tuteursQuotas)): ?>
                <form method="post" action="/stalhub/responsable/traiter">
                    <input type="hidden" name="id" value="<?= $demande['id'] ?>" />
                    
                    <!-- 2. AFFECTATION DU TUTEUR -->
                    <div class="form-group affectation-section">
                        <h4><i class="fas fa-chalkboard-teacher"></i> Affectation du tuteur pédagogique</h4>
                        
                        <!-- Option d'affectation automatique -->
                        <div class="auto-affectation-option">
                            <label class="checkbox-container">
                                <input type="checkbox" id="affectationAuto" onchange="toggleAffectationMode()" />
                                <span class="checkmark"></span>
                                <strong><i class="fas fa-magic"></i> Affectation automatique selon les quotas</strong>
                            </label>
                        </div>

                        <!-- Sélection manuelle -->
                        <div id="selectionManuelle" class="selection-manuelle">
                            <label for="tuteur_id"><strong><i class="fas fa-hand-pointer"></i> Choisir un tuteur manuellement :</strong></label>
                            <select name="tuteur_id" id="tuteur_id" class="form-control">
                                <option value="">-- Sélectionner un tuteur --</option>
                                <?php foreach ($tuteurs as $tuteur):
                                    $disponible = $tuteur['quota_actuel'] < $tuteur['quota_max'];
                                    $quotaTexte = "({$tuteur['quota_actuel']}/{$tuteur['quota_max']})";
                                    $disabled = !$disponible ? 'disabled' : '';
                                    $classe = !$disponible ? 'quota-complet' : 'quota-disponible';
                                ?>
                                    <option value="<?= $tuteur['id'] ?>" <?= $disabled ?> class="<?= $classe ?>" 
                                            data-quota-actuel="<?= $tuteur['quota_actuel'] ?>" 
                                            data-quota-max="<?= $tuteur['quota_max'] ?>">
                                        <?= htmlspecialchars($tuteur['nom_complet']) ?> <?= $quotaTexte ?>
                                        <?= !$disponible ? ' - COMPLET' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="button" class="btn btn-suggestion" onclick="suggererTuteur()" style="margin-top: 0.5rem;">
                                <i class="fas fa-lightbulb"></i> Suggérer un tuteur optimal
                            </button>
                        </div>

                        <!-- Affichage du tuteur sélectionné -->
                        <div id="tuteurSelectionne" class="tuteur-selectionne" style="display: none;">
                            <div class="selected-tutor-info">
                                <span class="selected-icon"><i class="fas fa-check-circle"></i></span>
                                <span id="tuteurNomSelectionne"></span>
                                <button type="button" class="btn-change" onclick="switchTutor()">
                                    <i class="fas fa-exchange-alt"></i> Changer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. COMMENTAIRE -->
                    <div class="form-group">
                        <label for="commentaire"><strong><i class="fas fa-comment"></i> Commentaire :</strong></label>
                        <textarea name="commentaire" id="commentaire" class="form-control" 
                                  placeholder="Commentaire obligatoire si refus ..."></textarea>
                    </div>
                    
                    <!-- 4. ACTIONS DE VALIDATION -->
                    <div class="actions-section">
                        <h4><i class="fas fa-clipboard-check"></i> Actions de validation</h4>
                        <div class="actions-buttons">
                            <button class="btn btn-validate" type="submit" name="action" value="valider" id="btnValider" disabled>
                                <i class="fas fa-check"></i> Valider la demande
                            </button>
                            <button class="btn btn-reject" type="submit" name="action" value="refuser">
                                <i class="fas fa-times"></i> Refuser la demande
                            </button>
                        </div>
                        <small class="validation-note">
                            <strong><i class="fas fa-exclamation-triangle"></i> Note :</strong> La validation nécessite l'affectation d'un tuteur pédagogique
                        </small>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert warning">
                    <p><i class="fas fa-exclamation-triangle"></i> Impossible de traiter cette demande : aucun tuteur pédagogique n'est disponible dans le système.</p>
                </div>
            <?php endif; ?>

        
            
       <?php else: ?>
            <p>
                <strong><i class="fas fa-flag"></i> Statut actuel :</strong> 
                <span class="etat <?= $demande['status'] === 'VALID_PEDAGO' ? 'validee' : 'refusee' ?>">
                    <i class="fas fa-<?= $demande['status'] === 'VALID_PEDAGO' ? 'check-circle' : 'times-circle' ?>"></i>
                    <?= $demande['status'] === 'VALID_PEDAGO' ? 'Validée' : 'Refusée' ?>
                </span>
            </p>
            
            <?php if (!empty($demande['tutor_id'])): ?>
                <p><strong><i class="fas fa-chalkboard-teacher"></i> Tuteur pédagogique assigné :</strong> 
                    <?= htmlspecialchars($model->getNomTuteur($demande['tutor_id'])) ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($demande['comment'])): ?>
                <p><strong><i class="fas fa-comment"></i> Commentaire :</strong> <?= nl2br(htmlspecialchars($demande['comment'])) ?></p>
            <?php endif; ?>

            <!-- Section de communication avec l'étudiant -->
            <div class="email-actions" style="margin-top: 2rem; padding: 1.5rem; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
                <h4><i class="fas fa-envelope"></i> Communication avec l'étudiant</h4>
                <p style="margin-bottom: 1rem; color: #6c757d; font-size: 0.9rem;">
                    Informer l'étudiant de l'état de sa demande par email
                </p>
                
                <?php if ($demande['status'] === 'VALID_PEDAGO'): ?>
                    <?php
                    // Préparer le contenu email pour validation
                    $sujet_validation = rawurlencode("✅ Votre demande de " . $demande['type'] . " a été validée - " . $demande['entreprise']);
                    $corps_validation = rawurlencode(
                        "Bonjour " . $demande['etudiant'] . ",\n\n" .
                        "Votre demande de " . $demande['type'] . " chez " . $demande['entreprise'] . " a été validée par le responsable pédagogique.\n\n" .
                        (!empty($demande['tutor_name']) ? "Tuteur pédagogique assigné : " . $demande['tutor_name'] . "\n" .
                        "Votre tuteur vous contactera prochainement pour organiser le suivi de votre " . $demande['type'] . ".\n\n" : "") .
                        (!empty($demande['comment']) ? "📝 Commentaire du responsable : " . $demande['comment'] . "\n\n" : "") .
                        " Prochaine étape :\n" .
                        "Connectez-vous à votre espace étudiant pour télécharger les documents officiels\n" .
                        "Félicitations et bonne réussite dans votre " . $demande['type'] . " !\n\n" .

                        "Cordialement,\nL'équipe pédagogique StaHub"
                    );
                    ?>
                    <a href="mailto:<?= htmlspecialchars($demande['email']) ?>?subject=<?= $sujet_validation ?>&body=<?= $corps_validation ?>" 
                       class="btn btn-success" style="background-color: #28a745; border-color: #28a745;">
                        <i class="fas fa-check-circle"></i> Informer de la validation
                    </a>
                    
                <?php elseif ($demande['status'] === 'REFUSEE_PEDAGO'): ?>
                    <?php
                    // Préparer le contenu email pour refus
                    $sujet_refus = rawurlencode("Information sur votre demande de " . $demande['type'] . " - " . $demande['entreprise']);
                    $corps_refus = rawurlencode(
                        "Bonjour " . $demande['etudiant'] . ",\n\n" .
                        "Nous avons examiné votre demande de " . $demande['type'] . " chez " . $demande['entreprise'] . ".\n\n" .
                        "Malheureusement, cette demande ne peut pas être validée en l'état.\n\n" .
                        (!empty($demande['comment']) ? "- Motif du refus : " . $demande['comment'] . "\n\n" : "") .
                       
                        "N'hésitez pas à contacter le responsable pédagogique pour des clarifications\n\n" .

                        "Cordialement,\nL'équipe pédagogique StaHub"
                    );
                    ?>
                    <a href="mailto:<?= htmlspecialchars($demande['email']) ?>?subject=<?= $sujet_refus ?>&body=<?= $corps_refus ?>" 
                       class="btn btn-reject" style="background-color: #dc3545; border-color: #dc3545;">
                        <i class="fas fa-times-circle"></i> Informer du refus
                    </a>
                    
                <?php else: ?>
                    <?php
                    // Email générique pour autres statuts
                    $sujet_general = rawurlencode("Information sur votre demande de " . $demande['type'] . " - " . $demande['entreprise']);
                    $corps_general = rawurlencode(
                        "Bonjour " . $demande['etudiant'] . ",\n\n" .
                        "Concernant votre demande de " . $demande['type'] . " chez " . $demande['entreprise'] . "...\n\n" .
                        (!empty($demande['comment']) ? "Commentaire :\n" . $demande['comment'] . "\n\n" : "") .
                        "Cordialement,\nL'équipe pédagogique StaHub"
                    );
                    ?>
                    <a href="mailto:<?= htmlspecialchars($demande['email']) ?>?subject=<?= $sujet_general ?>&body=<?= $corps_general ?>" 
                       class="btn btn-secondary" style="background-color: #6c757d; border-color: #6c757d;">
                        <i class="fas fa-envelope"></i> Contacter l'étudiant
                    </a>
                <?php endif; ?>

                
            </div>
            
             <!-- Modifation de l'affectation du tuteur apres validation -->
            <?php if ($demande['status'] === 'VALID_PEDAGO' && !empty($demande['tutor_id'])): ?>
                <div class="post-validation-actions" style="margin-top: 2rem; padding: 1.5rem; background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <h4><i class="fas fa-edit"></i> Gestion du tuteur pédagogique</h4>
                    <p style="color: #856404; margin-bottom: 1rem; font-size: 0.9rem;">
                        Modifier l'affectation du tuteur pour cette demande validée
                    </p>
                    
                    <div class="action-buttons" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <button class="btn btn-warning" onclick="ouvrirModalChangementTuteur()" style="background-color: #ffc107; border-color: #ffc107; color: #212529;">
                            <i class="fas fa-user-edit"></i> Changer le tuteur
                        </button>
                    </div>
                    
                    <!-- Tuteur actuel -->
                    <div class="tuteur-actuel" style="background-color: #d4edda; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #28a745;">
                        <h5><i class="fas fa-user-tie"></i> Tuteur pédagogique actuel :</h5>
                        <p style="margin: 0; font-size: 1.1rem; font-weight: bold; color: #155724;">
                            <?= htmlspecialchars($demande['tutor_name'] ?? 'Non assigné') ?>
                        </p>
                    </div>
                    
                    <!-- Historique des changements -->
                    <?php 
                    $changements = $model->getChangementsTuteur($demande['id']);
                    if (!empty($changements)): 
                    ?>
                        <div class="changements-tuteur" style="background-color: #e9ecef; padding: 1rem; border-radius: 4px;">
                            <h5><i class="fas fa-history"></i> Historique des changements :</h5>
                            <ul style="margin: 0; padding-left: 1.5rem;">
                                <?php foreach ($changements as $changement): ?>
                                    <li style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                                        <strong><?= htmlspecialchars($changement['date_formatee']) ?> :</strong> 
                                        <?= htmlspecialchars($changement['comment']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Modal de changement de tuteur -->
                <div id="modalChangementTuteur" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
                        <h4><i class="fas fa-user-edit"></i> Changer le tuteur pédagogique</h4>
                        
                        <form method="post" action="/stalhub/responsable/switchTutor">
                            <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                            
                            <div style="margin-bottom: 1rem;">
                                <label><strong>Tuteur actuel :</strong></label>
                                <p style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px; margin: 0.5rem 0; color: #495057;">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($demande['tutor_name'] ?? 'Aucun') ?>
                                </p>
                            </div>
                            
                            <div style="margin-bottom: 1rem;">
                                <label for="nouveau_tuteur"><strong>Nouveau tuteur :</strong></label>
                                <select name="nouveau_tuteur" id="nouveau_tuteur" class="form-control" required>
                                    <option value="">-- Sélectionner un nouveau tuteur --</option>
                                    <?php foreach ($tuteurs as $tuteur): ?>
                                        <?php if ($tuteur['id'] != $demande['tutor_id']): ?>
                                            <option value="<?= $tuteur['id'] ?>" 
                                                    <?= $tuteur['quota_actuel'] >= $tuteur['quota_max'] ? 'disabled' : '' ?>>
                                                <?= htmlspecialchars($tuteur['nom_complet']) ?> 
                                                (<?= $tuteur['quota_actuel'] ?>/<?= $tuteur['quota_max'] ?>)
                                                <?= $tuteur['quota_actuel'] >= $tuteur['quota_max'] ? ' - COMPLET' : '' ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div style="margin-bottom: 1rem;">
                                <label for="motif_changement"><strong>Motif du changement :</strong></label>
                                <textarea name="motif" id="motif_changement" class="form-control" rows="3" required 
                                          placeholder="Ex: Tuteur précédent indisponible, réorganisation pédagogique..."></textarea>
                            </div>
                            
                            <div style="text-align: right;">
                                <button type="button" onclick="fermerModal()" class="btn btn-secondary" style="margin-right: 1rem;">
                                    Annuler
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-check"></i> Confirmer le changement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div style="text-align: center; padding: 1rem;">
    <a href="/stalhub/responsable/requestList" class="btn-retour">← Retour</a>
</div>

<!-- Inclusion du fichier JavaScript externe -->
<script src="/stalhub/public/js/responsable-detailRequest.js"></script>
<style>
.sidebar .nav-links a {
    display: flex !important;
    align-items: center !important;
    white-space: nowrap;
}

.sidebar .nav-links a span:first-child {
    margin-right: 12px;
}
</style>

</body>
</html>