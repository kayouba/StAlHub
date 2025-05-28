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
      <p><span class="label">Numéro e-sup :</p>
      <?php
      $durationText = 'Non renseignée';
      if (!empty($requestDetails['start_date']) && !empty($requestDetails['end_date'])) {
        try {
          $start = new DateTime($requestDetails['start_date']);
          $end = new DateTime($requestDetails['end_date']);
          $interval = $start->diff($end);

          // Formattage : X mois Y jours
          $months = $interval->m + ($interval->y * 12);
          $days = $interval->d;

          $durationParts = [];
          if ($months > 0) $durationParts[] = "$months mois";
          if ($days > 0) $durationParts[] = "$days jours";

          $durationText = implode(' ', $durationParts);
        } catch (Exception $e) {
          $durationText = 'Erreur de date';
        }
      }
    ?>

<p><span class="label">Durée :</span> <span class="value"><?= htmlspecialchars($durationText) ?></span></p>

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
              <?php
                $status = strtolower($doc['status']);
                $statusColor = match ($status) {
                  'validé', 'validated' => 'green',
                  'refusé', 'rejected'  => 'red',
                  'soumis', 'submitted' => 'orange',
                  default => '#888'
                };

                $statusMap = [
                  'validated' => 'validé',
                  'rejected'  => 'refusé',
                  'submitted' => 'soumis',
                ];

                $displayStatus = $statusMap[$status] ?? $status;
              ?>
              <span class="status-text" style="font-weight: bold; color: <?= $statusColor ?>;">
                <?= ucfirst($displayStatus) ?>
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
                <a
                  class="btn-action"
                  href="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                >📄 Voir</a>
              <?php else: ?>
                <span style="color: #aaa;">Aucun</span>
              <?php endif; ?>
            </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php
      $refusedDocs = array_filter($documents, function($doc) {
        $status = strtolower($doc['status']);
        return in_array($status, ['rejected', 'refusé', 'refusee', 'refusé']);
      });

      $emailBody = "Bonjour " . ($requestDetails['first_name'] ?? '') . ",\n\n";
      $emailBody .= "Certains de vos documents ont été refusés. Veuillez consulter les motifs ci-dessous et fournir une version corrigée :\n\n";

      foreach ($refusedDocs as $doc) {
        $label = htmlspecialchars($doc['label']);
        $comment = htmlspecialchars($doc['comment'] ?? 'Aucun motif précisé');
        $emailBody .= "- $label : $comment\n";
      }

      $emailBody .= "\nConsultez votre espace en ligne pour soumettre à nouveau les documents.\n\nCordialement.";

      $mailtoLink = "mailto:" . rawurlencode($requestDetails['email'] ?? '') .
                    "?subject=" . rawurlencode("StAlHub - Relance documents stage") .
                    "&body=" . rawurlencode($emailBody);
    ?>

    <div class="actions-section">
      <a
        class="btn-relancer"
        href="<?= $mailtoLink ?>"
      >
        📧 Relancer l'étudiant par mail
      </a>
    </div>


  <?php endif; ?>
</div>