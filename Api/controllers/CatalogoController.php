<?php

class CatalogoController extends Controller
{
    private RegionModel   $regionModel;
    private ProvinciaModel $provinciaModel;

    public function __construct()
    {
        $this->regionModel   = new RegionModel();
        $this->provinciaModel = new ProvinciaModel();
    }

    // GET /api/regiones
    public function regiones(): void
    {
        $this->jsonResponse(Response::success($this->regionModel->getAll()));
    }

    // GET /api/provincias          → todas
    // GET /api/provincias?region_id=X → filtradas por región
    public function provincias(): void
    {
        $regionId = isset($_GET['region_id']) ? (int) $_GET['region_id'] : null;

        if ($regionId !== null && $regionId > 0) {
            $data = $this->provinciaModel->getByRegion($regionId);
        } else {
            $data = $this->provinciaModel->getAll();
        }

        $this->jsonResponse(Response::success($data));
    }
}
