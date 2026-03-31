<?php

class ReporteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function logExport(string $formato, string $ordenadoPor, string $direccion, int $total): array|false
    {
        $sql  = 'INSERT INTO reporte (formato, ordenado_por, direccion, total_registros)
                 VALUES (:formato, :ordenado_por, :direccion, :total)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':formato'      => $formato,
            ':ordenado_por' => $ordenadoPor,
            ':direccion'    => $direccion,
            ':total'        => $total,
        ]);

        return $this->getById((int) $this->db->lastInsertId());
    }

    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM reporte ORDER BY created_at DESC')->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM reporte WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateEstado(int $id, string $estado): array|false
    {
        $stmt = $this->db->prepare('UPDATE reporte SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => $estado, ':id' => $id]);
        return $stmt->rowCount() > 0 ? $this->getById($id) : false;
    }
}
