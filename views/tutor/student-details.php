<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'étudiant</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="admin-dashboard">
    <h1>Détails de l'étudiant</h1>

    <h2><?= htmlspecialchars($detail['last_name'] . ' ' . $detail['first_name']) ?></h2>
    <p><strong>Email :</strong> <?= htmlspecialchars($detail['email']) ?></p>
    <p><strong>Téléphone :</strong> <?= htmlspecialchars($detail['phone_number']) ?></p>
    <p><strong>Entreprise :</strong> <?= htmlspecialchars($detail['company_name']) ?></p>
    <p><strong>Contrat :</strong> <?= htmlspecialchars($detail['contract_type']) ?></p>
    <p><strong>Mission :</strong> <?= nl2br(htmlspecialchars($detail['mission'])) ?></p>
    <p><strong>Début :</strong> <?= $detail['start_date'] ?></p>
    <p><strong>Fin :</strong> <?= $detail['end_date'] ?></p>
    <p><strong>Salaire :</strong> <?= $detail['salary_value'] ?> / <?= $detail['salary_duration'] ?></p>
</main>

</body>
</html>
