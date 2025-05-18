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
                <td><a href="/stalhub/admin/companies/view?id=<?= $company['id'] ?>">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
            <tr><td colspan="4">Aucune entreprise trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
