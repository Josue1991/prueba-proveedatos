<?php

class ProvinciaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre_provincia, capital_provincia, id_region
             FROM Provincia
             ORDER BY nombre_provincia ASC'
        );
        return $stmt->fetchAll();
    }

    public function getByRegion(int $regionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre_provincia, capital_provincia, id_region
             FROM Provincia
             WHERE id_region = :regionId
             ORDER BY nombre_provincia ASC'
        );
        $stmt->execute([':regionId' => $regionId]);
        return $stmt->fetchAll();
    }
}
