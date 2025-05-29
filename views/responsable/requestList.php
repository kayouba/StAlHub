<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>StaHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/responsable.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<body>
    <div class="main-content-with-sidebar">
        <div class="container">
            <div class="title-section">
                <h1><i class="fas fa-graduation-cap"></i> Responsable Pédagogique</h1>
            </div>
            <div class="filters">
                <label class="filter-field">
                    <span><i class="fas fa-search"></i> Recherche</span>
                    <input type="text" id="searchInput" placeholder="Rechercher un étudiant ou une entreprise...">
                </label>

                <label class="filter-field">
                    <span><i class="fas fa-graduation-cap"></i> Formation</span>
                    <select id="filterFormation">
                        <option value="">Toutes</option>
                        <option value="Licence3 Miage">Licence 3 Miage</option>
                        <option value="Master 1 Miage">Master 1 Miage</option>
                        <option value="M2 Miage">Master 2 Miage</option>
                    </select>
                </label>

                <label class="filter-field">
                    <span><i class="fas fa-calendar-alt"></i> Date</span>
                    <input type="date" id="filterDate">
                </label>

                <label class="filter-field">
                    <span><i class="fas fa-briefcase"></i> Type</span>
                    <select id="filterType">
                        <option value="">Tous</option>
                        <option value="Stage">Stage</option>
                        <option value="Alternance">Alternance</option>
                    </select>
                </label>

                <label class="filter-field">
                    <span><i class="fas fa-info-circle"></i> État</span>
                    <select id="filterEtat">
                        <option value="">Tous</option>
                        <option value="attente">En attente</option>
                        <option value="validee">Validée</option>
                        <option value="refusee">Refusée</option>
                    </select>
                </label>

                <button class="btn secondary" id="resetFilters">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </div>


            <div class="table-container">
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
                            <tr data-etudiant="<?= htmlspecialchars($demande['etudiant']) ?>"
                                data-formation="<?= htmlspecialchars($demande['formation'] ?? 'Non renseigné') ?>"
                                data-entreprise="<?= htmlspecialchars($demande['entreprise']) ?>"
                                data-date="<?= htmlspecialchars($demande['date']) ?>"
                                data-type="<?= htmlspecialchars($demande['type']) ?>"
                                data-etat="<?= htmlspecialchars($demande['etat']) ?>">

                                <td><?= htmlspecialchars($demande['etudiant']) ?></td>
                                <td><?= htmlspecialchars($demande['formation'] ?? 'Non renseigné') ?></td>
                                <td><?= htmlspecialchars($demande['entreprise']) ?></td>
                                <td><?= htmlspecialchars($demande['date']) ?></td>
                                <td><?= htmlspecialchars($demande['type']) ?></td>
                                <td>
                                    <span class="etat <?= $demande['etat'] ?>">
                                        <?= ucfirst($demande['etat']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="btn" href="/stalhub/responsable/detailRequest?id=<?= $demande['id'] ?>">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="/stalhub/public/js/responsable-requestList.js"></script>
</body>

</html>