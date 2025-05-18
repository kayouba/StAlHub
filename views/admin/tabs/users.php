<table>
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] === 'admin' ? 'Administrateur' : 'Étudiant' ?></td>
                <td>
                    <a href="/stalhub/admin/users/view?id=<?= $user['id'] ?>">Voir</a>
                    <?php if ($user['role'] === 'student'): ?>
                        | <a href="/stalhub/admin/users/suspend?id=<?= $user['id'] ?>">Suspendre</a>
                    <?php endif; ?>
                    | <a href="/stalhub/admin/users/delete?id=<?= $user['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="4">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
