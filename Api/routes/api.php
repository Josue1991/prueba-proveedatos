<?php

/** @var Router $router */

$router->addRoute('GET',    '/api/contactos',             'ContactoController', 'index');
$router->addRoute('GET',    '/api/contactos/{id}',        'ContactoController', 'show');
$router->addRoute('POST',   '/api/contactos',             'ContactoController', 'store');
$router->addRoute('PUT',    '/api/contactos/{id}',        'ContactoController', 'update');
$router->addRoute('DELETE', '/api/contactos/{id}',        'ContactoController', 'destroy');

// Export (registered before {id} but {id} only matches digits so no conflict)
$router->addRoute('GET',    '/api/contactos/export',      'ReporteController',  'export');

// Reportes
$router->addRoute('GET',    '/api/reportes',              'ReporteController',  'index');
$router->addRoute('PUT',    '/api/reportes/{id}',         'ReporteController',  'updateEstado');
