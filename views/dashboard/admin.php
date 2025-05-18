<?php
$pendingCount = $pendingCount ?? 0;
$validatedCount = $validatedCount ?? 0;
$rejectedCount = $rejectedCount ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Admin Dashboard</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
</head>

<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="admin-dashboard">
    <h1>Tableau de bord administrateur</h1>

    <!-- Statistiques -->
    <div class="stats">
        <div class="card blue">
            <h2><?= $pendingCount ?></h2>
            <p>Demandes à valider</p>
        </div>
        <div class="card green">
            <h2><?= $validatedCount ?></h2>
            <p>Demandes validées</p>
        </div>
        <div class="card red">
            <h2><?= $rejectedCount ?></h2>
            <p>Demandes refusées</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" data-tab="users">Utilisateurs</button>
        <button class="tab" data-tab="requests">Demandes</button>
        <button class="tab" data-tab="companies">Entreprises</button>
    </div>

    <!-- Contenu chargé dynamiquement -->
    <section id="tab-content" class="tab-container">
        <!-- AJAX: le contenu sera inséré ici -->
    </section>
</main>

<!-- Script JS pour chargement dynamique -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.tab');
    const content = document.getElementById('tab-content');

    function loadTab(tabName) {
        // fetch(`/stalhub/admin/tabs/${tabName}.php`)
        fetch(`/stalhub/admin/tab/${tabName}`)

            .then(response => response.ok ? response.text() : Promise.reject("Erreur chargement onglet"))
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = `<p class="error">Erreur : ${error}</p>`;
            });
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            button.classList.add('active');
            const tabName = button.dataset.tab;
            loadTab(tabName);
        });
    });

    loadTab('users'); // onglet par défaut
});
</script>
</body>
</html>
