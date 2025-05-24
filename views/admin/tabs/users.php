<link rel="stylesheet" href="/stalhub/public/css/modal-users-admin.css">
<div class="filter-bar">
    <div class="filter-group">
        <label for="roleFilter">🎯 Filtrer par rôle</label>
        <select id="roleFilter" onchange="filterUsers()">
            <option value="all">Tous</option>
            <?php
            $roleLabels = [
                'student' => 'Étudiant',
                'cfa' => 'CFA',
                'director' => 'Direction',
                'company' => 'Entreprise',
                'reviewer' => 'Relecteur',
                'professional_responsible' => 'Responsable pédagogique',
                'academic_secretary' => 'Secrétariat',
                'tutor' => 'Tuteur'
            ];

            $roles = array_unique(array_column($users, 'role'));
            foreach ($roles as $role) {
                $value = strtolower($role);
                $label = $roleLabels[$value] ?? ucfirst($value);
                echo "<option value=\"$value\">$label</option>";
            }
            ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="searchInput">🔍 Rechercher</label>
        <input type="text" id="searchInput" onkeyup="searchUsers()" placeholder="Nom, prénom ou email...">
    </div>
</div>



<table>
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="userTable">
        <?php foreach ($users as $user): ?>
            <tr data-role="<?= strtolower(htmlspecialchars($user['role'])) ?>">
                <td data-label="Nom complet"><?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                <?php
                $roleLabels = [
                    'student' => 'Étudiant',
                    'cfa' => 'CFA',
                    'director' => 'Direction',
                    'company' => 'Entreprise',
                    'reviewer' => 'Relecteur',
                    'professional_responsible' => 'Responsable pédagogique',
                    'academic_secretary' => 'Secrétariat',
                    'tutor' => 'Tuteur'
                ];
                $role = $user['role'] ?? 'unknown';
                $label = $roleLabels[$role] ?? ucfirst($role);
                ?>
                <td data-label="Rôle"><?= htmlspecialchars($label) ?></td>
                <td data-label="Actions">
                    <a href="javascript:void(0);" onclick='openModal(<?= json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Voir</a>
                    <?php if ($user['role'] === 'student'): ?>
                        <a href="/stalhub/admin/users/suspend?id=<?= $user['id'] ?>">
                            <?= $user['is_active'] ? 'Suspendre' : 'Activer' ?>
                        </a>
                    <?php endif; ?>
                    <a href="/stalhub/admin/users/delete?id=<?= $user['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="4" style="text-align:center;">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- MODAL HTML -->
<div id="userModal" class="modal" style="display:none;">
    <div class="modal-content" style="background:#fff; padding:20px; border-radius:8px; width:400px; margin:100px auto; position:relative;">
        <span onclick="closeModal()" style="position:absolute; right:15px; top:10px; cursor:pointer; font-size:20px;">&times;</span>
        <h3>Détails de l'utilisateur</h3>
        <div id="userInfo"></div>

        <form id="roleForm">
            <input type="hidden" name="user_id" id="user_id">
            <label for="role">Changer le rôle</label>
            <select name="role" id="role" required style="width:100%; padding:8px; margin:10px 0;">
                <option value="cfa">CFA</option>
                <option value="director">Direction</option>
                <option value="company">Entreprise</option>
                <option value="student">Étudiant</option>
                <option value="reviewer">Relecteur</option>
                <option value="professional_responsible">Responsable pédagogique</option>
                <option value="academic_secretary">Secrétariat</option>
                <option value="tutor">Tuteur</option>
            </select>

            <label for="is_admin" style="display:block; margin-top:10px;">
                <input type="checkbox" name="is_admin" id="is_admin" style="margin-right:5px;">
                Administrateur
            </label>
            <button type="submit" style="padding:10px 20px; background:#004A7C; color:white; border:none; border-radius:4px; cursor:pointer;">Mettre à jour</button>
        </form>
    </div>
</div>


<script>
function openModal(user) {
    const modal = document.getElementById('userModal');
    modal.style.display = 'block';

    const activeText = user.is_active == 1 ? '✅ Actif' : '❌ Inactif';
    const rgpdText = user.consentement_rgpd == 1 ? '✅ Oui' : '❌ Non';

    document.getElementById('userInfo').innerHTML = `
        <p><strong>Nom :</strong> ${user.last_name} ${user.first_name}</p>
        <p><strong>Email :</strong> ${user.email}</p>
        <p><strong>Email secondaire :</strong> ${user.alternate_email || '-'}</p>
        <p><strong>Téléphone :</strong> ${user.phone_number || '-'}</p>
        <p><strong>Numéro étudiant :</strong> ${user.student_number || '-'}</p>
        <p><strong>Programme :</strong> ${user.program || '-'}</p>
        <p><strong>Parcours :</strong> ${user.track || '-'}</p>
        <p><strong>Niveau :</strong> ${user.level || '-'}</p>
        <p><strong>Code affectation :</strong> ${user.assignment_code || '-'}</p>
        <p><strong>Statut :</strong> ${activeText}</p>
        <p><strong>Consentement RGPD :</strong> ${rgpdText}</p>
        <p><strong>Créé le :</strong> ${user.created_at}</p>
        <p><strong>Dernière connexion :</strong> ${user.last_login_at || '-'}</p>
    `;

    document.getElementById('user_id').value = user.id;
    document.getElementById('role').value = user.role;
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}


</script>
