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
      'VALIDEE' => 0,
      'REFUSEE' => 0,
      'BROUILLON' => 0
    ];

    foreach ($requests as $r) {
      $status = strtoupper($r['status']);
      if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
      }
    }
  ?>

  <div class="stats">
    <div class="stat-box">En attente<br><strong><?= $statusCounts['SOUMISE'] ?></strong></div>
    <div class="stat-box">Validée<br><strong><?= $statusCounts['VALIDEE'] ?></strong></div>
    <div class="stat-box">Refusée<br><strong><?= $statusCounts['REFUSEE'] ?></strong></div>
    <div class="stat-box">Brouillon<br><strong><?= $statusCounts['BROUILLON'] ?></strong></div>
  </div>

  <div class="stage-section">
    <div class="stage-title">Stage / Alternance en cours</div>

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
              <td><?= $request['contract_type'] === 'stage' ? 'Stage' : 'Alternance' ?></td>
              <td><?= htmlspecialchars($request['company_name']) ?></td>
              <td><?= htmlspecialchars($request['start_date']) ?></td>
              <td><?= htmlspecialchars($request['end_date']) ?></td>
              <td><?= ucfirst(strtolower($request['status'])) ?></td>
              <td><a class="voir-link" href="/stalhub/student/request/view?id=<?= $request['id'] ?>">voir</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
