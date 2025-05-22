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
                    <span class="toggle-icon" id="icon-etudiant"><i class="fas fa-minus"></i></span>
                </div>
                <div class="card-content" id="content-etudiant">
                    <p><strong> Nom :</strong> <?= htmlspecialchars($demande['etudiant'] ?? 'N/A') ?></p>
                    <p><strong> Email :</strong> <?= htmlspecialchars($demande['email'] ?? 'N/A') ?></p>
                    <p><strong> Numéro étudiant :</strong> <?= htmlspecialchars($demande['student_id'] ?? 'N/A') ?></p>
                    <p><strong> Téléphone :</strong> <?= htmlspecialchars($demande['telephone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="card collapsible-card">
                <div class="card-header" onclick="toggleCard('demande')">
                    <h2><i class="fas fa-clipboard-list"></i> Information sur la demande</h2>
                    <span class="toggle-icon" id="icon-demande"><i class="fas fa-minus"></i></span>
                </div>
                <div class="card-content" id="content-demande">
                    <p><strong> Entreprise :</strong> <?= htmlspecialchars($demande['entreprise'] ?? 'N/A') ?></p>
                    <p><strong> Type :</strong> <?= htmlspecialchars($demande['contract_type'] ?? 'N/A') ?></p>
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
                    <span class="toggle-icon" id="icon-mission"><i class="fas fa-minus"></i></span>
                </div>
                <div class="card-content" id="content-mission">
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
                        // Quotas statiques des tuteurs (à remplacer par la base de données plus tard)
                        $tuteursQuotas = [
                            '3' => ['nom' => 'Marie Curie', 'quota_max' => 8, 'quota_actuel' => 3],
                            '4' => ['nom' => 'Pierre Martin', 'quota_max' => 6, 'quota_actuel' => 1],
                            '5' => ['nom' => 'Sophie Bernard', 'quota_max' => 10, 'quota_actuel' => 7],
                            '6' => ['nom' => 'Thomas Petit', 'quota_max' => 5, 'quota_actuel' => 5],
                            '7' => ['nom' => 'Jean Dupont', 'quota_max' => 7, 'quota_actuel' => 2]
                        ];
                        ?>

                        <!-- 1. AFFICHAGE DES QUOTAS EN PREMIER -->
                        <div class="quota-info">
                            <h4><i class="fas fa-chart-bar"></i> État des quotas des tuteurs pédagogiques :</h4>
                            <div class="quota-grid">
                                <?php foreach ($tuteursQuotas as $tuteur): ?>
                                    <div class="quota-item <?= $tuteur['quota_actuel'] >= $tuteur['quota_max'] ? 'quota-complet' : 'quota-ok' ?>">
                                        <span class="tuteur-nom"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($tuteur['nom']) ?></span>
                                        <span class="quota-badge"><?= $tuteur['quota_actuel'] ?>/<?= $tuteur['quota_max'] ?></span>
                                        <div class="quota-barre">
                                            <div class="quota-progression" style="width: <?= ($tuteur['quota_actuel'] / $tuteur['quota_max']) * 100 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

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
                                        <?php foreach ($tuteursQuotas as $id => $tuteur):
                                            $disponible = $tuteur['quota_actuel'] < $tuteur['quota_max'];
                                            $quotaTexte = "({$tuteur['quota_actuel']}/{$tuteur['quota_max']})";
                                            $disabled = !$disponible ? 'disabled' : '';
                                            $classe = !$disponible ? 'quota-complet' : 'quota-disponible';
                                        ?>
                                            <option value="<?= $id ?>" <?= $disabled ?> class="<?= $classe ?>">
                                                <?= htmlspecialchars($tuteur['nom']) ?> <?= $quotaTexte ?>
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

                    <?php elseif ($demande['status'] === 'MODIFICATION_DEMANDEE'): ?>
                        <div class="alert info">
                            <p><strong><i class="fas fa-hourglass-half"></i> Statut actuel :</strong> Modifications demandées</p>
                            <p><i class="fas fa-user-edit"></i> L'étudiant doit apporter des modifications à sa demande.</p>
                            <p><strong><i class="fas fa-comment"></i> Commentaire :</strong> <?= nl2br(htmlspecialchars($demande['comment'])) ?></p>
                        </div>
                        <p><i class="fas fa-clock"></i> Une fois que l'étudiant aura effectué les modifications, la demande repassera en statut "Soumise" et vous pourrez la traiter à nouveau.</p>
                        
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
                                <?php
                                // Correspondance avec les quotas pour l'affichage
                                $tuteursQuotas = [
                                    '3' => 'Marie Curie',
                                    '4' => 'Pierre Martin',
                                    '5' => 'Sophie Bernard',
                                    '6' => 'Thomas Petit',
                                    '7' => 'Jean Dupont'
                                ];
                                echo htmlspecialchars($tuteursQuotas[$demande['tutor_id']] ?? 'Tuteur #' . $demande['tutor_id']);
                                ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($demande['comment'])): ?>
                            <p><strong><i class="fas fa-comment"></i> Commentaire :</strong> <?= nl2br(htmlspecialchars($demande['comment'])) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: center; padding: 1rem;">
                <a href="/stalhub/responsable/requestList" class="btn-retour">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let modeAffectationAuto = false;
        let tuteurAutoSelectionne = null;

        function toggleAffectationMode() {
            const checkbox = document.getElementById('affectationAuto');
            const selectionManuelle = document.getElementById('selectionManuelle');
            const tuteurSelectionne = document.getElementById('tuteurSelectionne');
            const selectTuteur = document.getElementById('tuteur_id');
            
            modeAffectationAuto = checkbox.checked;
            
            if (modeAffectationAuto) {
                // Mode automatique activé
                selectionManuelle.style.display = 'none';
                selectTuteur.value = '';
                
                // Sélectionner automatiquement un tuteur
                affecterTuteurAutomatiquement();
            } else {
                // Mode manuel activé
                selectionManuelle.style.display = 'block';
                tuteurSelectionne.style.display = 'none';
                tuteurAutoSelectionne = null;
                updateValidationButton();
            }
        }

        function affecterTuteurAutomatiquement() {
            // Quotas des tuteurs (même structure que PHP)
            const quotas = {
                '3': {nom: 'Marie Curie', quota_max: 8, quota_actuel: 3},
                '4': {nom: 'Pierre Martin', quota_max: 6, quota_actuel: 1},
                '5': {nom: 'Sophie Bernard', quota_max: 10, quota_actuel: 7},
                '6': {nom: 'Thomas Petit', quota_max: 5, quota_actuel: 5},
                '7': {nom: 'Jean Dupont', quota_max: 7, quota_actuel: 2}
            };
            
            const tuteursDisponibles = [];
            
            // Pondération selon les places libres
            Object.keys(quotas).forEach(id => {
                const tuteur = quotas[id];
                if (tuteur.quota_actuel < tuteur.quota_max) {
                    const placesLibres = tuteur.quota_max - tuteur.quota_actuel;
                    for (let i = 0; i < placesLibres; i++) {
                        tuteursDisponibles.push(id);
                    }
                }
            });
            
            if (tuteursDisponibles.length > 0) {
                const indexAleatoire = Math.floor(Math.random() * tuteursDisponibles.length);
                const tuteurId = tuteursDisponibles[indexAleatoire];
                const tuteurNom = quotas[tuteurId].nom;
                const quotaInfo = `(${quotas[tuteurId].quota_actuel}/${quotas[tuteurId].quota_max})`;
                
                // Stocker la sélection automatique
                tuteurAutoSelectionne = tuteurId;
                
                // Afficher le tuteur sélectionné
                document.getElementById('tuteurNomSelectionne').textContent = `${tuteurNom} ${quotaInfo}`;
                document.getElementById('tuteurSelectionne').style.display = 'block';
                
                updateValidationButton();
            } else {
                alert('Aucun tuteur disponible pour l\'affectation automatique !');
                document.getElementById('affectationAuto').checked = false;
                toggleAffectationMode();
            }
        }

        function suggererTuteur() {
            // Même logique que l'affectation automatique mais pour suggestion
            affecterTuteurAutomatiquement();
            if (tuteurAutoSelectionne) {
                document.getElementById('tuteur_id').value = tuteurAutoSelectionne;
                updateValidationButton();
            }
        }

        function changerTuteur() {
            document.getElementById('affectationAuto').checked = false;
            toggleAffectationMode();
        }

        function updateValidationButton() {
            const btnValider = document.getElementById('btnValider');
            const selectTuteur = document.getElementById('tuteur_id');
            
            const tuteurSelected = selectTuteur.value || tuteurAutoSelectionne;
            
            if (tuteurSelected) {
                btnValider.disabled = false;
                btnValider.classList.add('btn-enabled');
            } else {
                btnValider.disabled = true;
                btnValider.classList.remove('btn-enabled');
            }
        }

        // Event listeners
        if (document.getElementById('tuteur_id')) {
            document.getElementById('tuteur_id').addEventListener('change', updateValidationButton);
        }

        // Ajouter le tuteur automatique au formulaire avant soumission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (modeAffectationAuto && tuteurAutoSelectionne) {
                    // Créer un champ caché pour le tuteur auto-sélectionné
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'tuteur_id';
                    hiddenInput.value = tuteurAutoSelectionne;
                    this.appendChild(hiddenInput);
                }
            });
        }

        // Fonction pour basculer l'état d'une card
        function toggleCard(cardId) {
            const content = document.getElementById('content-' + cardId);
            const icon = document.getElementById('icon-' + cardId);
            const card = content.closest('.collapsible-card');
            
            if (content.style.display === 'none') {
                // Ouvrir la card
                content.style.display = 'block';
                icon.innerHTML = '<i class="fas fa-minus"></i>';
                card.classList.remove('collapsed');
                
                // Animation d'ouverture
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                requestAnimationFrame(() => {
                    content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                });
            } else {
                // Fermer la card
                content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                icon.innerHTML = '<i class="fas fa-plus"></i>';
                card.classList.add('collapsed');
                
                setTimeout(() => {
                    content.style.display = 'none';
                }, 300);
            }
            
            // Sauvegarder l'état dans localStorage
            localStorage.setItem('card-' + cardId, content.style.display === 'none' ? 'closed' : 'open');
        }

        // Fonction pour fermer toutes les cards
        function collapseAll() {
            const cards = ['etudiant', 'demande', 'mission'];
            cards.forEach(cardId => {
                const content = document.getElementById('content-' + cardId);
                if (content.style.display !== 'none') {
                    toggleCard(cardId);
                }
            });
        }

        // Fonction pour ouvrir toutes les cards
        function expandAll() {
            const cards = ['etudiant', 'demande', 'mission'];
            cards.forEach(cardId => {
                const content = document.getElementById('content-' + cardId);
                if (content.style.display === 'none') {
                    toggleCard(cardId);
                }
            });
        }

        // Restaurer l'état des cards au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const cards = ['etudiant', 'demande', 'mission'];
            
            cards.forEach(cardId => {
                const savedState = localStorage.getItem('card-' + cardId);
                if (savedState === 'closed') {
                    // Fermer la card sans animation au chargement
                    const content = document.getElementById('content-' + cardId);
                    const icon = document.getElementById('icon-' + cardId);
                    const card = content.closest('.collapsible-card');
                    
                    content.style.display = 'none';
                    icon.innerHTML = '<i class="fas fa-plus"></i>';
                    card.classList.add('collapsed');
                }
            });
            
            // Initialiser le bouton de validation
            updateValidationButton();
        });
    </script>
</body>
</html>