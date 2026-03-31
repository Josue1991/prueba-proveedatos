<?php

class ContactoController extends Controller
{
    private ContactoModel $model;

    public function __construct()
    {
        $this->model = new ContactoModel();
    }

    // GET /api/contactos
    public function index(): void
    {
        $contactos = $this->model->getAll();
        $this->jsonResponse(Response::success($contactos));
    }

    // GET /api/contactos/{id}
    public function show(string $id): void
    {
        $contacto = $this->model->getById((int) $id);

        if (!$contacto) {
            $this->jsonResponse(Response::error('Contacto no encontrado', 404), 404);
            return;
        }

        $this->jsonResponse(Response::success($contacto));
    }

    // POST /api/contactos
    public function store(): void
    {
        $data = $this->getJsonInput();

        if (!$this->validate($data, $error)) {
            $this->jsonResponse(Response::error($error, 422), 422);
            return;
        }

        $contacto = $this->model->create($data);
        $this->jsonResponse(Response::success($contacto, 201), 201);
    }

    // PUT /api/contactos/{id}
    public function update(string $id): void
    {
        $existing = $this->model->getById((int) $id);

        if (!$existing) {
            $this->jsonResponse(Response::error('Contacto no encontrado', 404), 404);
            return;
        }

        $data = $this->getJsonInput();

        if (!$this->validate($data, $error)) {
            $this->jsonResponse(Response::error($error, 422), 422);
            return;
        }

        $contacto = $this->model->update((int) $id, $data);
        $this->jsonResponse(Response::success($contacto));
    }

    // DELETE /api/contactos/{id}
    public function destroy(string $id): void
    {
        $existing = $this->model->getById((int) $id);

        if (!$existing) {
            $this->jsonResponse(Response::error('Contacto no encontrado', 404), 404);
            return;
        }

        $this->model->delete((int) $id);
        $this->jsonResponse(Response::success(null, 204), 204);
    }

    // POST /api/contactos/{id}/foto
    public function uploadFoto(string $id): void
    {
        $existing = $this->model->getById((int) $id);
        if (!$existing) {
            $this->jsonResponse(Response::error('Contacto no encontrado', 404), 404);
            return;
        }

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(Response::error('No se recibió ningún archivo válido', 422), 422);
            return;
        }

        $file    = $_FILES['foto'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            $this->jsonResponse(Response::error('Solo se permiten imágenes (jpg, png, gif, webp)', 422), 422);
            return;
        }

        $blob = file_get_contents($file['tmp_name']);
        if ($blob === false) {
            $this->jsonResponse(Response::error('Error al leer el archivo', 500), 500);
            return;
        }

        $contacto = $this->model->updateFoto((int) $id, $blob, $mime);
        $this->jsonResponse(Response::success($contacto));
    }

    // GET /api/contactos/{id}/foto
    public function getFoto(string $id): void
    {
        $row = $this->model->getFoto((int) $id);

        if (!$row || empty($row['fotografia'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Sin fotografía']);
            return;
        }

        header('Content-Type: ' . ($row['fotografia_mime'] ?? 'image/jpeg'));
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . strlen($row['fotografia']));
        echo $row['fotografia'];
    }

    // ---------------------------------------------------------------
    private function validate(array $data, ?string &$error): bool
    {
        $required = ['nombre', 'email', 'telefono'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                $error = "El campo '{$field}' es requerido";
                return false;
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'El email no tiene un formato válido';
            return false;
        }

        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $data['telefono'])) {
            $error = 'El teléfono solo puede contener números, +, -, espacios y paréntesis (7-20 caracteres)';
            return false;
        }

        if (strlen($data['nombre']) > 100 || strlen($data['email']) > 100
            || strlen($data['telefono']) > 20
        ) {
            $error = 'Uno o más campos superan la longitud máxima permitida';
            return false;
        }

        if (!empty($data['id_provincia']) && !filter_var($data['id_provincia'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $error = 'El campo id_provincia debe ser un número entero positivo';
            return false;
        }

        if (!empty($data['fecha_nacimiento']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_nacimiento'])) {
            $error = 'La fecha de nacimiento debe tener el formato YYYY-MM-DD';
            return false;
        }

        if (!empty($data['fecha_ingreso']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_ingreso'])) {
            $error = 'La fecha de ingreso debe tener el formato YYYY-MM-DD';
            return false;
        }

        return true;
    }
}

