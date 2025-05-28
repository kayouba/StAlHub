<?php
declare(strict_types=1);

namespace Core;

use Config\Database;

abstract class Model
{
    protected \PDO $pdo;
    protected string $table;

    public function __construct()
    {
        $this->pdo   = Database::get();
    }

    /**
     * Récupère tous les enregistrements.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(sprintf('SELECT * FROM `%s`', $this->table));
        return $stmt->fetchAll();
    }

    /**
     * Récupère un enregistrement par son ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(sprintf('SELECT * FROM `%s` WHERE id = ?', $this->table));
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Insère un nouvel enregistrement.
     * @param array $data tableau associatif colonne => valeur
     */
    public function insert(array $data): int
    {
        $cols  = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql   = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(',', $cols),
            implode(',', $placeholders)
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un enregistrement existant.
     */
    public function update(int $id, array $data): bool
    {
        $cols = [];
        foreach (array_keys($data) as $col) {
            $cols[] = "`$col` = ?";
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE id = ?',
            $this->table,
            implode(', ', $cols)
        );
        $stmt = $this->pdo->prepare($sql);
        $values = array_values($data);
        $values[] = $id;
        return $stmt->execute($values);
    }

    /**
     * Supprime un enregistrement par son ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(sprintf('DELETE FROM `%s` WHERE id = ?', $this->table));
        return $stmt->execute([$id]);
    }
}