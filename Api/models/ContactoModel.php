<?php

class ContactoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM contacto ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function getAllSorted(string $orderBy, string $dir): array
    {
        $allowed = ['id', 'nombre', 'email', 'telefono', 'ciudad', 'created_at'];
        if (!in_array($orderBy, $allowed, true)) {
            $orderBy = 'created_at';
        }
        $dir = $dir === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM contacto ORDER BY {$orderBy} {$dir}");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM contacto WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): array|false
    {
        $sql  = 'INSERT INTO contacto (nombre, email, telefono, ciudad)
                 VALUES (:nombre, :email, :telefono, :ciudad)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':email'    => $data['email'],
            ':telefono' => $data['telefono'],
            ':ciudad'   => $data['ciudad'] ?? null,
        ]);

        return $this->getById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false
    {
        $sql  = 'UPDATE contacto
                 SET nombre = :nombre, email = :email,
                     telefono = :telefono, ciudad = :ciudad
                 WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':email'    => $data['email'],
            ':telefono' => $data['telefono'],
            ':ciudad'   => $data['ciudad'] ?? null,
            ':id'       => $id,
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
