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
                }, 2000);   
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
                                <button type="button" class="btn-change" onclick="changerTuteur()">
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
        <?php endif; ?>
    </div>
</div>
<div style="text-align: center; padding: 1rem;">
    <a href="/stalhub/responsable/requestList" class="btn-retour">← Retour</a>
</div>

<!-- Inclusion du fichier JavaScript externe -->
<script src="/stalhub/public/js/responsable-detailRequest.js"></script>

</body>
</html>