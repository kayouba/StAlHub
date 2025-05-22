<?php /** views/responsable/requestList.php */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>StaHub - Tableau de bord</title>
  <link rel="stylesheet" href="/stalhub/public/css/responsable.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
  <!-- Inclusion de la sidebar commune -->
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <!-- Contenu principal avec marge pour la sidebar -->
  <div class="main-content-with-sidebar">
    <div class="container">
      <!-- Titre centré -->
      <div class="title-section">
        <h1>Responsable Pédagogique</h1>
      </div>

      <h2>Demandes en attente de validation</h2>

      <div class="filters">
        <label>Recherche:
          <input type="text" id="searchInput" placeholder="Rechercher un étudiant ou une entreprise...">
        </label>
        <label>Formation:
          <select id="filterFormation">
            <option value="">Toutes</option>
            <option value="Licence3 Miage">Licence 3 Miage</option>
            <option value="Master 1 Miage">Master 1 Miage</option>
            <option value="M2 Miage">Master 2 Miage</option>
          </select>
        </label>
        <label>Date:
          <input type="date" id="filterDate">
        </label>
        <label>Type:
          <select id="filterType">
            <option value="">Tous</option>
            <option value="Stage">Stage</option>
            <option value="Alternance">Alternance</option>
          </select>
        </label>
        <label>État:
          <select id="filterEtat">
            <option value="">Tous</option>
            <option value="attente">En_attente</option>
            <option value="validee">Validée</option>
            <option value="refusee">Refusée</option>
          </select>
        </label>

        <button class="btn secondary" id="resetFilters">Réinitialiser</button>
      </div>
     
      <table id="demandesTable">
        <thead>
          <tr>
            <th>Étudiant</th>
            <th>Formation</th>
            <th>Entreprise</th>
            <th>Date</th>
            <th>Type</th>
            <th>État</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($demandes as $demande): ?>
          <tr>
            <td><?= htmlspecialchars($demande['etudiant']) ?></td>
            <td><?= htmlspecialchars($demande['formation'] ?? 'Non renseigné') ?></td>
            <td><?= htmlspecialchars($demande['entreprise']) ?></td>
            <td><?= htmlspecialchars($demande['date']) ?></td>
            <td><?= htmlspecialchars($demande['type']) ?></td>
            <td><span class="etat <?= $demande['etat'] ?>"><?= ucfirst($demande['etat']) ?></span></td>
            <td><a class="btn" href="/stalhub/responsable/detailRequest?id=<?= $demande['id'] ?>">Voir</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById("searchInput");
    const filterFormation = document.getElementById("filterFormation");
    const filterDate = document.getElementById("filterDate");
    const filterType = document.getElementById("filterType");
    const filterEtat = document.getElementById("filterEtat");
    const resetBtn = document.getElementById("resetFilters");

    const table = document.getElementById("demandesTable").getElementsByTagName("tbody")[0];

    function filterTable() {
      const searchTerm = searchInput.value.toLowerCase();
      const formation = filterFormation.value;
      const date = filterDate.value;
      const type = filterType.value;
      const etat = filterEtat.value;

      for (let row of table.rows) {
        const cells = row.getElementsByTagName("td");

        const matchSearch = Array.from(cells).some(cell =>
          cell.textContent.toLowerCase().includes(searchTerm)
        );

        const matchFormation = !formation || cells[1].textContent === formation;
        const matchDate = !date || cells[3].textContent === date;
        const matchType = !type || cells[4].textContent === type;
        const matchEtat = !etat || cells[5].querySelector('span').classList.contains(etat);

        if (matchSearch && matchFormation && matchDate && matchType && matchEtat) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      }
    }

    searchInput.addEventListener("input", filterTable);
    filterFormation.addEventListener("change", filterTable);
    filterDate.addEventListener("change", filterTable);
    filterType.addEventListener("change", filterTable);
    filterEtat.addEventListener("change", filterTable);
    resetBtn.addEventListener("click", () => {
      searchInput.value = "";
      filterFormation.value = "";
      filterDate.value = "";
      filterType.value = "";
      filterEtat.value = "";
      filterTable();
    });
  </script>
</body>
</html>