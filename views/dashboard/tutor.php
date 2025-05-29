<?php

use App\Lib\StatusTranslator;

$students_assigned = $students_assigned ?? 0;
$students_to_assign = $students_to_assign ?? 0;
$requests = $requests ?? [];

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>StalHub - Dashboard Tuteur</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/stalhub/public/css/dashbord-tutors.css">

</head>

<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="admin-dashboard">
        <h1>Tableau de bord du Tuteur</h1>

        <div class="stats">
            <div class="card blue">
                <h2><?= $students_assigned ?></h2>
                <p>Étudiants actuellement assignés</p>
            </div>
        </div>

        <section>
            <h3>Capacité de suivi</h3>
            <form method="POST" action="/stalhub/tutor/update">
                <label for="students_to_assign">Nombre d'étudiants que vous pouvez suivre :</label>
                <input type="number" name="students_to_assign" id="students_to_assign" min="0" value="<?= $students_to_assign ?>">
                <button type="submit">Mettre à jour</button>
            </form>
        </section>

        <section>
            <h3>📋 Vos demandes assignées</h3>
            <?php if (empty($requests)): ?>
                <p>Aucune demande ne vous est encore assignée.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Entreprise</th>
                            <th>Contrat</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['student_first_name'] . ' ' . $req['student_last_name']) ?></td>
                                <td><?= htmlspecialchars($req['company_name'] ?? '—') ?></td>
                                <td><?= StatusTranslator::contractType($req['contract_type']) ?></td>
                                <td><?= htmlspecialchars($req['start_date']) ?> ➡️ <?= htmlspecialchars($req['end_date']) ?></td>
                                <td><?= StatusTranslator::translate(($req['status'])) ?></td>
                                <td>
                                    <button class="action-link" onclick='openRequestModal(<?= json_encode($req, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>👁 Voir</button>

                                    <?php if ($req['can_sign_convention'] ?? false): ?>
                                        <a class="action-link" href="/stalhub/tutor/sign-convention?id=<?= $req['id'] ?>" title="Signer la convention">✍️ Signer</a>
                                    <?php endif; ?>



                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <!-- 🌌 MODALE -->
    <div id="requestModal" class="modal">
        <div class="modal-glass">
            <span class="close" onclick="closeRequestModal()">×</span>
            <h3>Détails de la demande</h3>
            <div id="requestDetails"></div>
        </div>
    </div>

    <script>
        function openRequestModal(data) {
            const modal = document.getElementById('requestModal');
            const details = document.getElementById('requestDetails');

            details.innerHTML = `
        <h4>👨‍🎓 Étudiant</h4>
        <ul>
            <li><strong>Nom :</strong> ${data.student_first_name} ${data.student_last_name}</li>
            <li><strong>Mail :</strong> ${data.student_email}</li>
            <li><strong>Téléphone :</strong> ${data.student_phone_number}</li>
            <li><strong>Niveau :</strong> ${data.student_level}</li>
            <li><strong>Programme :</strong> ${data.student_program}</li>
            <li><strong>Numéro étudiant :</strong> ${data.student_student_number}</li>
        </ul>

        <h4>🏢 Entreprise</h4>
        <ul>
            <li><strong>Nom :</strong> ${data.company_name ?? '—'}</li>
            <li><strong>SIRET :</strong> ${data.company_siret ?? '—'}</li>
            <li><strong>Code postal :</strong> ${data.company_postal_code ?? '—'}</li>
            <li><strong>Ville & Pays :</strong> ${data.company_city ?? '—'}, ${data.company_country ?? '—'}</li>
        </ul>

        <h4>📄 Demande</h4>
        <ul>
            <li><strong>Type de contrat :</strong> ${formatContractType(data.contract_type)}</li>
            <li><strong>Période :</strong> du ${data.start_date} au ${data.end_date}</li>
            <li><strong>Statut :</strong> ${translate(data.status)}</li>
            <li><strong>Email du référent :</strong> ${data.supervisor_email}</li>
            <li><strong>Numero du référent :</strong> ${data.supervisor_num}</li>
            <li><strong>Poste :</strong> ${data.job_title}</li>
            <li><strong>Missions :</strong> ${data.mission}</li>
            <li><strong>Heures / semaine :</strong> ${data.weekly_hours}</li>
        </ul>
    `;

            modal.style.display = 'flex';
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        function formatContractType(type) {
            switch (type) {
                case 'apprenticeship':
                    return 'Apprentissage';
                case 'stage':
                    return 'Stage';
                default:
                    return type;
            }
        }

        function translate(status) {
            const map = {
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
            return map[status] || status;
        }
    </script>
</body>

</html>