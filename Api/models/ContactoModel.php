<?php

class ContactoModel
{
    private PDO $db;

    private const BASE_SELECT = '
        SELECT c.id, c.nombre, c.email, c.telefono, c.id_provincia, c.created_at,
               p.nombre_provincia, p.capital_provincia,
               r.id   AS id_region,
               r.nombre_region
        FROM contacto c
        LEFT JOIN Provincia p ON c.id_provincia = p.id
        LEFT JOIN Region    r ON p.id_region    = r.id
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(self::BASE_SELECT . ' ORDER BY c.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getAllSorted(string $orderBy, string $dir): array
    {
        $allowed = ['id', 'nombre', 'email', 'telefono', 'id_provincia', 'created_at'];
        if (!in_array($orderBy, $allowed, true)) {
            $orderBy = 'created_at';
        }
        $dir  = $dir === 'DESC' ? 'DESC' : 'ASC';
        $col  = 'c.' . $orderBy;
        $stmt = $this->db->query(self::BASE_SELECT . " ORDER BY {$col} {$dir}");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE c.id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): array|false
    {
        $sql  = 'INSERT INTO contacto (nombre, email, telefono, id_provincia)
                 VALUES (:nombre, :email, :telefono, :id_provincia)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':email'       => $data['email'],
            ':telefono'    => $data['telefono'],
            ':id_provincia' => $data['id_provincia'] ?: null,
        ]);

        return $this->getById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false
    {
        $sql  = 'UPDATE contacto
                 SET nombre = :nombre, email = :email,
                     telefono = :telefono, id_provincia = :id_provincia
                 WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'       => $data['nombre'],
            ':email'        => $data['email'],
            ':telefono'     => $data['telefono'],
            ':id_provincia' => $data['id_provincia'] ?: null,
            ':id'           => $id,
        ]);

        return $stmt->rowCount() > 0 ? $this->getById($id) : false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contacto WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

