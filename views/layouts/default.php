<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($params['title'] ?? 'StalHub') ?></title>
</head>
<body>
  <nav>
    <a href="/">Accueil</a> |
    <a href="/login">Connexion</a>
  </nav>
  <main>
    <?= $content ?>
  </main>
</body>
</html>
