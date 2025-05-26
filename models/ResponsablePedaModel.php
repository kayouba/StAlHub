<?php
namespace App\Model;

use App\Lib\Database;
use PDO;
use Exception;

class ResponsablePedaModel {
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }
     /**
     * MAPPING DES TYPES DE CONTRAT
     * Basé sur vos données réelles : apprenticeship, stage
     */
    private const CONTRACT_TYPE_MAPPING = [
        'apprenticeship' => 'Alternance',
        'stage' => 'Stage',
        'internship' => 'Stage',
    ];

    /**
     * MAPPING DES STATUTS
     * Basé sur vos données réelles : REFUSEE_PEDAGO, VALID_PEDAGO, SOUMISE
     */
    private const STATUS_MAPPING = [
        'SOUMISE' => 'attente',
        'VALID_PEDAGO' => 'validee', 
        'REFUSEE_PEDAGO' => 'refusee',
        'EN_ATTENTE' => 'attente',
    ];

    /**
     * MAPPING DES FORMATIONS
     * Basé sur vos données réelles de la table users
     */
    private const FORMATION_MAPPING = [
        'Master 1 Miage' => 'Master 1 Miage',
        'M1 MIAGE 2024-2025' => 'Master 1 Miage',
        'Master 1' => 'Master 1 Miage',
        'Master 2' => 'Master 2 Miage',
        'Licence 3' => 'Licence 3 Miage',
        'L3' => 'Licence 3 Miage',
        'M1' => 'Master 1 Miage',
       
    ];

    
/**
     * Récupère toutes les demandes avec mapping des valeurs
     * 
     * @return array Les demandes avec les valeurs mappées pour l'affichage
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                r.id,
                CONCAT(u.first_name, ' ', u.last_name) as etudiant,
                u.program as formation,
                c.name as entreprise,
                r.created_on,
                r.contract_type,
                r.status,
                r.start_date,
                r.end_date,
                r.mission,
                r.job_title,
                r.salary_value,
                r.salary_duration
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            ORDER BY r.created_on DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Applique le mapping à chaque demande
        return array_map([$this, 'mapDemandeValues'], $demandes);
    }

    /**
     * Récupère une demande par ID avec mapping des valeurs
     * 
     * @param int $id L'ID de la demande
     * @return array|false La demande mappée ou false si non trouvée
     */
    public function getById(int $id): array|false
    {
        $sql = "
            SELECT 
                r.*,
                CONCAT(u.first_name, ' ', u.last_name) as etudiant,
                u.email,
                u.student_number as student_id,
                u.phone_number as telephone,
                u.program as formation,
                c.name as entreprise,
                CONCAT(t.first_name, ' ', t.last_name) as tutor_name
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            LEFT JOIN users t ON r.tutor_id = t.id
            WHERE r.id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$demande) {
            return false;
        }
        
        // Applique le mapping à la demande
        return $this->mapDemandeValues($demande);
    }

    /**
     * Applique le mapping des valeurs pour une demande
     * 
     * @param array $demande La demande brute de la BDD
     * @return array La demande avec les valeurs mappées
     */
    private function mapDemandeValues(array $demande): array
    {
        // ============================================================================
        // MAPPING DU TYPE DE CONTRAT
        // ============================================================================
        if (isset($demande['contract_type'])) {
            $demande['type'] = self::CONTRACT_TYPE_MAPPING[$demande['contract_type']] 
                ?? ucfirst($demande['contract_type']);
        }

        // ============================================================================
        // MAPPING DU STATUT
        // ============================================================================
        if (isset($demande['status'])) {
            $demande['etat'] = self::STATUS_MAPPING[$demande['status']] 
                ?? strtolower($demande['status']);
        }

        // ============================================================================
        // MAPPING DE LA FORMATION
        // ============================================================================
        if (isset($demande['formation'])) {
            $demande['formation'] = self::FORMATION_MAPPING[$demande['formation']] 
                ?? $demande['formation'];
        }

        // ============================================================================
        // FORMATAGE DE LA DATE
        // ============================================================================
        if (isset($demande['created_on'])) {
            $date = new \DateTime($demande['created_on']);
            $demande['date'] = $date->format('d/m/Y');
        }

        // ============================================================================
        // NETTOYAGE DES VALEURS NULL
        // ============================================================================
        $demande['formation'] = $demande['formation'] ?? 'Non renseigné';
        $demande['telephone'] = $demande['telephone'] ?? 'Non renseigné';
        $demande['job_title'] = $demande['job_title'] ?? $demande['mission'] ?? 'Non renseigné';

        return $demande;
    }

    /**
     * Récupère tous les tuteurs avec leurs quotas
     * 
     * @return array Liste des tuteurs avec quotas
     */
    public function getAllTuteurs(): array
    {
        $sql = "
            SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as nom_complet,
                u.first_name,
                u.last_name,
                COALESCE(u.students_to_assign, 0) as quota_max,
                COALESCE(u.students_assigned, 0) as quota_actuel
            FROM users u
            WHERE u.role = 'tutor'
            ORDER BY u.last_name, u.first_name
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les tuteurs avec leurs quotas pour affichage
     * 
     * @return array Tuteurs avec quotas formatés
     */
    public function getTuteursAvecQuotas(): array
    {
        $sql = "
            SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as nom,
                COALESCE(u.students_to_assign, 0) as quota_max,
                COALESCE(u.students_assigned, 0) as quota_actuel
            FROM users u
            WHERE u.role = 'tutor'
            ORDER BY u.students_assigned ASC, u.last_name
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un tuteur peut encore accepter des étudiants
     * 
     * @param int $tuteur_id L'ID du tuteur
     * @return bool True si le tuteur peut accepter, false sinon
     */
    public function verifierQuotaTuteur(int $tuteur_id): bool
    {
        $sql = "
            SELECT 
                COALESCE(students_to_assign, 0) as quota_max,
                COALESCE(students_assigned, 0) as quota_actuel
            FROM users u
            WHERE u.id = :tuteur_id AND u.role = 'tutor'
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tuteur_id', $tuteur_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        return $result['quota_actuel'] < $result['quota_max'];
    }

    /**
     * Traite une demande (validation, refus)
     * 
     * @param int $id L'ID de la demande
     * @param string $action L'action à effectuer
     * @param string $commentaire Le commentaire
     * @param int|null $tuteur_id L'ID du tuteur (optionnel)
     * @return bool Succès ou échec
     */
    public function traiterDemande(int $id, string $action, string $commentaire = '', ?int $tuteur_id = null): bool
    {
        // Mapping des actions vers les statuts de la BDD
        $statusMapping = [
            'valider' => 'VALID_PEDAGO',
            'refuser' => 'REFUSEE_PEDAGO'  
        ];
        
        $status = $statusMapping[$action] ?? 'SOUMISE';
        
        $sql = "
            UPDATE requests 
            SET status = :status,
                comment = :comment,
                tutor_id = :tutor_id,
                updated_at = NOW()
            WHERE id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':comment', $commentaire);
        $stmt->bindParam(':tutor_id', $tuteur_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Affecte automatiquement un tuteur selon les quotas
     * 
     * @return int|null L'ID du tuteur affecté ou null si aucun disponible
     */
    public function affecterTuteurAutomatiquement(): ?int
    {
        $sql = "
            SELECT u.id
            FROM users u
            WHERE u.role = 'tutor' 
            AND u.students_assigned < u.students_to_assign
            ORDER BY u.students_assigned ASC
            LIMIT 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['id'] : null;
    }

    /**
     * Récupère le nom d'un tuteur
     * 
     * @param int $tuteur_id L'ID du tuteur
     * @return string Le nom complet du tuteur
     */
    public function getNomTuteur(int $tuteur_id): string
    {
        $sql = "
            SELECT CONCAT(first_name, ' ', last_name) as nom_complet
            FROM users 
            WHERE id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $tuteur_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['nom_complet'] : 'Tuteur inconnu';
    }


    /**
     * Incrémente le nombre d’étudiants assignés à un tuteur
     */
    public function incrementerQuotaTuteur(int $tuteur_id): void {
        $sql = "
            UPDATE users 
            SET students_assigned = students_assigned + 1
            WHERE id = ? AND role = 'tutor'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tuteur_id]);
    }

    /**
     * Décrémente le quota d’un tuteur (minimum 0)
     */
    public function decrementerQuotaTuteur(int $tuteur_id): void {
        $sql = "
            UPDATE users 
            SET students_assigned = GREATEST(students_assigned - 1, 0)
            WHERE id = ? AND role = 'tutor'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tuteur_id]);
    }

    /**
     * Met à jour le quota maximum d’un tuteur
     */
    public function updateQuotaMaxTuteur(int $tuteur_id, int $nouveau_quota): bool {
        $sql = "
            UPDATE users 
            SET students_to_assign = ?
            WHERE id = ? AND role = 'tutor'
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nouveau_quota, $tuteur_id]);
    }

    /**
     * Récupère les statistiques générales sur les tuteurs
     */
    public function getStatistiquesTuteurs(): array {
        $sql = "
            SELECT 
                COUNT(*) as total_tuteurs,
                SUM(COALESCE(students_to_assign, 5)) as total_places,
                SUM(COALESCE(students_assigned, 0)) as total_assignes,
                COUNT(CASE 
                    WHEN COALESCE(students_assigned, 0) >= COALESCE(students_to_assign, 5) 
                    THEN 1 END) as tuteurs_complets
            FROM users
            WHERE role = 'tutor' AND is_active = 1
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
/**
 * Change le tuteur d'une demande déjà validée avec historique
 */
public function updateRequestTutor(int $demande_id, int $nouveau_tuteur_id, string $motif): bool
{
    try {
        $this->pdo->beginTransaction();
        
        // Récupérer les infos actuelles
        $sql = "SELECT tutor_id, status FROM requests WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $demande_id]);
        $demande_actuelle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$demande_actuelle || $demande_actuelle['status'] !== 'VALID_PEDAGO') {
            throw new Exception("Demande non trouvée ou non validée");
        }
        
        $ancien_tuteur_id = $demande_actuelle['tutor_id'];
        
        // Vérifier que le nouveau tuteur est différent
        if ($ancien_tuteur_id == $nouveau_tuteur_id) {
            throw new Exception("Le nouveau tuteur doit être différent de l'actuel");
        }
        
        // Vérifier disponibilité du nouveau tuteur
        if (!$this->verifierQuotaTuteur($nouveau_tuteur_id)) {
            throw new Exception("Le nouveau tuteur a atteint son quota maximum");
        }
        
        // Mettre à jour la demande
        $sql = "UPDATE requests SET tutor_id = :nouveau_tuteur, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nouveau_tuteur' => $nouveau_tuteur_id, 'id' => $demande_id]);
        
        // Enregistrer dans l'historique
        $ancien_tuteur_nom = $this->getNomTuteur($ancien_tuteur_id);
        $nouveau_tuteur_nom = $this->getNomTuteur($nouveau_tuteur_id);
        $commentaire = "Changement de tuteur : $ancien_tuteur_nom → $nouveau_tuteur_nom. Motif : $motif";
        
        $sql = "INSERT INTO status_history (request_id, previous_status, comment, changed_at) VALUES (?, 'VALID_PEDAGO', ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$demande_id, $commentaire]);
        
        $this->pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $this->pdo->rollback();
        return false;
    }
}

/**
 * Récupère les changements de tuteur d'une demande
 */
public function getChangementsTuteur(int $demande_id): array
{
    $sql = "
        SELECT 
            comment,
            DATE_FORMAT(changed_at, '%d/%m/%Y à %H:%i') as date_formatee
        FROM status_history 
        WHERE request_id = :request_id 
        AND comment LIKE '%Changement de tuteur%'
        ORDER BY changed_at DESC
    ";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['request_id' => $demande_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Enregistre une action dans l'historique
 */
public function ajouterHistorique(int $request_id, string $previous_status, string $comment): bool
{
    $sql = "INSERT INTO status_history (request_id, previous_status, comment, changed_at) VALUES (?, ?, ?, NOW())";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([$request_id, $previous_status, $comment]);
}
    

}
