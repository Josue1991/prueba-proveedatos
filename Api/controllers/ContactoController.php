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
            || (isset($data['ciudad']) && strlen($data['ciudad']) > 100)
        ) {
            $error = 'Uno o más campos superan la longitud máxima permitida';
            return false;
        }

        return true;
    }
}
