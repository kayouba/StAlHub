<?php
function statusToCssClass($status) {
  $status = strtolower($status);
  return match ($status) {
    'valide', 'validé', 'complete' => 'complete',
    'soumise', 'transmise' => 'transmise',
    'refusee', 'incomplete' => 'incomplete',
    'attente' => 'transmise', // exemple, adapte selon ton besoin
    default => 'transmise'
  };
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Secrétaire</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f9f9f9;
    }

    main {
      margin-left: 240px; /* Pour la sidebar */
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
      color: orange;
      font-weight: bold;
    }

    .incomplete {
      color: red;
      font-weight: bold;
    }

    a {
      color: #004b80;
      text-decoration: none;
    }
  </style>
</head>

<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main>
  <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
  <p class="bienvenue">Vous êtes connecté en tant que secrétaire.</p>

  <h1>Demandes de Stage</h1>

  <div class="filters">
    <select id="filter-formation">
      <option value="">Toutes les formations</option>
      <option value="licence 3 miage">Licence 3 Miage</option>
      <option value="master 1 miage">Master 1 Miage</option>
      <option value="m2 miage">Master 2 Miage</option>
    </select>

    <select id="filter-etat">
      <option value="">Tous les états</option>
      <option value="complete">Validée</option>
      <option value="transmise">Soumise / Attente</option>
      <option value="incomplete">Refusée</option>
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
        <th>État</th>
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
              'validee' => 'Validé',
              'refusee' => 'Refusé',
              'attente' => 'En attente',
              default => ucfirst($demande['etat']),
            };
          ?>
        </td>

          <td><a href="/stalhub/secretary/details?id=<?= $demande['id'] ?>">voir</a></td>
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
      const etat = row.children[5].className.toLowerCase();
      const fullText = row.textContent.toLowerCase();

      const matchFormation = !formationVal || formation.includes(formationVal);
      const matchEtat = !etatVal || etat === etatVal;
      const matchSearch = !searchVal || fullText.includes(searchVal);

      row.style.display = (matchFormation && matchEtat && matchSearch) ? "" : "none";
    });
  }

  Object.values(filters).forEach(el => el.addEventListener("input", filterTable));
</script>



</body>
</html>
