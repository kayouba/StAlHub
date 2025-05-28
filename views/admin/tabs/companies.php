<link rel="stylesheet" href="/stalhub/public/css/modal-companies-admin.css">

<div class="export-buttons">
    <button onclick="exportCompanies('csv')">⬇️ CSV</button>
    <button onclick="exportCompanies('excel')">📊 Excel</button>
    <button onclick="exportCompanies('print')">🖨️ Imprimable</button>
</div>

<div class="filter-bar">
    <div class="filter-group">
        <label for="companySearch">🔍 Recherche :</label>
        <input type="text" id="companySearch" onkeyup="filterCompanies()" placeholder="Nom, ville, SIRET..." style="padding: 6px 10px; border-radius: 6px; border: 1px solid #ccc;">
    </div>
</div>

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
            <tr data-company="<?= htmlspecialchars(json_encode($company), ENT_QUOTES, 'UTF-8') ?>">

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
            <tr>
                <td colspan="4">Aucune entreprise trouvée.</td>
            </tr>
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
