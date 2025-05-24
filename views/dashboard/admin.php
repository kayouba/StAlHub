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
    document.getElementById('is_admin').value = user.is_admin;
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

function filterUsers() {
    const selectedRole = document.getElementById('roleFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#userTable tr');

    rows.forEach(row => {
        const userRole = row.getAttribute('data-role').toLowerCase();
        row.style.display = (selectedRole === 'all' || userRole === selectedRole) ? '' : 'none';
    });
}

function openRequestModal(req) {
    const html = `
        <p><strong>Étudiant :</strong> ${req.student_name}</p>
        <p><strong>Entreprise :</strong> ${req.company_name}</p>
        <p><strong>Type de contrat :</strong> ${req.contract_type ? req.contract_type : '-'}</p>

        <p><strong>Email référent :</strong> ${req.referent_email}</p>
        <p><strong>Mission :</strong> ${req.mission}</p>
        <p><strong>Heures par semaine :</strong> ${req.weekly_hours ?? '-'}</p>
        <p><strong>Salaire :</strong> ${req.salary_value} / ${req.salary_duration}</p>
        <p><strong>Début :</strong> ${req.start_date}</p>
        <p><strong>Fin :</strong> ${req.end_date}</p>
        <p><strong>Statut :</strong> ${req.status}</p>
    `;
    document.getElementById('requestDetails').innerHTML = html;
    document.getElementById('requestModal').style.display = 'flex';
}

function closeRequestModal() {
    document.getElementById('requestModal').style.display = 'none';
}

function openCompanyModal(company) {
    const info = `
        <p><strong>Nom :</strong> ${company.name}</p>
        <p><strong>SIRET :</strong> ${company.siret}</p>
        <p><strong>Email :</strong> ${company.email || '-'}</p>
        <p><strong>Adresse :</strong> ${company.address || '-'}, ${company.postal_code || '-'} ${company.city || '-'}</p>
        <p><strong>Détails :</strong> ${company.details || '-'}</p>
    `;
    document.getElementById('companyInfo').innerHTML = info;

    fetch('/stalhub/admin/companies/requests?company_id=' + company.id)
    .then(res => res.json())
    .then(data => {
        if (data.length > 0) {
            let html = '<ul>';
            data.forEach(req => {
                html += `
                    <li>
                        <strong>${req.student_name}</strong> | 
                        Contrat : ${req.contract_type || '-'}<br>
                        Référent : ${req.referent_email || '-'}<br>
                        Mission : ${req.mission || '-'}<br>
                        Heures / semaine : ${req.weekly_hours || '-'}<br>
                        Salaire : ${req.salary_value || '-'} / ${req.salary_duration || '-'}<br>
                        Période : ${req.start_date} → ${req.end_date}<br>
                        Statut : <strong>${req.status}</strong>
                    </li><hr>
                `;
            });
            html += '</ul>';
            document.getElementById('companyRequests').innerHTML = html;
        } else {
            document.getElementById('companyRequests').innerHTML = '<em>Aucune demande associée</em>';
        }
    });


    document.getElementById('companyModal').style.display = 'flex';
}

function closeCompanyModal() {
    document.getElementById('companyModal').style.display = 'none';
}

function searchUsers() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#userTable tr");

    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();

        if (name.includes(input) || email.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

function filterRequests() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const tutor = document.getElementById('tutorFilter').value;
    const type = document.getElementById('typeFilter').value.toLowerCase();
    const search = document.getElementById('searchInput').value.toLowerCase();

    const rows = document.querySelectorAll('#requestsTable tr');
    let anyVisible = false;

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status')?.toLowerCase() || '';
        const rowTutor = row.getAttribute('data-tutor') || '';
        const rowType = row.getAttribute('data-type')?.toLowerCase() || '';
        const student = row.querySelector('[data-label="Étudiant"]')?.textContent.toLowerCase() || '';
        const company = row.querySelector('[data-label="Entreprise"]')?.textContent.toLowerCase() || '';

        const matchStatus = status === 'all' || rowStatus === status;
        const matchTutor = tutor === 'all' || rowTutor === tutor;
        const matchType = type === 'all' || rowType === type;
        const matchSearch = student.includes(search) || company.includes(search);

        if (matchStatus && matchTutor && matchType && matchSearch) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyRow = document.querySelector('#requestsTable .empty-message');
    if (emptyRow) {
        emptyRow.style.display = anyVisible ? 'none' : '';
    }
}

</script>

</body>
</html>
