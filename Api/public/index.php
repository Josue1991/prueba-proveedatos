<?php

// ── CORS ──────────────────────────────────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Autoload ──────────────────────────────────────────────────────────────
$base = dirname(__DIR__);

require_once $base . '/config/database.php';
require_once $base . '/core/Database.php';
require_once $base . '/core/Controller.php';
require_once $base . '/core/Router.php';
require_once $base . '/helpers/Response.php';
require_once $base . '/models/ContactoModel.php';
require_once $base . '/models/ReporteModel.php';
require_once $base . '/models/RegionModel.php';
require_once $base . '/models/ProvinciaModel.php';
require_once $base . '/controllers/ContactoController.php';
require_once $base . '/controllers/ReporteController.php';
require_once $base . '/controllers/CatalogoController.php';

// ── Routing ───────────────────────────────────────────────────────────────
$router = new Router();
require_once $base . '/routes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
