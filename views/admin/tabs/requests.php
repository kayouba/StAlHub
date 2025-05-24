<link rel="stylesheet" href="/stalhub/public/css/modal-request-admin.css">
<div class="filter-bar">
    <div class="filter-group">
        <label for="statusFilter">📌 Statut</label>
        <select id="statusFilter" onchange="filterRequests()">
            <option value="all">Tous</option>
            <option value="SOUMISE">Soumise</option>
            <option value="VALIDEE">Validée</option>
            <option value="REFUSEE">Refusée</option>
            <!-- ajoute d'autres statuts si besoin -->
        </select>
    </div>

    <div class="filter-group">
        <label for="tutorFilter">👤 Tuteur</label>
        <select id="tutorFilter" onchange="filterRequests()">
            <option value="all">Tous</option>
            <?php foreach ($tutors as $tutor): ?>
                <option value="<?= htmlspecialchars($tutor['id']) ?>"><?= htmlspecialchars($tutor['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="typeFilter">📂 Contrat</label>
        <select id="typeFilter" onchange="filterRequests()">
            <option value="all">Tous</option>
            <option value="apprentissage">Apprentissage</option>
            <option value="stage">Stage</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="searchInput">🔍 Recherche</label>
        <input type="text" id="searchInput" onkeyup="filterRequests()" placeholder="Nom étudiant ou entreprise...">
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom étudiant</th>
            <th>Entreprise</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td><?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['student_name']) ?></td>
                <td><?= htmlspecialchars($req['company_name']) ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td>
                    <a href="javascript:void(0);" onclick='openRequestModal(<?= json_encode($req, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?>)'>Voir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
            <tr><td colspan="5">Aucune demande trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modale HTML -->
<div id="requestModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span onclick="closeRequestModal()">×</span>
        <h3>Détails de la demande</h3>
        <div id="requestDetails"></div>
    </div>
</div>

<!-- Style simple et propre -->
<style>
</style>

<!-- Script JS -->
<script>
</script>
