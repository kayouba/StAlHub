<?php include __DIR__ . '/../components/sidebar.php'; ?>

<link rel="stylesheet" href="/stalhub/public/css/secretary-detailsfile.css">
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
            <th>Fournie ?</th>
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
                <input class="comment-input" type="text" value="<?= htmlspecialchars($doc['comment'] ?? '') ?>" placeholder="Ajouter un commentaire..." data-id="<?= htmlspecialchars($doc['id'] ?? '') ?>" />
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const validateButtons = document.querySelectorAll('.validate-btn');
    const refuseButtons = document.querySelectorAll('.refuse-btn');
    const validateAllBtn = document.getElementById('validateAllBtn');
    const commentInputs = document.querySelectorAll('.comment-input');

    // 💾 Sauvegarde automatique des commentaires
    commentInputs.forEach(input => {
      let saveTimeout;
      
      input.addEventListener('input', function () {
        const documentId = this.dataset.id;
        const comment = this.value;
        const saveIndicator = this.nextElementSibling;
        
        // Débouncing : attendre 1 seconde après la dernière frappe
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
          saveComment(documentId, comment, saveIndicator);
        }, 1000);
      });

      // Sauvegarde immédiate quand l'utilisateur quitte le champ
      input.addEventListener('blur', function () {
        const documentId = this.dataset.id;
        const comment = this.value;
        const saveIndicator = this.nextElementSibling;
        
        clearTimeout(saveTimeout);
        saveComment(documentId, comment, saveIndicator);
      });
    });

    // Fonction pour sauvegarder le commentaire
    function saveComment(documentId, comment, saveIndicator) {
      fetch('/stalhub/secretary/save-comment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          document_id: documentId,
          comment: comment
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Afficher l'indicateur de sauvegarde
          saveIndicator.style.display = 'inline';
          setTimeout(() => {
            saveIndicator.style.display = 'none';
          }, 2000);
        } else {
          console.error('Erreur lors de la sauvegarde du commentaire');
        }
      })
      .catch(error => {
        console.error('Erreur réseau:', error);
      });
    }

    // ✅ Validation d'un seul document
    validateButtons.forEach(button => {
      button.addEventListener('click', function () {
        const row = this.closest('tr');
        const documentId = this.dataset.id;
        const commentInput = row.querySelector('.comment-input');
        const comment = commentInput ? commentInput.value : '';

        fetch('/stalhub/secretary/update-document-status', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            document_id: documentId,
            status: 'validated',
            comment: comment
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const statusCell = row.querySelector('.doc-status');
            const statusText = statusCell.querySelector('.status-text');
            statusCell.dataset.status = 'validée';
            statusText.textContent = 'Validée';
            statusText.style.color = 'green';
          } else {
            alert("Erreur lors de la validation du document.");
          }
        });
      });
    });

    // ❌ Refus d'un seul document
    refuseButtons.forEach(button => {
      button.addEventListener('click', function () {
        const row = this.closest('tr');
        const documentId = this.dataset.id;
        const commentInput = row.querySelector('.comment-input');
        const comment = commentInput ? commentInput.value : '';

        fetch('/stalhub/secretary/update-document-status', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            document_id: documentId,
            status: 'rejected',
            comment: comment
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const statusCell = row.querySelector('.doc-status');
            const statusText = statusCell.querySelector('.status-text');
            statusCell.dataset.status = 'refusée';
            statusText.textContent = 'Refusée';
            statusText.style.color = 'red';
          } else {
            alert("Erreur lors du refus du document.");
          }
        });
      });
    });

    // ✅ Valider tous les documents (mise à jour UI + base de données)
    if (validateAllBtn) {
      validateAllBtn.addEventListener('click', function () {
        const allRows = document.querySelectorAll('tbody tr');
        const allDocumentIds = [];

        allRows.forEach(row => {
          const statusCell = row.querySelector('.doc-status');
          const statusText = statusCell.querySelector('.status-text');
          statusCell.dataset.status = 'validée';
          statusText.textContent = 'Validée';
          statusText.style.color = 'green';

          const documentId = row.dataset.id;
          allDocumentIds.push(documentId);
        });

        // ⚠️ Mise à jour de la base de données pour tous les documents
        fetch('/stalhub/secretary/validate-all-documents', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            document_ids: allDocumentIds
          })
        })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            alert("Une erreur est survenue lors de la validation en masse.");
          }
        });
      });
    }
  });
</script>