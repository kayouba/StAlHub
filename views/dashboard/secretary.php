<?php
// Traitement de l'upload de convention en AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_convention') {
    header('Content-Type: application/json');
    
    try {
        // Vérifier les données reçues
        if (!isset($_POST['demande_id']) || !isset($_FILES['convention'])) {
            throw new Exception('Données manquantes');
        }
        
        $demandeId = intval($_POST['demande_id']);
        $file = $_FILES['convention'];
        
        // Vérifications du fichier
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors de l\'upload du fichier');
        }
        
        // Vérifier la taille (max 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('Fichier trop volumineux (max 10MB)');
        }
        
        // Vérifier le type MIME
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé. Seuls les fichiers PDF et Word sont acceptés.');
        }
        
        // Générer un nom unique pour le fichier
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "convention_" . $demandeId . "_" . uniqid() . "." . $extension;
        
        // Définir le dossier de destination (modifié)
        $uploadDir = __DIR__ . '/../public/uploads/users/demandes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $filename;
        
        // Déplacer le fichier uploadé
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Impossible de sauvegarder le fichier');
        }
        
        // Connexion à la base de données (adaptez selon votre configuration)
        // require_once __DIR__ . '/../config/database.php';
        
        // Mettre à jour la base de données
        /*
        $stmt = $pdo->prepare("
            UPDATE demandes 
            SET convention_path = ?, 
                status = 'convention_envoyee',
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$filename, $demandeId]);
        */
        
        // Réponse de succès
        echo json_encode([
            'success' => true,
            'message' => 'Convention uploadée et envoyée avec succès',
            'filename' => $filename,
            'new_status' => 'Convention envoyée'
        ]);
        exit;
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Le reste de votre code PHP pour afficher la page
function statusToCssClass($status) {
  $status = strtolower($status);
  return match ($status) {
    'valide', 'validé', 'complete', 'valid_secretaire' => 'complete',
    'soumise', 'transmise', 'en_attente_secretaire' => 'transmise',
    'refusee', 'refusé', 'incomplete', 'refusee_secretaire' => 'incomplete',
    'attente' => 'transmise',
    'convention_envoyee', 'convention envoyée' => 'convention-sent', // Nouvelle classe
    default => 'transmise'
  };
}

function formatStatus($status) {
  return match ($status) {
    'REFUSEE_SECRETAIRE' => 'incomplet',
    'VALID_SECRETAIRE' => 'validé',
    'EN_ATTENTE_SECRETAIRE' => 'en attente',
    'SOUMISE' => 'soumise',
    'CONVENTION_ENVOYEE' => 'Convention envoyée',
    default => strtolower($status)
  };
}

function getDisplayStatus($demande) {
  // Si on a un état calculé depuis les documents, on l'utilise
  if (isset($demande['etat'])) {
    return match ($demande['etat']) {
      'validee' => 'validé',
      'refusee' => 'incomplet',
      'attente' => 'en attente',
      'convention_envoyee' => 'Convention envoyée',
      default => 'en attente'
    };
  }
  
  // Sinon on utilise le statut de la demande
  return formatStatus($demande['status'] ?? '');
}

function getDisplayStatusClass($demande) {
  // Si on a un état calculé depuis les documents, on l'utilise
  if (isset($demande['etat'])) {
    return match ($demande['etat']) {
      'validee' => 'complete',
      'refusee' => 'incomplete',
      'attente' => 'transmise',
      'convention_envoyee' => 'convention-sent',
      default => 'transmise'
    };
  }
  
  // Sinon on utilise le statut de la demande
  return statusToCssClass($demande['status'] ?? '');
}

// Ici, ajoutez votre logique pour récupérer les données utilisateur et demandes
// Par exemple :
// require_once __DIR__ . '/../config/database.php';
// $user = getCurrentUser(); // fonction à adapter selon votre système
// $demandes = getAllDemandes(); // fonction à adapter selon votre système
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Secrétaire</title>
  <link rel="stylesheet" href="/stalhub/public/css/secretary-dashboard.css">
  <script src="/stalhub/public/js/secretary-dashboard.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main>
  <h1>Bienvenue, <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h1>
  <p class="bienvenue">Vous êtes connecté en tant que secrétariat pédagogique.</p>
  
  <h1>Demandes de Stage</h1>
  
  <div class="filters">
    <select id="filter-formation">
      <option value="">Toutes les formations</option>
      <option value="licence 3">Licence 3</option>
      <option value="master 1">Master 1</option>
      <option value="master 2">Master 2</option>
    </select>
    
    <select id="filter-etat">
      <option value="">Tous les états</option>
      <option value="validé">Validée</option>
      <option value="incomplet">Incomplet</option>
      <option value="en attente">En attente</option>
      <option value="soumise">Soumise</option>
    </select>
    
    <input type="text" id="search" placeholder="Rechercher" />
  </div>
  
  <table>
    <thead>
      <tr>
        <th>Nom Prénom</th>
        <th>Formation</th>
        <th>Parcours</th>
        <th>Entreprise</th>
        <th>Date</th>
        <th>Type</th>
        <th>État</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="table-body">
      <?php
        $demandes = array_filter($demandes, function ($demande) {
          return strtolower($demande['type'] ?? '') === 'stage';
        });
      ?>

      <?php foreach ($demandes as $demande): ?>
        <?php
          $statusLabel = getDisplayStatus($demande);
          $statusClass = getDisplayStatusClass($demande);
        ?>
        <tr>
          <td><?= htmlspecialchars($demande['etudiant']) ?></td>
          <td><?= htmlspecialchars($demande['formation'] ?? 'Non renseignée') ?></td>
          <td><?= htmlspecialchars($demande['parcours']) ?></td>
          <td><?= htmlspecialchars($demande['entreprise']) ?></td>
          <td><?= htmlspecialchars($demande['date'] ?? '') ?></td>
          <td><?= htmlspecialchars($demande['type'] ?? '') ?></td>
          <td class="<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></td>
          <td>
          <a href="/stalhub/secretary/details?id=<?= $demande['id'] ?>" title="Voir">
            👁️
          </a>
          <button class="upload-btn" data-id="<?= $demande['id'] ?>" title="Télécharger la convention">
            📤
          </button>
          
        </td>

        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<div id="upload-popup" class="popup-overlay" style="display:none;">
  <div class="popup-content" onclick="event.stopPropagation();">
    <h2>Télécharger la convention</h2>
    <p>Veuillez télécharger la convention ici :</p>
    <input type="file" id="convention-file" accept=".pdf,.doc,.docx" />
    <div class="popup-actions">
      <button id="send-to-student">Envoyer à l'étudiant pour signer</button>
      <button id="close-popup">Annuler</button>
    </div>
  </div>
</div>

</body>
</html>
<script>
function openConventionModal() {
    document.getElementById('conventionModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('conventionModal').style.display = 'none';
}
</script>
