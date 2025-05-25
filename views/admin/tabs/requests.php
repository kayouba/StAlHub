<link rel="stylesheet" href="/stalhub/public/css/modal-request-admin.css">
<div class="export-buttons">
    <button onclick="exportRequests('csv')">⬇️ Exporter CSV</button>
    <button onclick="exportRequests('excel')">📊 Exporter Excel</button>
    <button onclick="exportRequests('print')">🖨️ Version Imprimable</button>
</div>

<div class="filter-bar">
    <div class="filter-row">
        <div class="filter-group">
            <label for="statusFilter">📌  Statut</label>
            <select id="statusFilter" onchange="filterRequests()">
                <option value="all">Tous</option>
                <option value="SOUMISE">Soumise</option>
                <option value="VALID_PEDAGO">Validée pédagogiquement</option>
                <option value="REFUSEE_PEDAGO">Refusée pédagogiquement</option>
                <option value="EN_ATTENTE_SECRETAIRE">En attente secrétaire</option>
                <option value="VALID_SECRETAIRE">Validée par le secrétariat</option>
                <option value="REFUSEE_SECRETAIRE">Refusée par le secrétariat</option>
                <option value="EN_ATTENTE_CFA">En attente CFA</option>
                <option value="VALID_CFA">Validée CFA</option>
                <option value="REFUSEE_CFA">Refusée CFA</option>
                <option value="VALIDE">Validée finale</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="tutorFilter">👤  Tuteur</label>
            <select id="tutorFilter" onchange="filterRequests()">
                <option value="all">Tous</option>
                <?php foreach ($tutors as $tutor): ?>
                    <option value="<?= htmlspecialchars($tutor['id']) ?>">
                        <?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="filter-row">
        <div class="filter-group">
            <label for="typeFilter">📂  Contrat</label>
            <select id="typeFilter" onchange="filterRequests()">
                <option value="all">Tous</option>
                <option value="apprenticeship">Apprentissage</option>
                <option value="stage">Stage</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="searchInput">🔍  Recherche</label>
            <input type="text" id="searchInput" onkeyup="filterRequests()" placeholder="Nom étudiant ou entreprise...">
        </div>
    </div>
</div>

<!-- 📋 TABLEAU DES DEMANDES -->
<table id="requestsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom étudiant</th>
            <th>Entreprise</th>
            <th>Statut</th>
            <th>Tuteur</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $req): ?>
            <tr
                data-request='<?= htmlspecialchars(json_encode($req), ENT_QUOTES, "UTF-8") ?>'
                data-status="<?= htmlspecialchars($req['status']) ?>"
                data-tutor="<?= htmlspecialchars($req['tutor_id'] ?? '') ?>"
                data-type="<?= htmlspecialchars($req['contract_type'] ?? '') ?>"
            >

                <td><?= $req['id'] ?></td>
                <td data-label="Étudiant"><?= htmlspecialchars($req['student_name'] ?? '—') ?></td>
                <td data-label="Entreprise"><?= htmlspecialchars($req['company_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><?= htmlspecialchars($req['tutor_name'] ?? '—') ?></td>
                <td>
                    <a href="javascript:void(0);" onclick='openRequestModal(<?= json_encode($req, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?>)'>Voir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
            <tr class="empty-message"><td colspan="6">Aucune demande trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- 🪟 MODALE DEMANDE -->
<div id="requestModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeRequestModal()">×</span>
        <h3>Détails de la demande</h3>
        <div id="requestDetails"></div>

        <form id="updateTutorForm">
            <label for="modalTutor">👤 Changer le tuteur</label>
            <select id="modalTutor" name="tutor_id">
                <?php foreach ($tutors as $tutor): ?>
                    <option value="<?= $tutor['id'] ?>">
                        <?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']. ' ( '. $tutor['students_assigned']. ' / ' .$tutor['students_to_assign']. ' )') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="request_id" id="modalRequestId">
            <button type="submit">💾 Enregistrer</button>
        </form>
    </div>
</div>
<script>

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('updateTutorForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/stalhub/admin/requests/updateTutor', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Tuteur mis à jour ✅');
                    closeRequestModal();
                    location.reload();
                } else {
                    alert('Erreur : ' + (data.message || 'Échec de la mise à jour.'));
                }
            });
        });
    } else {
        console.warn("⚠️ Le formulaire #updateTutorForm n'a pas été trouvé dans le DOM.");
    }
});
</script>