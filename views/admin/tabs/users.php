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
                    <a href="javascript:void(0);" onclick="openModal(
                        <?= $user['id'] ?>,
                        '<?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>',
                        '<?= $user['role'] ?>'
                    )">Voir</a>
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

<!-- MODAL HTML (placé ici pour être accessible par le parent) -->
<div id="userModal" class="modal" style="display:none;">
    <div class="modal-content" style="background:#fff; padding:20px; border-radius:8px; width:400px; margin:100px auto; position:relative;">
        <span onclick="closeModal()" style="position:absolute; right:15px; top:10px; cursor:pointer; font-size:20px;">&times;</span>
        <h3>Détails de l'utilisateur</h3>
        <div id="userInfo"></div>

        <form id="roleForm">
            <input type="hidden" name="user_id" id="user_id">
            <label for="role">Changer le rôle</label>
            <select name="role" id="role" required style="width:100%; padding:8px; margin:10px 0;">
                <option value="student">Étudiant</option>
                <option value="admin">Administrateur</option>
            </select>
            <button type="submit" style="padding:10px 20px; background:#004A7C; color:white; border:none; border-radius:4px; cursor:pointer;">Mettre à jour</button>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}
</style>
