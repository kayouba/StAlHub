<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes étudiants assignés</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="admin-dashboard">
    <h1>Étudiants assignés</h1>

    <table class="styled-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Type de contrat</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['last_name'] . ' ' . $s['first_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['contract_type']) ?></td>
                <td><?= htmlspecialchars($s['start_date']) ?></td>
                <td><?= htmlspecialchars($s['end_date']) ?></td>
                <td><a href="/stalhub/tutor/student?id=<?= $s['request_id'] ?>">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>