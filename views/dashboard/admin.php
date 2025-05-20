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

    <div class="tabs">
        <button class="tab active" data-tab="users">Utilisateurs</button>
        <button class="tab" data-tab="requests">Demandes</button>
        <button class="tab" data-tab="companies">Entreprises</button>
    </div>

    <section id="tab-content" class="tab-container">
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.tab');
    const content = document.getElementById('tab-content');

    function loadTab(tabName) {
        fetch(`/stalhub/admin/tab/${tabName}`)
            .then(response => response.ok ? response.text() : Promise.reject("Erreur chargement onglet"))
            .then(html => {
                content.innerHTML = html;
                bindRoleForm();
            })
            .catch(error => {
                content.innerHTML = `<p class="error">Erreur : ${error}</p>`;
            });
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            button.classList.add('active');
            loadTab(button.dataset.tab);
        });
    });

    loadTab('users');
});

function openModal(user) {
    const modal = document.getElementById('userModal');
    modal.style.display = 'block';

    const activeText = user.is_active == 1 ? '✅ Actif' : '❌ Inactif';
    const rgpdText = user.consentement_rgpd == 1 ? '✅ Oui' : '❌ Non';

    document.getElementById('userInfo').innerHTML = `
        <p><strong>Nom :</strong> ${user.last_name} ${user.first_name}</p>
        <p><strong>Email :</strong> ${user.email}</p>
        <p><strong>Email secondaire :</strong> ${user.alternate_email || '-'}</p>
        <p><strong>Téléphone :</strong> ${user.phone_number || '-'}</p>
        <p><strong>Numéro étudiant :</strong> ${user.student_number || '-'}</p>
        <p><strong>Programme :</strong> ${user.program || '-'}</p>
        <p><strong>Parcours :</strong> ${user.track || '-'}</p>
        <p><strong>Niveau :</strong> ${user.level || '-'}</p>
        <p><strong>Code affectation :</strong> ${user.assignment_code || '-'}</p>
        <p><strong>Statut :</strong> ${activeText}</p>
        <p><strong>Consentement RGPD :</strong> ${rgpdText}</p>
        <p><strong>Créé le :</strong> ${user.created_at}</p>
        <p><strong>Dernière connexion :</strong> ${user.last_login_at || '-'}</p>
    `;

    document.getElementById('user_id').value = user.id;
    document.getElementById('role').value = user.role;
}


function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

function bindRoleForm() {
    const form = document.getElementById('roleForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('/stalhub/admin/users/updateRole', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Rôle mis à jour avec succès.');
                closeModal();
                location.reload();
            } else {
                alert('Erreur : ' + (data.message || 'Impossible de modifier le rôle.'));
            }
        });
    });
}

</script>

</body>
</html>
