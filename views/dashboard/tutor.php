<?php
$students_assigned = $students_assigned ?? 0;
$students_to_assign = $students_to_assign ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Dashboard Tuteur</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
</head>

<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="admin-dashboard">
    <h1>Tableau de bord du Tuteur</h1>

    <div class="stats">
        <div class="card blue">
            <h2><?= $students_assigned ?></h2>
            <p>Étudiants actuellement assignés</p>
        </div>
    </div>

    <section style="margin-top: 40px;">
        <h3>Capacité de suivi</h3>
        <form method="POST" action="/stalhub/tutor/update" style="max-width: 400px;">
            <label for="students_to_assign">Nombre d'étudiants que vous pouvez suivre :</label>
            <input type="number" name="students_to_assign" id="students_to_assign" min="0" value="<?= $students_to_assign ?>" style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc;">
            <button type="submit" style="padding: 10px 20px; background-color: #004A7C; color: white; border: none; border-radius: 4px;">Mettre à jour</button>
        </form>
    </section>
</main>
</body>
</html>
