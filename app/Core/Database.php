<?php
/**
 * Gestionnaire de base de données avec support multi-tenant
 */
class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Singleton
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Récupère la connexion PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Exécute une requête SELECT
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une seule ligne
     */
    public function queryOne($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Exécute une requête INSERT/UPDATE/DELETE
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Récupère le dernier ID inséré
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }
}
