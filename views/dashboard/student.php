<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/dashbord-student.css"> <!-- Mets ton propre chemin -->
    <style>

    </style>
</head>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
<body>


<div class="header" style="display: flex; justify-content: center; align-items: center;">
    <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
</div>

    <a href="/stalhub/student/new-request">
        <button style="float: right; padding:10px; margin:3px">
            + Nouvelle demande
        </button>
    </a>
    <div class="main">

        <div class="welcome">
            <h2>Vos demandes</h2>
            <div class="stats">
                <div class="stat-box">En attente<br><strong>0</strong></div>
                <div class="stat-box">Validée<br><strong>1</strong></div>
                <div class="stat-box">Refusée<br><strong>1</strong></div>
                <div class="stat-box">Brouillon<br><strong>1</strong></div>
            </div>
        </div>

        <div class="stage-section">
            <div class="stage-title">STAGE / ALTERNANCE EN COURS</div>
            <table>
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>État</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Orange</td>
                        <td>01/04/2022</td>
                        <td>01/08/2022</td>
                        <td>Validée</td>
                        <td><a class="voir-link" href="#">voir</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
