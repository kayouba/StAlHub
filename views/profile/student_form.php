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
  </style>
</head>
<body>

  <div class="container">
    <h2><i class="fas fa-user-graduate"></i> Compléter le Profil Étudiant</h2>

    <form action="/stalhub/profile/submit" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div>
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom">
        </div>
        <div>
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom">
        </div>
      </div>

      <label for="email">Adresse email</label>
      <input type="email" id="email" name="email">

      <label for="num-etudiant">Numéro étudiant</label>
      <input type="text" id="num-etudiant" name="num-etudiant">

      <div class="row">
        <div>
          <label for="formation">Formation</label>
          <select id="formation" name="formation">
            <option value="">-- Sélectionner --</option>
            <option value="L3">Licence 3</option>
            <option value="M1">Master 1</option>
            <option value="M2">Msater 2</option>
          </select>
        </div>
        <div>
          <label for="parcours">Parcours</label>
          <select id="parcours" name="parcours">
            <option value="MIAGE" selected>MIAGE</option>
          </select>
        </div>
      </div>

      <label for="annee">Année d'études</label>
      <select id="annee" name="annee">
        <option value="">-- Sélectionner --</option>
        <option value="2024-2025">2024 - 2025</option>
        <option value="2025-2026">2025 - 2026</option>
        <option value="2026-2027">2026 - 2027</option>
        <option value="2027-2028">2027 - 2028</option>
        <option value="2028-2029">2028 - 2029</option>
        <option value="2029-2030">2029 - 2030</option>
        <option value="2030-2031">2030 - 2031</option>
      </select>

      <label for="cv">Téléverser votre CV <span style="font-weight: normal; font-size: 12px;">(PDF uniquement)</span></label>
      <input type="file" id="cv" name="cv" accept=".pdf">

      <button type="submit" class="btn">Enregistrer</button>
    </form>
  </div>

</body>
</html>