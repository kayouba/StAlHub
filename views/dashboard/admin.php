<?php
$users = $users ?? [];
$pendingCount = $pendingCount ?? 0;
$validatedCount = $validatedCount ?? 0;
$rejectedCount = $rejectedCount ?? 0;

function safe($value) {
    return htmlspecialchars($value ?? '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Admin Dashboard</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
</head>

<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="admin-dashboard">
        <h1>Tableau de bord administrateur</h1>

        <div class="stats">
            <div class="card blue">
                <h2><?= $pendingCount ?></h2>
                <p>Demandes à valider</p>
            </div>
            <div class="card green">
                <h2><?= $validatedCount ?></h2>
                <p>Demandes validées</p>
            </div>
            <div class="card red">
                <h2><?= $rejectedCount ?></h2>
                <p>Demandes refusées</p>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active">Utilisateurs</button>
            <button class="tab" onclick="location.href='/stalhub/admin/requests'">Demandes</button>
            <button class="tab" onclick="location.href='/stalhub/admin/companies'">Entreprises</button>
        </div>

        <section class="user-list">
            <div class="header">
                <h2>Liste des étudiants</h2>
                <a href="/stalhub/admin/add-user" class="button" style ="color:white;">+ Ajouter un administrateur</a>
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
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= safe($user['last_name'] . ' ' . $user['first_name']) ?></td>
                            <td><?= safe($user['email']) ?></td>
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
        </section>
    </main>
</body>
</html>
