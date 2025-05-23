<?php include __DIR__ . '/../components/sidebar.php'; ?>

<link rel="stylesheet" href="/stalhub/public/css/secretary-detailsfile.css">
<script src="/stalhub/public/js/secretary-detailsfile.js" defer></script>
<div class="main-content">
  <?php if (!$requestDetails): ?>
    <p>Demande introuvable.</p>
  <?php else: ?>
    <h2>Détails de la Demande</h2>

    <div class="section">
      <h3>Étudiant</h3>
      <p><span class="label">Nom :</span> <span class="value"><?= htmlspecialchars($requestDetails['last_name']) ?></span></p>
      <p><span class="label">Prénom :</span> <span class="value"><?= htmlspecialchars($requestDetails['first_name']) ?></span></p>
      <p><span class="label">Numéro d'Étudiant :</span> <span class="value"><?= htmlspecialchars($requestDetails['student_number']) ?></span></p>
      <p><span class="label">Formation :</span> <span class="value"><?= htmlspecialchars($requestDetails['level']) ?></span></p>
      <p><span class="label">Type de contrat :</span> <span class="value"><?= htmlspecialchars($requestDetails['contract_type']) ?></span></p>
    </div>

    <div class="section">
      <h3>Demande</h3>
      <p><span class="label">Intitulé du poste :</span> <span class="value"><?= htmlspecialchars($requestDetails['job_title']) ?></span></p>
      <p><span class="label">Mission :</span> <span class="value"><?= htmlspecialchars($requestDetails['mission']) ?></span></p>
      <p><span class="label">Volume Horaire :</span> <span class="value"><?= htmlspecialchars($requestDetails['weekly_hours'] ?? 'Non précisé') ?></span></p>
      <p><span class="label">Tuteur :</span> <span class="value"><?= htmlspecialchars($requestDetails['supervisor'] ?? 'Non renseigné') ?></span></p>
      <p><span class="label">Numéro e-sup :</span> <span class="value"><?= htmlspecialchars($requestDetails['student_number']) ?></span></p>
      <p><span class="label">Durée :</span> <span class="value">3 mois</span></p>
    </div>

    <div class="section">
      <h3>Pièces Jointes</h3>  
      <table>
        <thead>
          <tr>
            <th>Nom de la pièce</th>
            <th>Fournie</th>
            <th>Statut</th>
            <th>Actions</th>
            <th>Commentaire</th>
            <th>Fichier</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($documents as $doc): ?>
            <tr data-id="<?= htmlspecialchars($doc['id'] ?? '') ?>">
              <td><strong><?= htmlspecialchars($doc['label']) ?></strong></td>
              <td>
                <?= !empty($doc['file_path']) ? '<span class="icon-check">✔️</span>' : '<span class="icon-cross">⭕</span>' ?>
              </td>
              <td class="doc-status" data-status="<?= strtolower($doc['status']) ?>">
                <span class="status-text" style="font-weight: bold; color: <?= 
                  strtolower($doc['status']) === 'validée' ? 'green' : 
                  (strtolower($doc['status']) === 'refusée' ? 'red' : 'orange') 
                ?>;">
                  <?= ucfirst(htmlspecialchars($doc['status'])) ?>
                </span>
              </td>
              <td>
                <button class="btn-action validate-btn" data-id="<?= htmlspecialchars($doc['id'] ?? '') ?>">✅ Valider</button>
                <button class="btn-action refuse-btn" data-id="<?= htmlspecialchars($doc['id'] ?? '') ?>">❌ Refuser</button>
                <div class="message-container"></div>
              </td>
              <td>
                <input 
                  class="comment-input" 
                  type="text" 
                  value="<?= htmlspecialchars($doc['comment'] ?? '') ?>" 
                  placeholder="Ajouter un commentaire..." 
                  data-id="<?= htmlspecialchars($doc['id'] ?? '') ?>" 
                />
                <span class="save-indicator" style="color: green; font-size: 12px; display: none;">💾 Sauvegardé</span>
              </td>
              <td>
                <?php if (!empty($doc['file_path'])): ?>
                  <a class="btn-action" href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" rel="noopener noreferrer">📄 Voir</a>
                <?php else: ?>
                  <span style="color: #aaa;">Aucun</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="actions-section">
        <a
          class="btn-relancer"
          href="mailto:<?= htmlspecialchars($requestDetails['email'] ?? ''); ?>?subject=Relance%20documents%20stage&body=Bonjour%20<?= rawurlencode($requestDetails['first_name'] ?? ''); ?>%2C%0A%0AVos%20documents%20ne%20sont%20pas%20valides.%20Veuillez%20consulter%20votre%20espace%20en%20ligne%20et%20fournir%20les%20documents%20necessaires.%0A%0ACordialement."
        >
          📧 Relancer l'étudiant par mail
        </a>
      </div>

      <div class="global-message" id="globalMessage"></div>
    </div>

  <?php endif; ?>
</div>