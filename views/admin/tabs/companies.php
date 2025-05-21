<link rel="stylesheet" href="/stalhub/public/css/modal-companies-admin.css">
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>SIRET</th>
            <th>Ville</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($companies as $company): ?>
            <tr>
                <td><?= htmlspecialchars($company['name']) ?></td>
                <td><?= htmlspecialchars($company['siret']) ?></td>
                <td><?= htmlspecialchars($company['city']) ?></td>
                <td>
                    <a href="javascript:void(0);" onclick='openCompanyModal(<?= json_encode($company, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?>)'>Voir</a>
                    
                    <!-- <a href="/stalhub/admin/companies/delete?id=<?= $company['id'] ?>" onclick="return confirm('Confirmer la suppression de cette entreprise ?')">Supprimer</a>-->
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
            <tr><td colspan="4">Aucune entreprise trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- ✅ MODALE ENTREPRISE -->
<div id="companyModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span onclick="closeCompanyModal()">×</span>
        <h3>Détails de l’entreprise</h3>
        <div id="companyInfo"></div>
        <hr style="margin: 20px 0;">
        <h4>Demandes associées</h4>
        <div id="companyRequests"></div>
    </div>
</div>



<script>
</script>
