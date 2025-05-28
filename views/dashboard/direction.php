<?php

use App\Lib\StatusTranslator; ?>
<?php $get = $_GET; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>StalHub - Direction Dashboard</title>
    <link rel="stylesheet" href="/stalhub/public/css/dashbord-cfa.css">
</head>

<body>

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="cfa-main">
        <h1>🎓 Tableau de bord Direction</h1>

        <div class="filters">
            <form method="get" id="filterForm">
                <label for="track">Formation :</label>
                <select name="track" id="track" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Tous</option>
                    <?php foreach ($tracks as $track): ?>
                        <option value="<?= htmlspecialchars($track) ?>" <?= ($get['track'] ?? '') === $track ? 'selected' : '' ?>>
                            <?= htmlspecialchars($track) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="program">Niveau :</label>
                <select name="program" id="program" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Tous</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= htmlspecialchars($program) ?>" <?= ($get['program'] ?? '') === $program ? 'selected' : '' ?>>
                            <?= htmlspecialchars($program) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="tabs">
            <button class="tab-button active" data-tab="pending">📥 À valider</button>
            <button class="tab-button" data-tab="validated">✅ Validées</button>
        </div>

        <section id="pending" class="tab-content active">
            <input class="search-input" type="text" placeholder="🔎 Rechercher un étudiant...">
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Formation</th>
                        <th>Niveau</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $req): ?>
                        <?php
                        $matchTrack = empty($get['track']) || $req['track'] === $get['track'];
                        $matchProgram = empty($get['program']) || $req['program'] === $get['program'];
                        ?>
                        <?php if ($matchTrack && $matchProgram): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['student']) ?></td>
                                <td><?= htmlspecialchars($req['program']) ?></td>
                                <td><?= htmlspecialchars($req['track'] ?? '—') ?></td>
                                <td><?= htmlspecialchars(StatusTranslator::translate($req['status'])) ?></td>
                                <td class="actions">
                                    <button onclick='openRequestModal(<?= json_encode($req, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="pdf-btn">👁 Voir</button>
                                    <a href="/stalhub/direction/request?id=<?= $req['id'] ?>" class="validate-btn">✅ Valider</a>

                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="validated" class="tab-content">
            <input class="search-input" type="text" placeholder="🔎 Rechercher un étudiant...">
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Programme</th>
                        <th>Niveau</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($validatedRequests as $req): ?>
                        <?php
                        $matchTrack = empty($get['track']) || $req['track'] === $get['track'];
                        $matchProgram = empty($get['program']) || $req['program'] === $get['program'];
                        ?>
                        <?php if ($matchTrack && $matchProgram): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['student']) ?></td>
                                <td><?= htmlspecialchars($req['program']) ?></td>
                                <td><?= htmlspecialchars($req['track']) ?></td>
                                <td><?= htmlspecialchars(StatusTranslator::translate($req['status'])) ?></td>
                                <td class="actions">
                                    <a href="/stalhub/direction/view?id=<?= urlencode($req['id']) ?>&readonly=1" class="pdf-btn">👁 Voir</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </section>

        <!-- MODALE DEMANDE -->
        <div id="requestModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeRequestModal()">×</span>
                <h3>Détails de la demande</h3>
                <div id="requestDetails"></div>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Gestion des onglets
            document.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    button.classList.add('active');
                    document.getElementById(button.dataset.tab).classList.add('active');
                });
            });

            // Recherche en temps réel
            document.querySelectorAll('.tab-content').forEach(tab => {
                const input = tab.querySelector('.search-input');
                if (!input) return;

                input.addEventListener('input', () => {
                    const search = input.value.toLowerCase();
                    tab.querySelectorAll('tbody tr').forEach(row => {
                        const match = [...row.cells].some(cell =>
                            cell.textContent.toLowerCase().includes(search)
                        );
                        row.style.display = match ? '' : 'none';
                    });
                });
            });
        });
        window.openRequestModal = function(req) {
            const modal = document.getElementById('requestModal');
            const details = document.getElementById('requestDetails');

            let docsHtml = '';
            if (req.documents && req.documents.length > 0) {
                docsHtml = '<h4>📎 Documents disponibles :</h4><ul>';
                req.documents.forEach(doc => {
                    const link = '/stalhub/document/view?file=' + encodeURIComponent(doc.file_path);
                    docsHtml += `<li><a href="${link}" target="_blank">📄 ${doc.label}</a></li>`;
                });
                docsHtml += '</ul>';
            } else {
                docsHtml = '<p><em>Aucun document joint.</em></p>';
            }

            details.innerHTML = `
        <p><strong>Étudiant :</strong> ${req.student}</p>
        <p><strong>Programme :</strong> ${req.program}</p>
        <p><strong>Formation :</strong> ${req.track ?? '-'}</p>
        <p><strong>Statut :</strong> ${req.status}</p>
        <p><strong>Email référent :</strong> ${req.referent_email ?? '-'}</p>
        <p><strong>Mission :</strong> ${req.mission ?? '-'}</p>
        <p><strong>Durée :</strong> ${req.start_date} → ${req.end_date}</p>
        <p><strong>Heures/semaine :</strong> ${req.weekly_hours ?? '-'}h</p>
        <p><strong>Salaire :</strong> ${req.salary_value ?? '-'} / ${req.salary_duration ?? '-'}</p>
        ${docsHtml}
    `;

            modal.style.display = 'flex';
        };

        window.closeRequestModal = function() {
            document.getElementById('requestModal').style.display = 'none';
        };
    </script>