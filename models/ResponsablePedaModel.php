<?php
namespace App\Model;

use App\Lib\Database;
use PDO;

class ResponsablePedaModel {
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }


    public function getAll(): array {
        $sql = "
            SELECT 
                r.id,
                CONCAT(u.first_name, ' ', u.last_name) AS etudiant,
                u.role,
                u.program AS formation,
                c.name AS entreprise,
                r.start_date AS date,
                r.contract_type AS type,
                CASE
                    WHEN r.status = 'SOUMISE' THEN 'attente'
                     WHEN r.status = 'VALID_PEDAGO' THEN 'validee'
                     WHEN r.status = 'REFUSEE_PEDAGO' THEN 'refusee'
                    ELSE NULL
                END AS etat
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            WHERE LOWER(u.role) = 'student'
            AND r.status IN ('SOUMISE','VALID_PEDAGO', 'REFUSEE_PEDAGO', 'VALIDE')
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById(int $id): ?array {
        $sql = "
            SELECT
                r.*,
                u.id AS student_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone_number AS telephone,
                u.program,
                CONCAT(u.first_name, ' ', u.last_name) AS etudiant,
                c.name AS entreprise,
                t.id AS tutor_id,
                CONCAT(t.first_name, ' ', t.last_name) AS tutor_name
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            LEFT JOIN users t ON r.tutor_id = t.id
            WHERE r.id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllTuteurs(): array {
        $sql = "
            SELECT 
                id, 
                CONCAT(first_name, ' ', last_name) AS nom_complet
            FROM users
            WHERE LOWER(role) = 'tuteur'
            ORDER BY last_name, first_name
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    



    public function demanderModifications(int $id, string $commentaire): void {
        $stmt = $this->pdo->prepare("
            UPDATE requests
            SET status = 'ANNULEE', 
                comment = ?, 
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$commentaire, $id]);
    }

// Méthodes à ajouter dans votre ResponsablePedaModel.php

/**
 * Récupère les tuteurs avec leurs quotas (version statique pour les tests)
 */
public function getTuteursAvecQuotas(): array {
    // Pour l'instant, données statiques - à remplacer par la base de données plus tard
    return [
        '3' => ['nom' => 'Marie Curie', 'quota_max' => 8, 'quota_actuel' => 3],
        '4' => ['nom' => 'Pierre Martin', 'quota_max' => 6, 'quota_actuel' => 1],
        '5' => ['nom' => 'Sophie Bernard', 'quota_max' => 10, 'quota_actuel' => 7],
        '6' => ['nom' => 'Thomas Petit', 'quota_max' => 5, 'quota_actuel' => 5],
        '7' => ['nom' => 'Jean Dupont', 'quota_max' => 7, 'quota_actuel' => 2]
    ];
}

/**
 * Vérifie si un tuteur peut encore prendre des étudiants
 */
public function verifierQuotaTuteur(int $tuteur_id): bool {
    $quotas = $this->getTuteursAvecQuotas();
    
    if (!isset($quotas[$tuteur_id])) {
        return false;
    }
    
    return $quotas[$tuteur_id]['quota_actuel'] < $quotas[$tuteur_id]['quota_max'];
}
/**
 * Affecte automatiquement un tuteur selon les quotas disponibles
 */
public function affecterTuteurAutomatiquement(): ?int {
    $quotas = $this->getTuteursAvecQuotas();
    $tuteursDisponibles = [];
    
    foreach ($quotas as $id => $tuteur) {
        if ($tuteur['quota_actuel'] < $tuteur['quota_max']) {
            $placesLibres = $tuteur['quota_max'] - $tuteur['quota_actuel'];
            
            // Plus de places libres = plus de chances d'être sélectionné
            // Ajouter le tuteur plusieurs fois selon ses places libres
            for ($i = 0; $i < $placesLibres; $i++) {
                $tuteursDisponibles[] = (int)$id;
            }
        }
    }
    
    if (empty($tuteursDisponibles)) {
        return null; // Aucun tuteur disponible
    }
    
    // Sélection aléatoire pondérée
    $indexAleatoire = array_rand($tuteursDisponibles);
    return $tuteursDisponibles[$indexAleatoire];
}

/**
 * Récupère le nom d'un tuteur par son ID
 */
public function getNomTuteur(int $tuteur_id): string {
    $quotas = $this->getTuteursAvecQuotas();
    
    if (isset($quotas[$tuteur_id])) {
        return $quotas[$tuteur_id]['nom'];
    }
    
    // Fallback vers la base de données si le tuteur n'est pas dans les quotas statiques
    $sql = "SELECT CONCAT(first_name, ' ', last_name) AS nom_complet FROM users WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$tuteur_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['nom_complet'] : 'Tuteur #' . $tuteur_id;
}

/**
 * Met à jour le quota d'un tuteur après affectation (version statique pour les tests)
 * TODO: À remplacer par une mise à jour en base de données
 */
public function incrementerQuotaTuteur(int $tuteur_id): void {
    // Pour l'instant, cette méthode ne fait rien car nous utilisons des données statiques
    // Plus tard, elle mettra à jour le quota en base de données
    // Exemple futur :
    /*
    $sql = "UPDATE users SET quota_actuel = quota_actuel + 1 WHERE id = ? AND role = 'tuteur'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$tuteur_id]);
    */
}

/**
 * Mise à jour de la méthode traiterDemande pour inclure la gestion des quotas
 */
public function traiterDemande(int $id, string $action, ?string $commentaire = null, ?int $tuteur_id = null): void {
    if ($action === 'refuser') {
        $stmt = $this->pdo->prepare("
            UPDATE requests
            SET status = 'REFUSEE_PEDAGO', comment = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$commentaire, $id]);
        
    } else if ($action === 'demander_modifications') {
        $this->demanderModifications($id, $commentaire);
        
    } else if ($action === 'valider') {
        // Vérifier le quota avant l'affectation
        if (!$this->verifierQuotaTuteur($tuteur_id)) {
            throw new Exception("Le tuteur sélectionné a atteint son quota maximum d'étudiants.");
        }
        
        // Valider avec un tuteur pédagogique
        $stmt = $this->pdo->prepare("
            UPDATE requests
            SET status = 'VALID_PEDAGO', comment = ?, tutor_id = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$commentaire, $tuteur_id, $id]);
        
        // Incrémenter le quota du tuteur (version statique pour les tests)
        $this->incrementerQuotaTuteur($tuteur_id);
    }
}
    


/* 
public function validerDemande($id) {
    $stmt = $this->pdo->prepare("UPDATE requests SET status = 'VALID_PEDAGO', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
}

public function refuserDemande($id, $motif) {
    $stmt = $this->pdo->prepare("UPDATE requests SET status = 'REFUSEE_PEDAGO', comment = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$motif, $id]);
}
 */


    
}
