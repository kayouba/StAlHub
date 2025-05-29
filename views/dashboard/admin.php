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
    <style>
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .document-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 15px;
        }

        .document-card {
            width: 200px;
            background: #f9f9f9;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .doc-preview iframe {
            width: 100%;
            height: 120px;
            border: none;
        }

        .doc-meta {
            padding: 10px;
            text-align: center;
            font-size: 14px;
            background-color: #fff;
        }

        .doc-title {
            font-weight: 600;
            color: #333;
        }
    </style>
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
        document.addEventListener('DOMContentLoaded', function() {
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
            document.getElementById('is_admin').checked = user.is_admin;
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function bindRoleForm() {
            const form = document.getElementById('roleForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
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
            const status = document.getElementById('statusFilter').value;
            const tutor = document.getElementById('tutorFilter').value;
            const type = document.getElementById('typeFilter').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase();

            const rows = document.querySelectorAll('#requestsTable tbody tr');
            let anyVisible = false;

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status') || '';
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

        function translateStatus(status) {
            const statusMap = {
                BROUILLON: "Brouillon",
                SOUMISE: "Soumise",
                VALID_PEDAGO: "Validée par référent pédagogique",
                REFUSEE_PEDAGO: "Refusée par référent pédagogique",
                EN_ATTENTE_SIGNATURE_ENT: "En attente de signature entreprise",
                SIGNEE_PAR_ENTREPRISE: "Signée par l’entreprise",
                EN_ATTENTE_CFA: "En attente CFA",
                VALID_CFA: "Validée par le CFA",
                REFUSEE_CFA: "Refusée par le CFA",
                EN_ATTENTE_SECRETAIRE: "En attente du secrétariat",
                VALID_SECRETAIRE: "Validée par le secrétariat",
                REFUSEE_SECRETAIRE: "Refusée par le secrétariat",
                EN_ATTENTE_DIRECTION: "En attente de la direction",
                VALID_DIRECTION: "Validée par la direction",
                REFUSEE_DIRECTION: "Refusée par la direction",
                VALIDE: "Demande validée",
                SOUTENANCE_PLANIFIEE: "Soutenance planifiée",
                ANNULEE: "Annulée",
                EXPIREE: "Expirée"
            };
            return statusMap[status?.toUpperCase()] || status;
        }

        function openRequestModal(req) {
            const modal = document.getElementById('requestModal');
            const details = document.getElementById('requestDetails');
            const tutorSelect = document.getElementById('modalTutor');

            let docsHtml = '';
            if (req.documents && req.documents.length > 0) {
                docsHtml = '<section class="document-section"><h4>📎 Documents disponibles :</h4><div class="document-grid">';
                req.documents.forEach(doc => {
                    const fileUrl = '/stalhub/document/view?file=' + encodeURIComponent(doc.file_path);
                    docsHtml += `
                <a href="${fileUrl}" target="_blank" class="document-card">
                    <div class="doc-preview">
                        <iframe src="${fileUrl}" frameborder="0"></iframe>
                    </div>
                    <div class="doc-meta">
                        <div class="doc-title">📄 ${doc.label}</div>
                    </div>
                </a>`;
                });
                docsHtml += '</div></section>';
            } else {
                docsHtml = '<p><em>Aucun document joint.</em></p>';
            }

            details.innerHTML = `
        <p><strong>Étudiant :</strong> ${req.student_name}</p>
        <p><strong>Entreprise :</strong> ${req.company_name}</p>
        <p><strong>Type :</strong> ${req.contract_type || '-'}</p>
        <p><strong>Statut :</strong> ${translateStatus(req.status)}</p>
        <p><strong>Mission :</strong> ${req.mission || '-'}</p>
        <p><strong>Date :</strong> ${req.start_date || '-'} → ${req.end_date || '-'}</p>
        ${docsHtml}
    `;

            tutorSelect.value = req.tutor_id;
            document.getElementById('modalRequestId').value = req.id;
            modal.style.display = 'flex';

            const form = document.getElementById('updateTutorForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                console.log("📤 Envoi de :", Object.fromEntries(formData));

                fetch('/stalhub/admin/requests/updateTutor', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(text => {
                        console.log("📄 Réponse brute :", text);
                        try {
                            const data = JSON.parse(text);
                            console.log("✅ JSON :", data);
                            if (data.status === 'success') {
                                alert('Tuteur mis à jour ✅');
                                closeRequestModal();
                                location.reload();
                            } else {
                                alert('Erreur : ' + (data.message || 'Échec de la mise à jour.'));
                            }
                        } catch (e) {
                            console.error("❌ Échec de parsing JSON", e);
                        }
                    })
                    .catch(err => {
                        console.error("❌ Erreur réseau", err);
                    });

            }, {
                once: true
            });
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }


        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        function exportUsers(format) {
            const rows = document.querySelectorAll('#userTable tr');
            let data = [
                [
                    'Prénom', 'Nom', 'Email principal', 'Email secondaire', 'Téléphone',
                    'Numéro étudiant', 'Programme', 'Parcours', 'Niveau', 'Code affectation',
                    'Rôle', 'Actif', 'Admin', 'Consentement RGPD',
                    'Étudiants suivis', 'Étudiants max', 'Créé le', 'Dernière connexion'
                ]
            ];

            rows.forEach(row => {
                if (row.style.display === 'none') return;
                const user = row.dataset.user ? JSON.parse(decodeURIComponent(row.dataset.user)) : null;
                if (user) {
                    data.push([
                        user.first_name || '',
                        user.last_name || '',
                        user.email || '',
                        user.alternate_email || '',
                        user.phone_number || '',
                        user.student_number || '',
                        user.program || '',
                        user.track || '',
                        user.level || '',
                        user.assignment_code || '',
                        roleLabel(user.role),
                        user.is_active == 1 ? 'Oui' : 'Non',
                        user.is_admin == 1 ? 'Oui' : 'Non',
                        user.consentement_rgpd == 1 ? 'Oui' : 'Non',
                        user.students_assigned || 0,
                        user.students_to_assign || 0,
                        user.created_at || '',
                        user.last_login_at || ''
                    ]);
                }
            });

            if (data.length <= 1) {
                alert("Aucun utilisateur à exporter.");
                return;
            }

            if (format === 'csv') {
                const csvContent = data.map(e => e.map(cell => `"${cell}"`).join(",")).join("\n");
                downloadFile(new Blob([csvContent], {
                    type: 'text/csv'
                }), 'utilisateurs.csv');
            } else if (format === 'excel') {
                const table = `
            <table>
                <tr>${data[0].map(c => `<th>${c}</th>`).join('')}</tr>
                ${data.slice(1).map(row => `<tr>${row.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}
            </table>
        `;
                const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
                          xmlns:x="urn:schemas-microsoft-com:office:excel"
                          xmlns="http://www.w3.org/TR/REC-html40">
                      <head><meta charset="UTF-8"></head><body>${table}</body></html>`;

                const blob = new Blob([html], {
                    type: 'application/vnd.ms-excel'
                });
                downloadFile(blob, 'utilisateurs.xls');
            } else if (format === 'print') {
                const printWindow = window.open('', '', 'width=800,height=600');
                const html = `
            <html><head><title>Utilisateurs</title>
            <style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:8px;text-align:left}</style>
            </head><body>
            <h2>Liste complète des utilisateurs</h2>
            <table>
                <thead><tr>${data[0].map(h => `<th>${h}</th>`).join('')}</tr></thead>
                <tbody>${data.slice(1).map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>
            </table>
            </body></html>
        `;
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.print();
            }
        }

        function exportRequests(format) {
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            const headers = [
                'Étudiant', 'Entreprise', 'Tuteur',
                'Nom référent', 'Prénom référent', 'Email référent', 'Poste référent',
                'Télétravail', 'Jours télétravail/semaine',
                'Type de contrat', 'Intitulé poste', 'Email encadrant',
                'Mission', 'Date début', 'Date fin', 'Heures/semaines',
                'À l’étranger', 'Pays', 'Salaire', 'Périodicité salaire',
                'Date création', 'Archivée', 'Commentaire', 'Statut', 'Mise à jour'
            ];

            const data = [headers];

            rows.forEach(row => {
                if (row.style.display === 'none') return;
                const req = row.dataset.request ? JSON.parse(row.dataset.request) : null;
                if (req) {
                    data.push([
                        req.student_name || '',
                        req.company_name || '',
                        req.tutor_name || '',
                        req.supervisor_last_name || '',
                        req.supervisor_first_name || '',
                        req.supervisor_email || '',
                        req.supervisor_position || '',
                        req.is_remote == 1 ? 'Oui' : 'Non',
                        req.remote_days_per_week ?? '',
                        req.contract_type || '',
                        req.job_title || '',
                        req.referent_email || '',
                        req.mission || '',
                        req.start_date || '',
                        req.end_date || '',
                        req.weekly_hours ?? '',
                        req.is_abroad == 1 ? 'Oui' : 'Non',
                        req.country || '',
                        req.salary_value ?? '',
                        req.salary_duration || '',
                        req.created_on || '',
                        req.archived == 1 ? 'Oui' : 'Non',
                        req.comment || '',
                        req.status || '',
                        req.updated_at || ''
                    ]);
                }
            });

            if (data.length <= 1) return alert("Aucune demande à exporter.");

            const downloadFile = (blob, filename) => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            };

            if (format === 'csv') {
                const csvContent = data.map(row => row.map(cell => `"${cell}"`).join(",")).join("\n");
                downloadFile(new Blob([csvContent], {
                    type: 'text/csv'
                }), 'demandes.csv');
            } else if (format === 'excel') {
                const table = `
            <table>
                <tr>${data[0].map(c => `<th>${c}</th>`).join('')}</tr>
                ${data.slice(1).map(row => `<tr>${row.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}
            </table>`;
                const html = `<html><head><meta charset="UTF-8"></head><body>${table}</body></html>`;
                downloadFile(new Blob([html], {
                    type: 'application/vnd.ms-excel'
                }), 'demandes.xls');
            } else if (format === 'print') {
                const html = `
            <html><head><title>Export demandes</title>
            <style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px}</style>
            </head><body>
            <h2>Liste des demandes</h2>
            <table>
                <thead><tr>${data[0].map(c => `<th>${c}</th>`).join('')}</tr></thead>
                <tbody>${data.slice(1).map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>
            </table>
            </body></html>
        `;
                const win = window.open('', '', 'width=900,height=600');
                win.document.write(html);
                win.document.close();
                win.print();
            }
        }

        function filterCompanies() {
            const search = document.getElementById("companySearch").value.toLowerCase();
            const rows = document.querySelectorAll("tbody tr");

            let anyVisible = false;
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const siret = row.cells[1].textContent.toLowerCase();
                const city = row.cells[2].textContent.toLowerCase();

                const match = name.includes(search) || siret.includes(search) || city.includes(search);
                row.style.display = match ? "" : "none";
                if (match) anyVisible = true;
            });

            const emptyRow = document.querySelector("tbody .empty-message");
            if (emptyRow) emptyRow.style.display = anyVisible ? "none" : "";
        }

        function exportCompanies(format) {
            const rows = document.querySelectorAll("tbody tr");
            const headers = ["SIRET", "Nom", "Adresse", "Code postal", "Ville", "Pays", "Email", "Détails", "Créé le"];
            const data = [headers];

            rows.forEach(row => {
                if (row.style.display === "none") return;
                const comp = row.dataset.company ? JSON.parse(row.dataset.company) : null;
                if (comp) {
                    data.push([
                        comp.siret || "",
                        comp.name || "",
                        comp.address || "",
                        comp.postal_code || "",
                        comp.city || "",
                        comp.country || "",
                        comp.email || "",
                        comp.details || "",
                        comp.created_at || ""
                    ]);
                }
            });

            if (data.length <= 1) return alert("Aucune entreprise à exporter.");

            const download = (blob, filename) => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            };

            if (format === "csv") {
                const csv = data.map(row => row.map(c => `"${c}"`).join(",")).join("\n");
                download(new Blob([csv], {
                    type: "text/csv"
                }), "entreprises.csv");
            } else if (format === "excel") {
                const table = `
            <table>
                <tr>${headers.map(h => `<th>${h}</th>`).join("")}</tr>
                ${data.slice(1).map(row => `<tr>${row.map(c => `<td>${c}</td>`).join("")}</tr>`).join("")}
            </table>`;
                const html = `<html><head><meta charset="UTF-8"></head><body>${table}</body></html>`;
                download(new Blob([html], {
                    type: "application/vnd.ms-excel"
                }), "entreprises.xls");
            } else if (format === "print") {
                const html = `
            <html><head><title>Entreprises</title>
            <style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px}</style>
            </head><body>
            <h2>Liste des entreprises</h2>
            <table>
                <thead><tr>${headers.map(c => `<th>${c}</th>`).join("")}</tr></thead>
                <tbody>${data.slice(1).map(r => `<tr>${r.map(c => `<td>${c}</td>`).join("")}</tr>`).join("")}</tbody>
            </table>
            </body></html>
        `;
                const win = window.open("", "", "width=800,height=600");
                win.document.write(html);
                win.document.close();
                win.print();
            }
        }

        function roleLabel(role) {
            const labels = {
                student: 'Étudiant',
                cfa: 'CFA',
                director: 'Direction',
                company: 'Entreprise',
                reviewer: 'Relecteur',
                professional_responsible: 'Responsable pédagogique',
                academic_secretary: 'Secrétariat',
                tutor: 'Tuteur'
            };
            return labels[role] || role;
        }

        function downloadFile(content, fileName, type) {
            const blob = new Blob([content], {
                type
            });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = fileName;
            a.click();
        }
    </script>

</body>

</html>