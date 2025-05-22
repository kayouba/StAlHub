<?php include __DIR__ . '/../components/sidebar.php'; ?>

<style>
  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f2f4f8;
    display: flex;
  }

  .sidebar {
    width: 250px;
    min-height: 100vh;
    background-color: #2c3e50;
    color: white;
    padding: 20px;
    position: fixed;
    top: 0;
    left: 0;
  }

  .main-content {
    margin-left: 250px;
    padding: 30px;
    width: calc(100% - 250px);
  }

  .section {
    background-color: #fff;
    padding: 25px;
    margin-bottom: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }

  h2 {
    margin-top: 0;
    color: #2c3e50;
    margin-bottom: 20px;
  }

  h3 {
    color: #34495e;
    margin-bottom: 15px;
  }

  .section p {
    margin: 8px 0;
  }

  .label {
    font-weight: bold;
    display: inline-block;
    width: 220px;
    color: #333;
  }

  .value {
    color: #555;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }

  th, td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
  }

  th {
    background-color: #f5f5f5;
    color: #333;
  }

  .status-valid {
    color: green;
    font-weight: bold;
  }

  .status-refused {
    color: red;
    font-weight: bold;
  }

  .status-pending {
    color: orange;
    font-weight: bold;
  }

  .icon-check {
    color: green;
    font-size: 18px;
  }

  .icon-cross {
    color: gray;
    font-size: 18px;
  }

  .btn-relancer {
    margin-top: 1rem;
    display: inline-block;
    background-color: #0077cc;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    margin-right: 10px;
  }

  .btn-relancer:hover {
    background-color: #005fa3;
  }

  .btn-validate-all {
    margin-top: 1rem;
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    border: none;
    cursor: pointer;
  }

  .btn-validate-all:hover {
    background-color: #218838;
  }

  .btn-validate-all:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
  }

  .btn-action {
    color: #0077cc;
    background-color: #eef6fc;
    border: 1px solid #0077cc;
    border-radius: 4px;
    padding: 5px 10px;
    margin: 2px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .btn-action:hover {
    background-color: #d0e8ff;
    text-decoration: none;
  }
  
  .comment-input {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  .loading {
    opacity: 0.6;
    pointer-events: none;
  }

  .success-message {
    color: green;
    font-size: 12px;
    margin-top: 5px;
  }

  .error-message {
    color: red;
    font-size: 12px;
    margin-top: 5px;
  }

  .actions-section {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
  }

  .global-message {
    margin-top: 10px;
    padding: 10px;
    border-radius: 4px;
    display: none;
  }

  .global-message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .global-message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .debug-info {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
  }
</style>

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
                <input class="comment-input" type="text" value="<?= htmlspecialchars($doc['comment'] ?? '') ?>" placeholder="Ajouter un commentaire..." />
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
        
        <button class="btn-validate-all" id="validateAllBtn">
          ✅ Valider tous les documents
        </button>
      </div>

      <div class="global-message" id="globalMessage"></div>
    </div>

  <?php endif; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const validateButtons = document.querySelectorAll('.validate-btn');
    const refuseButtons = document.querySelectorAll('.refuse-btn');

    validateButtons.forEach(button => {
      button.addEventListener('click', function () {
        const row = this.closest('tr');
        const statusCell = row.querySelector('.doc-status');
        const statusText = statusCell.querySelector('.status-text');

        // Mise à jour visuelle
        statusCell.dataset.status = 'validée';
        statusText.textContent = 'Validée';
        statusText.style.color = 'green';
      });
    });

    refuseButtons.forEach(button => {
      button.addEventListener('click', function () {
        const row = this.closest('tr');
        const statusCell = row.querySelector('.doc-status');
        const statusText = statusCell.querySelector('.status-text');

        // Mise à jour visuelle
        statusCell.dataset.status = 'refusée';
        statusText.textContent = 'Refusée';
        statusText.style.color = 'red';
      });
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    const validateAllBtn = document.getElementById('validateAllBtn');

    validateAllBtn.addEventListener('click', function () {
      const allRows = document.querySelectorAll('tbody tr');

      allRows.forEach(row => {
        const statusCell = row.querySelector('.doc-status');
        const statusText = statusCell.querySelector('.status-text');

        statusCell.dataset.status = 'validée';
        statusText.textContent = 'Validée';
        statusText.style.color = 'green';
      });
    });
  });
  
</script>
