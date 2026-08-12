<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | El sitio institucional no necesita CORS (todo se sirve desde el mismo origen vía
    | Inertia). Lo único que lo usa hoy es la versión "demo" de Actualizar Datos, pensada
    | para ser llamada desde un sitio estático completamente aparte (ver
    | DemoActualizarDatosController) -- sin cookies/sesión cross-origin, así que
    | 'supports_credentials' queda en false a propósito.
    |
    */

    'paths' => ['api/demo/*'],

    // GET se suma por el listado protegido por token para la página oculta de administración
    // (ver DemoActualizarDatosController::registros()) -- el resto de las rutas demo siguen
    // siendo POST.
    'allowed_methods' => ['GET', 'POST'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
