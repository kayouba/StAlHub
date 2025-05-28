<?php
function statusToCssClass($status) {
  $status = strtolower($status);
  return match ($status) {
    'validee_finale', 'complete' => 'complete',
    'signee', 'transmise' => 'transmise',
    'attente' => 'pending',
    'incomplete' => 'incomplete',
    'refusee', 'rejected' => 'rejected',
    default => 'transmise'
  };
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Direction</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f9f9f9;
    }

    main {
      margin-left: 240px;
      padding: 20px;
    }

    h1 {
      color: #004b80;
      margin-bottom: 20px;
    }

    .bienvenue {
      margin-top: -15px;
      margin-bottom: 30px;
      font-size: 1.1em;
      color: #333;
    }

    .pending {
      color: #d2a500;
      font-weight: bold;
    }

    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
    }

    select, input[type="text"] {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #f0f0f0;
    }

    .complete {
      color: green;
      font-weight: bold;
    }

    .transmise {
      color: #2196F3;
      font-weight: bold;
    }

    .incomplete {
      color: red;
      font-weight: bold;
    }

    .rejected {
      color: #dc3545;
      font-weight: bold;
    }

    a {
      color: #004b80;
      text-decoration: none;
    }

    .header-info {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
    }

    .header-info h1 {
      color: white;
      margin: 0;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: center;
    }

    .stat-number {
      font-size: 2em;
      font-weight: bold;
      color: #004b80;
    }

    .stat-label {
      color: #666;
      margin-top: 5px;
    }
  </style>
</head>

<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main>
  <div class="header-info">
    <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
    <p>Gestion des conventions de stage et validation finale des dossiers.</p>
</div>


  <?php
    // Calcul des statistiques
    $stats = [
      'attente' => 0,
      'signee' => 0,
      'validee_finale' => 0,
      'refusee' => 0,
      'total' => count($demandes)
    ];

    foreach ($demandes as $demande) {
      if (isset($stats[$demande['etat']])) {
        $stats[$demande['etat']]++;
      }
    }
  ?>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-number"><?= $stats['attente'] ?></div>
      <div class="stat-label">En attente de signature</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['signee'] ?></div>
      <div class="stat-label">Conventions signées</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['validee_finale'] ?></div>
      <div class="stat-label">Dossiers validés</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['refusee'] ?></div>
      <div class="stat-label">Dossiers refusés</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['total'] ?></div>
      <div class="stat-label">Total des demandes</div>
    </div>
  </div>

  <h2>Demandes de Stage - Conventions</h2>

   <div class="filters">
    <select id="filter-formation">
      <option value="">Toutes les formations</option>
      <option value="licence 3">Licence 3</option>
      <option value="master 1">Master 1</option>
      <option value="master 2">Master 2</option>
    </select>

    <select id="filter-etat">
      <option value="">Tous les états</option>
      <option value="validé définitivement">Validé définitivement</option>
      <option value="convention signée">Convention signée</option>
      <option value="en attente de signature">En attente de signature</option>
      <option value="dossier refusé">Dossier refusé</option>
    </select>

    <input type="text" id="search" placeholder="Rechercher étudiant, entreprise..." />
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
        <th>État Convention</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="table-body">
      <?php foreach ($demandes as $demande): ?>
        <?php
          $statusClass = statusToCssClass($demande['etat']);
        ?>
        <tr>
          <td><?= htmlspecialchars($demande['etudiant']) ?></td>
          <td><?= htmlspecialchars($demande['formation'] ?? 'Non renseignée') ?></td>
          <td><?= htmlspecialchars($demande['parcours']) ?></td>
          <td><?= htmlspecialchars($demande['entreprise']) ?></td>
          <td><?= htmlspecialchars($demande['date'] ?? '') ?></td>
          <td><?= htmlspecialchars($demande['type'] ?? '') ?></td>
          <td class="<?= $statusClass ?>">
          <?php
            echo match ($demande['etat']) {
              'validee_finale' => 'Validé définitivement',
              'signee' => 'Convention signée',
              'attente' => 'En attente de signature',
              'refusee' => 'Dossier refusé',
              default => ucfirst($demande['etat']),
            };
          ?>
        </td>
          <td><a href="/stalhub/direction/details?id=<?= $demande['id'] ?>">gérer</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<script>
  
  const filters = {
    formation: document.getElementById("filter-formation"),
    etat: document.getElementById("filter-etat"),
    search: document.getElementById("search")
  };

  const rows = document.querySelectorAll("#table-body tr");

  function filterTable() {
    const formationVal = filters.formation.value.toLowerCase();
    const etatVal = filters.etat.value.toLowerCase();
    const searchVal = filters.search.value.toLowerCase();

    rows.forEach(row => {
      const formation = row.children[1].textContent.toLowerCase();
      const etat = row.children[6].textContent.toLowerCase(); // colonne État
      const fullText = row.textContent.toLowerCase();

      const matchFormation = !formationVal || formation.includes(formationVal);
      const matchEtat = !etatVal || etat.includes(etatVal);
      const matchSearch = !searchVal || fullText.includes(searchVal);

      row.style.display = (matchFormation && matchEtat && matchSearch) ? "" : "none";
    });
  }

  Object.values(filters).forEach(el => el.addEventListener("input", filterTable));
</script>

</body>
</html>