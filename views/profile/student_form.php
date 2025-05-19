<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Compléter le Profil</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
    }

    .container {
      max-width: 800px;
      margin: 40px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #003366;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .row {
      display: flex;
      gap: 20px;
    }

    .row > div {
      flex: 1;
    }

    .btn {
      display: inline-block;
      margin-top: 30px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #0056b3;
    }
    
    .alert {
      padding: 10px 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-size: 14px;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .file-preview {
      margin-top: 5px;
      font-size: 12px;
      color: #007bff;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <div class="container">
    <h2>
      <?php if (isset($user['role']) && $user['role'] === 'student'): ?>
        <i class="fas fa-user-graduate"></i> Compléter le Profil Étudiant
      <?php else: ?>
        <i class="fas fa-user"></i> Compléter votre Profil
      <?php endif; ?>
    </h2>
    
    <?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($_SESSION['form_errors'] as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['form_errors']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form action="/stalhub/profile/submit" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div>
          <label for="nom">Nom *</label>
          <input type="text" id="nom" name="nom" value="<?php echo isset($user['last_name']) ? htmlspecialchars($user['last_name']) : ''; ?>" required>
        </div>
        <div>
          <label for="prenom">Prénom *</label>
          <input type="text" id="prenom" name="prenom" value="<?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : ''; ?>" required>
        </div>
      </div>

      <label for="email">Adresse email *</label>
      <input type="email" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>

              <?php if (isset($user['role']) && $user['role'] === 'student'): ?>
        <label for="num-etudiant">Numéro étudiant</label>
        <input type="text" id="num-etudiant" name="num-etudiant" value="<?php echo isset($user['student_number']) ? htmlspecialchars($user['student_number']) : ''; ?>">

        <div class="row">
          <div>
            <label for="program">Formation</label>
            <select id="program" name="program">
              <option value="NULL">-- Sélectionner --</option>
              <option value="L3" <?php echo (isset($user['program']) && $user['program'] === 'L3') ? 'selected' : ''; ?>>Licence 3</option>
              <option value="M1" <?php echo (isset($user['program']) && $user['program'] === 'M1') ? 'selected' : ''; ?>>Master 1</option>
              <option value="M2" <?php echo (isset($user['program']) && $user['program'] === 'M2') ? 'selected' : ''; ?>>Master 2</option>
            </select>
          </div>
          <div>
            <label for="track">Parcours</label>
            <select id="track" name="track">
              <option value="NULL">-- Sélectionner --</option>
              <option value="MIAGE" selected>MIAGE</option>
            </select>
          </div>
        </div>

        <label for="level">Année d'études</label>
        <?php
          // Détermine l'année scolaire en cours (août à juillet)
          $month = (int)date('m');
          $year = (int)date('Y');
          if ($month < 8) { // Si nous sommes entre janvier et juillet, l'année scolaire a commencé l'année précédente
              $startYear = $year - 1;
          } else { // Si nous sommes entre août et décembre, l'année scolaire commence cette année
              $startYear = $year;
          }
          $endYear = $startYear + 1;
          $currentSchoolYear = $startYear . '-' . $endYear;
        ?>
        <input type="text" id="level" name="level" value="<?php echo $currentSchoolYear; ?>" readonly>

        <label for="cv">Téléverser votre CV <span style="font-weight: normal; font-size: 12px;">(PDF uniquement, optionnel)</span></label>
        <input type="file" id="cv" name="cv" accept=".pdf">
        <?php
          // Vérifier si un CV existe déjà
          $userId = $_SESSION['user_id'] ?? null;
          $cvPath = "/uploads/users/$userId/cv.pdf";
          $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/stalhub' . $cvPath;
          
          if (file_exists($fullPath)):
        ?>
          <div class="file-preview">
            <a href="/stalhub<?php echo $cvPath; ?>" target="_blank">Voir le CV actuel</a>
          </div>
        <?php endif; ?>

        <label for="assurance">Téléverser votre assurance <span style="font-weight: normal; font-size: 12px;">(PDF uniquement, optionnel)</span></label>
        <input type="file" id="assurance" name="assurance" accept=".pdf">
        <?php
          // Vérifier si une assurance existe déjà
          $assurancePath = "/uploads/users/$userId/assurance.pdf";
          $fullAssurancePath = $_SERVER['DOCUMENT_ROOT'] . '/stalhub' . $assurancePath;
          
          if (file_exists($fullAssurancePath)):
        ?>
          <div class="file-preview">
            <a href="/stalhub<?php echo $assurancePath; ?>" target="_blank">Voir l'assurance actuelle</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <button type="submit" class="btn">Enregistrer</button>
    </form>
  </div>

</body>
</html>