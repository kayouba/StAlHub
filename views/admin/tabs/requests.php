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
                <td><?= htmlspecialchars($req['student_name'])?></td>
                <td><?= htmlspecialchars($req['company_name']) ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><a href="/stalhub/admin/requests/view?id=<?= $req['id'] ?>">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
            <tr><td colspan="5">Aucune demande trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
