<!-- Table des demandes -->
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
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
}
.modal-content {
    background: #fff;
    color: #333;
    padding: 24px;
    width: 500px;
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    position: relative;
    animation: fadeIn 0.2s ease-out;
}
.modal-content span {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 22px;
    color: #888;
    cursor: pointer;
}
.modal-content span:hover {
    color: #000;
}
.modal-content h3 {
    margin-top: 0;
    margin-bottom: 16px;
    color: #004A7C;
}
.modal-content p {
    margin: 6px 0;
    font-size: 14px;
}
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.95);}
    to {opacity: 1; transform: scale(1);}
}
</style>

<!-- Script JS -->
<script>
</script>
