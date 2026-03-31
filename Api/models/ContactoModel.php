<?php

class ContactoModel
{
    private PDO $db;

    private const BASE_SELECT = '
        SELECT c.id, c.codigo_empleado, c.cedula, c.nombre, c.apellidos, c.email, c.telefono, c.id_provincia,
               c.fecha_nacimiento, c.observaciones,
               CASE WHEN c.fotografia IS NOT NULL THEN 1 ELSE 0 END AS tiene_foto,
               c.fecha_ingreso, c.cargo, c.departamento, c.sueldo, c.jornada_parcial, c.obs_laboral,
               c.created_at,
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
        $allowed = ['id', 'codigo_empleado', 'nombre', 'email', 'telefono', 'id_provincia', 'created_at'];
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
        $sql  = 'INSERT INTO contacto
                    (cedula, nombre, apellidos, email, telefono, id_provincia,
                     fecha_nacimiento, observaciones, fecha_ingreso, cargo, departamento,
                     sueldo, jornada_parcial, obs_laboral)
                 VALUES
                    (:cedula, :nombre, :apellidos, :email, :telefono, :id_provincia,
                     :fecha_nacimiento, :observaciones, :fecha_ingreso, :cargo, :departamento,
                     :sueldo, :jornada_parcial, :obs_laboral)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cedula'           => $data['cedula']           ?? null,
            ':nombre'           => $data['nombre'],
            ':apellidos'        => $data['apellidos']        ?? null,
            ':email'            => $data['email'],
            ':telefono'         => $data['telefono'],
            ':id_provincia'     => $data['id_provincia']     ?: null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':observaciones'    => $data['observaciones']    ?? null,
            ':fecha_ingreso'    => $data['fecha_ingreso']    ?? null,
            ':cargo'            => $data['cargo']            ?? null,
            ':departamento'     => $data['departamento']     ?? null,
            ':sueldo'           => isset($data['sueldo']) && $data['sueldo'] !== '' ? $data['sueldo'] : null,
            ':jornada_parcial'  => isset($data['jornada_parcial']) && $data['jornada_parcial'] !== '' ? (int) $data['jornada_parcial'] : null,
            ':obs_laboral'      => $data['obs_laboral']      ?? null,
        ]);

        $newId  = (int) $this->db->lastInsertId();
        $codigo = $this->generateCodigo($newId, $data['departamento'] ?? '', $data['cargo'] ?? '');
        $this->db->prepare('UPDATE contacto SET codigo_empleado = :c WHERE id = :id')
             ->execute([':c' => $codigo, ':id' => $newId]);

        return $this->getById($newId);
    }

    public function update(int $id, array $data): array|false
    {
        $codigo = $this->generateCodigo($id, $data['departamento'] ?? '', $data['cargo'] ?? '');
        $sql  = 'UPDATE contacto
                 SET codigo_empleado = :codigo_empleado, cedula = :cedula, nombre = :nombre,
                     apellidos = :apellidos, email = :email, telefono = :telefono,
                     id_provincia = :id_provincia,
                     fecha_nacimiento = :fecha_nacimiento, observaciones = :observaciones,
                     fecha_ingreso = :fecha_ingreso, cargo = :cargo, departamento = :departamento,
                     sueldo = :sueldo, jornada_parcial = :jornada_parcial, obs_laboral = :obs_laboral
                 WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo_empleado'  => $codigo,
            ':cedula'           => $data['cedula']           ?? null,
            ':nombre'           => $data['nombre'],
            ':apellidos'        => $data['apellidos']        ?? null,
            ':email'            => $data['email'],
            ':telefono'         => $data['telefono'],
            ':id_provincia'     => $data['id_provincia']     ?: null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':observaciones'    => $data['observaciones']    ?? null,
            ':fecha_ingreso'    => $data['fecha_ingreso']    ?? null,
            ':cargo'            => $data['cargo']            ?? null,
            ':departamento'     => $data['departamento']     ?? null,
            ':sueldo'           => isset($data['sueldo']) && $data['sueldo'] !== '' ? $data['sueldo'] : null,
            ':jornada_parcial'  => isset($data['jornada_parcial']) && $data['jornada_parcial'] !== '' ? (int) $data['jornada_parcial'] : null,
            ':obs_laboral'      => $data['obs_laboral']      ?? null,
            ':id'               => $id,
        ]);

        return $this->getById($id);
    }

    private function generateCodigo(int $id, string $departamento, string $cargo): string
    {
        $d = mb_strtoupper(mb_substr(trim($departamento), 0, 1, 'UTF-8'), 'UTF-8');
        $c = mb_strtoupper(mb_substr(trim($cargo),        0, 2, 'UTF-8'), 'UTF-8');
        return $d . $c . $id;
    }

    public function updateFoto(int $id, string $blob, string $mime): array|false
    {
        $stmt = $this->db->prepare(
            'UPDATE contacto SET fotografia = :fotografia, fotografia_mime = :mime WHERE id = :id'
        );
        $stmt->bindParam(':fotografia', $blob, PDO::PARAM_LOB);
        $stmt->bindValue(':mime', $mime);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $this->getById($id);
    }

    public function getFoto(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT fotografia, fotografia_mime FROM contacto WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contacto WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

