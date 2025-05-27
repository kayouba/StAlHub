<?php use App\Lib\StatusTranslator; ?>
<?php $get = $_GET; ?>
<?php $activeTab = $get['tab'] ?? 'pending'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - CFA Dashboard</title>
    <link rel="stylesheet" href="/stalhub/public/css/dashbord-cfa.css">
    <script src="/stalhub/public/js/cfa-dashboard-enhanced.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="cfa-main">
    <h1>🎓 Tableau de bord CFA</h1>

    <div class="filters">
        <form method="get" id="filterForm">
            <input type="hidden" name="tab" id="tabInput" value="<?= $activeTab ?>">
            <label for="track">Formation :</label>
            <select name="track" id="track" onchange="this.form.submit()">
                <option value="">Tous</option>
                <?php foreach ($tracks as $track): ?>
                    <option value="<?= htmlspecialchars($track) ?>" <?= ($get['track'] ?? '') === $track ? 'selected' : '' ?>>
                        <?= htmlspecialchars($track) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="program">Niveau :</label>
            <select name="program" id="program" onchange="this.form.submit()">
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
        <button class="tab-button <?= $activeTab === 'pending' ? 'active' : '' ?>" data-tab="pending">📥 À valider</button>
        <button class="tab-button <?= $activeTab === 'validated' ? 'active' : '' ?>" data-tab="validated">✅ Validées</button>
    </div>

    <section id="pending" class="tab-content <?= $activeTab === 'pending' ? 'active' : '' ?>">
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
                                <a href="/stalhub/request/pdf?id=<?= $req['id'] ?>" target="_blank" class="pdf-btn">📄 PDF</a>
                                <form action="/stalhub/cfa/validate" method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <button type="submit" class="validate-btn">✅ Valider</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="validated" class="tab-content <?= $activeTab === 'validated' ? 'active' : '' ?>">
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
                                <button onclick='openRequestModal(<?= json_encode($req, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="pdf-btn">👁 Voir</button>
                                <a href="/stalhub/request/pdf?id=<?= $req['id'] ?>" target="_blank" class="pdf-btn">📄 PDF</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<!-- 🪟 MODALE DEMANDE -->
<div id="requestModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRequestModal()">×</span>
        <h3>Détails de la demande</h3>
        <div id="requestDetails"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabInput = document.getElementById('tabInput');

    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');

            if (tabInput) {
                tabInput.value = button.dataset.tab;
            }
        });
    });

    document.querySelectorAll('.tab-content').forEach(tab => {
        const input = tab.querySelector('.search-input');
        if (!input) return;

        input.addEventListener('keyup', () => {
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

window.openRequestModal = function (req) {
    const modal = document.getElementById('requestModal');
    const details = document.getElementById('requestDetails');

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
    `;
    modal.style.display = 'flex';
};

window.closeRequestModal = function () {
    document.getElementById('requestModal').style.display = 'none';
};
</script>
</body>
</html>
