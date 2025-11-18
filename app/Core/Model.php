<?php
/**
 * Modèle de base avec support multi-tenant
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $multiTenant = true; // Active le filtrage par company_id

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les enregistrements
     */
    public function all($companyId = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($this->multiTenant && $companyId !== null) {
            $sql .= " WHERE company_id = ?";
            $params[] = $companyId;
        }

        $sql .= " ORDER BY {$this->primaryKey} DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Récupère un enregistrement par ID
     */
    public function find($id, $companyId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->multiTenant && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }

        return $this->db->queryOne($sql, $params);
    }

    /**
     * Crée un nouvel enregistrement
     */
    public function create($data)
    {
        $fields = array_keys($data);
        $values = array_values($data);

        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $fieldsStr = implode(',', $fields);

        $sql = "INSERT INTO {$this->table} ({$fieldsStr}) VALUES ({$placeholders})";

        $this->db->execute($sql, $values);

        return $this->db->lastInsertId();
    }

    /**
     * Met à jour un enregistrement
     */
    public function update($id, $data, $companyId = null)
    {
        $fields = array_keys($data);
        $values = array_values($data);

        $setClause = implode(' = ?, ', $fields) . ' = ?';

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        $values[] = $id;

        if ($this->multiTenant && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $values[] = $companyId;
        }

        return $this->db->execute($sql, $values);
    }

    /**
     * Supprime un enregistrement
     */
    public function delete($id, $companyId = null)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->multiTenant && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }

        return $this->db->execute($sql, $params);
    }

    /**
     * Recherche avec des conditions
     */
    public function where($conditions, $companyId = null)
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }

        if ($this->multiTenant && $companyId !== null) {
            $whereClauses[] = "company_id = ?";
            $params[] = $companyId;
        }

        $whereStr = implode(' AND ', $whereClauses);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereStr}";

        return $this->db->query($sql, $params);
    }

    /**
     * Compte les enregistrements
     */
    public function count($companyId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if ($this->multiTenant && $companyId !== null) {
            $sql .= " WHERE company_id = ?";
            $params[] = $companyId;
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }
}
