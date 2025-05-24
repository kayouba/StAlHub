<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f2f4f8;
    }

    .container {
      max-width: 800px;
      margin: 50px auto;
      background: #fff;
      padding: 35px 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    h2 {
      text-align: center;
      font-size: 26px;
      margin-bottom: 30px;
      color: #2c3e50;
    }

    .alert {
      padding: 15px;
      margin-bottom: 25px;
      border-radius: 6px;
      font-size: 15px;
    }

    .alert-danger {
      background: #fbe4e6;
      color: #a00;
    }

    .alert-success {
      background: #e5f7ea;
      color: #2b7a41;
    }

    form label {
      display: block;
      margin: 20px 0 5px;
      font-weight: 600;
    }

    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    .section {
      margin-bottom: 35px;
    }

    .section-title {
      font-size: 18px;
      font-weight: bold;
      color: #005093;
      border-left: 4px solid #005093;
      padding-left: 10px;
      margin-bottom: 15px;
    }

    .section-note {
      font-size: 14px;
      color: #666;
      margin-top: 10px;
    }

    small {
      font-weight: normal;
      font-size: 13px;
      color: #777;
    }

    .btn {
      width: 100%;
      padding: 13px;
      background-color: #007bff;
      color: white;
      border: none;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 20px;
    }

    .btn:hover {
      background-color: #005dc3;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="container">
  <h2><i class="fas fa-user"></i> Mon Profil</h2>

  <?php if (!empty($_SESSION['form_errors'])): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($_SESSION['form_errors'] as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <form action="/stalhub/profile/submit" method="POST" enctype="multipart/form-data">
    <!-- Infos Perso -->
    <div class="section">
      <div class="section-title">Informations personnelles</div>

      <label for="nom">Nom *</label>
      <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>

      <label for="prenom">Prénom *</label>
      <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>

      <label for="email">Adresse email *</label>
      <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

      <?php if ($user['role'] === 'student'): ?>
        <label for="num-etudiant">Numéro étudiant</label>
        <input type="text" name="num-etudiant" id="num-etudiant" value="<?= htmlspecialchars($user['student_number'] ?? '') ?>">

        <label for="program">Formation</label>
        <select name="program" id="program">
          <option value="">-- Sélectionner --</option>
          <option value="L3" <?= ($user['program'] ?? '') === 'L3' ? 'selected' : '' ?>>Licence 3</option>
          <option value="M1" <?= ($user['program'] ?? '') === 'M1' ? 'selected' : '' ?>>Master 1</option>
          <option value="M2" <?= ($user['program'] ?? '') === 'M2' ? 'selected' : '' ?>>Master 2</option>
        </select>

        <label for="track">Parcours</label>
        <select name="track" id="track">
          <option value="">-- Sélectionner --</option>
          <option value="MIAGE" <?= ($user['track'] ?? '') === 'MIAGE' ? 'selected' : '' ?>>MIAGE</option>
        </select>

        <label>Année d'études</label>
        <input type="text" name="level" value="<?= date('Y') . '-' . (date('Y') + 1) ?>" readonly>
      <?php endif; ?>
    </div>

    <!-- Documents -->
    <div class="section">
      <div class="section-title">Documents (facultatifs)</div>

      <label for="cv">CV <small>Format PDF</small></label>
      <input type="file" name="cv" id="cv" accept=".pdf">
      <?php if (!empty($user['cv_filename'])): ?>
        <small>CV actuel : <?= htmlspecialchars($user['cv_filename']) ?></small><br>
        <a href="/stalhub/document/view?file=<?= urlencode('/uploads/users/' . $user['id'] . '/' . $user['cv_filename']) ?>" target="_blank">
              📄 Voir le CV
        </a>

      <?php endif; ?>

      <label for="assurance" style="margin-top: 15px;">Attestation d'assurance <small>Format PDF</small></label>
      <input type="file" name="assurance" id="assurance" accept=".pdf">
      <?php if (!empty($user['insurance_filename'])): ?>
        <small>Assurance actuelle : <?= htmlspecialchars($user['insurance_filename']) ?></small><br>
        <a href="/stalhub/document/view?file=<?= urlencode('/uploads/users/' . $user['id'] . '/' . $user['insurance_filename']) ?>" target="_blank">
          📄 Voir l'assurance
        </a>
      <?php endif; ?>

      <p class="section-note">Ces documents peuvent être ajoutés ou modifiés à tout moment.</p>
    </div>


    <button type="submit" class="btn">Enregistrer les modifications</button>
  </form>
</div>

</body>
</html>
