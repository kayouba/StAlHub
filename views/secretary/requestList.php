
<?php /** views/responsable/page1.php */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>StaHub - Tableau de bord</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    /* Styles pour sidebar + topbar + contenu */

    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    /* Sidebar fixe */
    .sidebar {
      width: 250px;
      background-color: #f0f0f0;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      padding: 20px;
      overflow-y: auto;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      z-index: 100;
    }

    /* Décaler topbar à droite pour laisser place à sidebar */
    .topbar {
      position: fixed;
      top: 0;
      left: 250px;
      right: 0;
      height: 60px;
      background: #fff;
      border-bottom: 1px solid #ccc;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 20px;
      box-sizing: border-box;
      z-index: 200;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-initials {
      background-color: #007bff;
      color: white;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      font-size: 1rem;
    }

    .role {
      font-weight: 500;
      color: #333;
    }

    .logout-link {
      color: #007bff;
      text-decoration: none;
      font-weight: 600;
      border: 1px solid #007bff;
      padding: 6px 12px;
      border-radius: 4px;
      transition: background-color 0.3s, color 0.3s;
    }

    .logout-link:hover {
      background-color: #007bff;
      color: white;
    }

    /* Contenu principal décalé pour la sidebar et la topbar */
    .layout {
      margin-left: 250px;
      margin-top: 60px;
      padding: 20px;
      box-sizing: border-box;
    }

    /* Styles des états */
    .etat.attente {
      color: orange;
      font-weight: bold;
    }
    .etat.validee {
      color: green;
      font-weight: bold;
    }
    .etat.refusee {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
      
  <!-- Inclusion de ta sidebar commune -->
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <header class="topbar">
    <div class="topbar-right">
      <span class="role">Responsable de professionnalisation</span>
    </div>
  </header>

  <div class="layout">
    <main class="main-content">
      <div class="container">
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

            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
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
