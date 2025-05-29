<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>StalHub - Tableau de bord</title>
  <link rel="stylesheet" href="/stalhub/public/css/dashbord-student.css">
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>


<div class="main">

  <?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert-success">
      <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <div class="top-bar">
    <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
    <a href="/stalhub/student/new-request">
      <button class="btn-new">+ Nouvelle demande</button>
    </a>
  </div>

  <?php
    // Calcul des statistiques à partir des vraies données
    $statusCounts = [
      'SOUMISE' => 0,
      'VALIDE' => 0,
      'REFUSE' => 0,
    ];

    foreach ($requests as $r) {
      $status = strtoupper($r['status']);
      if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
      }
    }
  ?>

  <div class="stats">
    <div class="stat-box">En Cours de traitement<br><strong><?= $statusCounts['SOUMISE'] ?></strong></div>
    <div class="stat-box">Validée<br><strong><?= $statusCounts['VALIDE'] ?></strong></div>
    <div class="stat-box">Refusée<br><strong><?= $statusCounts['REFUSE'] ?></strong></div>
  </div>

  <div class="stage-section">
    <div class="stage-title">Toutes mes demandes</div>

    <?php if (empty($requests)): ?>
      <p style="text-align:center; padding: 20px;">Aucune demande pour le moment.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Type de demande</th>
            <th>Entreprise</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $request): ?>
            <tr>
            <td data-label="Type de demande"><?= $request['contract_type'] === 'stage' ? 'Stage' : 'Alternance' ?></td>
            <td data-label="Entreprise"><?= htmlspecialchars($request['company_name']) ?></td>
            <td data-label="Date de début"><?= htmlspecialchars($request['start_date']) ?></td>
            <td data-label="Date de fin"><?= htmlspecialchars($request['end_date']) ?></td>
            <td data-label="État"><?= htmlspecialchars($request['translated_status']) ?></td>
            <td data-label="Action">
                <a class="voir-link" href="/stalhub/student/request/view?id=<?= $request['id'] ?>">voir</a>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>

      </table>
    <?php endif; ?>
  </div>
</div>

</body>
</html>


