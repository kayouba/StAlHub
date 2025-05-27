<?php 
function statusToCssClass($status) {
  $status = strtolower($status);
  return match ($status) {
    'valide', 'validé', 'complete', 'valid_secretaire' => 'complete',
    'soumise', 'transmise', 'en_attente_secretaire' => 'transmise',
    'refusee', 'refusé', 'incomplete', 'refusee_secretaire' => 'incomplete',
    'attente' => 'transmise',
    default => 'transmise'
  };
}

function formatStatus($status) {
  return match ($status) {
    'REFUSEE_SECRETAIRE' => 'incomplet',
    'VALID_SECRETAIRE' => 'validé',
    'EN_ATTENTE_SECRETAIRE' => 'en attente',
    'SOUMISE' => 'soumise',
    default => strtolower($status)
  };
}

function getDisplayStatus($demande) {
  // Si on a un état calculé depuis les documents, on l'utilise
  if (isset($demande['etat'])) {
    return match ($demande['etat']) {
      'validee' => 'validé',
      'refusee' => 'incomplet',
      'attente' => 'en attente',
      default => 'en attente'
    };
  }
  
  // Sinon on utilise le statut de la demande
  return formatStatus($demande['status'] ?? '');
}

function getDisplayStatusClass($demande) {
  // Si on a un état calculé depuis les documents, on l'utilise
  if (isset($demande['etat'])) {
    return match ($demande['etat']) {
      'validee' => 'complete',
      'refusee' => 'incomplete',
      'attente' => 'transmise',
      default => 'transmise'
    };
  }
  
  // Sinon on utilise le statut de la demande
  return statusToCssClass($demande['status'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Secrétaire</title>
  <link rel="stylesheet" href="/stalhub/public/css/secretary-dashboard.css">
  <script src="/stalhub/public/js/secretary-dashboard.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main>
  <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
  <p class="bienvenue">Vous êtes connecté en tant que secrétariat pédagogique.</p>
  
  <h1>Demandes de Stage</h1>
  
  <div class="filters">
    <select id="filter-formation">
      <option value="">Toutes les formations</option>
      <option value="licence 3">Licence 3</option>
      <option value="master 1">Master 1</option>
      <option value="master 2">Master 2</option>
    </select>
    
    <select id="filter-etat">
      <option value="">Tous les états</option>
      <option value="validé">Validée</option>
      <option value="incomplet">Incomplet</option>
      <option value="en attente">En attente</option>
      <option value="soumise">Soumise</option>
    </select>
    
    <input type="text" id="search" placeholder="Rechercher" />
  </div>
  
  <table>
    <thead>
      <tr>
        <th>Nom Prénom</th>
        <th>Formation</th>
        <th>Parcours</th>
        <th>Entreprise</th>
        <th>Date</th>
        <th>Type</th>
        <th>État</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="table-body">
      <?php
        $demandes = array_filter($demandes, function ($demande) {
          return strtolower($demande['type'] ?? '') === 'stage';
        });
      ?>

      <?php foreach ($demandes as $demande): ?>
        <?php
          $statusLabel = getDisplayStatus($demande);
          $statusClass = getDisplayStatusClass($demande);
        ?>
        <tr>
          <td><?= htmlspecialchars($demande['etudiant']) ?></td>
          <td><?= htmlspecialchars($demande['formation'] ?? 'Non renseignée') ?></td>
          <td><?= htmlspecialchars($demande['parcours']) ?></td>
          <td><?= htmlspecialchars($demande['entreprise']) ?></td>
          <td><?= htmlspecialchars($demande['date'] ?? '') ?></td>
          <td><?= htmlspecialchars($demande['type'] ?? '') ?></td>
          <td class="<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></td>
          <td><a href="/stalhub/secretary/details?id=<?= $demande['id'] ?>">voir</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

</body>
</html>