<?php

class RegionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, nombre_region FROM Region ORDER BY nombre_region ASC');
        return $stmt->fetchAll();
    }
}
