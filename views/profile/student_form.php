<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Compléter le Profil Étudiant</title>
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
  </style>
</head>
<body>

  <div class="container">
    <h2><i class="fas fa-user-graduate"></i> Compléter le Profil Étudiant</h2>
    
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

      <label for="num-etudiant">Numéro étudiant *</label>
      <input type="text" id="num-etudiant" name="num-etudiant" value="<?php echo isset($user['student_number']) ? htmlspecialchars($user['student_number']) : ''; ?>" required>

      <div class="row">
        <div>
          <label for="formation">Formation *</label>
          <select id="formation" name="formation" required>
            <option value="">-- Sélectionner --</option>
            <option value="L3" <?php echo (isset($user['formation']) && $user['formation'] === 'L3') ? 'selected' : ''; ?>>Licence 3</option>
            <option value="M1" <?php echo (isset($user['formation']) && $user['formation'] === 'M1') ? 'selected' : ''; ?>>Master 1</option>
            <option value="M2" <?php echo (isset($user['formation']) && $user['formation'] === 'M2') ? 'selected' : ''; ?>>Master 2</option>
          </select>
        </div>
        <div>
          <label for="parcours">Parcours *</label>
          <select id="parcours" name="parcours" required>
            <option value="MIAGE" selected>MIAGE</option>
          </select>
        </div>
      </div>

      <label for="annee">Année d'études *</label>
      <select id="annee" name="annee" required>
        <option value="">-- Sélectionner --</option>
        <option value="2024-2025" <?php echo (isset($user['annee']) && $user['annee'] === '2024-2025') ? 'selected' : ''; ?>>2024 - 2025</option>
        <option value="2025-2026" <?php echo (isset($user['annee']) && $user['annee'] === '2025-2026') ? 'selected' : ''; ?>>2025 - 2026</option>
        <option value="2026-2027" <?php echo (isset($user['annee']) && $user['annee'] === '2026-2027') ? 'selected' : ''; ?>>2026 - 2027</option>
        <option value="2027-2028" <?php echo (isset($user['annee']) && $user['annee'] === '2027-2028') ? 'selected' : ''; ?>>2027 - 2028</option>
        <option value="2028-2029" <?php echo (isset($user['annee']) && $user['annee'] === '2028-2029') ? 'selected' : ''; ?>>2028 - 2029</option>
        <option value="2029-2030" <?php echo (isset($user['annee']) && $user['annee'] === '2029-2030') ? 'selected' : ''; ?>>2029 - 2030</option>
        <option value="2030-2031" <?php echo (isset($user['annee']) && $user['annee'] === '2030-2031') ? 'selected' : ''; ?>>2030 - 2031</option>
      </select>

      <label for="cv">Téléverser votre CV <span style="font-weight: normal; font-size: 12px;">(PDF uniquement, optionnel)</span></label>
      <input type="file" id="cv" name="cv" accept=".pdf">
      <?php if (isset($user['cv_filename']) && !empty($user['cv_filename'])): ?>
        <p><small>CV actuel: <?php echo htmlspecialchars($user['cv_filename']); ?></small></p>
      <?php endif; ?>

      <button type="submit" class="btn">Enregistrer</button>
    </form>
  </div>

</body>
</html>