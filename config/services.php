<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cessa_api' => [
        'url' => env('CESSA_API_URL'),
        'token' => env('CESSA_API_TOKEN'),
    ],

    'contact' => [
        'notify_email' => env('CONTACT_NOTIFICATION_EMAIL', 'cloudflare.dtic@cessa.com.bo'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'sip' => [
        'base_url' => env('SIP_BASE_URL', 'https://dev-sip.mc4.com.bo:8443'),
        'apikey' => env('SIP_QR_API_KEY'),
        'username' => env('SIP_USERNAME'),
        'password' => env('SIP_PASSWORD'),
        'apikey_servicio' => env('SIP_SERVICE_API_KEY'),
        'callback_username' => env('SIP_CALLBACK_USERNAME'),
        'callback_password' => env('SIP_CALLBACK_PASSWORD'),
    ],

    'cobranzas' => [
        // Apagado por defecto a propósito -- ver RegistrarFacturacionCobranzas. Recién prender
        // esto (COBRANZAS_FACTURACION_ENABLED=true) después de probar la integración de forma
        // aislada contra api-cobranzas-bancos (crear/pagar una transacción de prueba, bajar un
        // comprobante) desde la red donde sí es alcanzable -- ver
        // documentacion/PLAN_PAGO_Y_FACTURACION.md, "Orden recomendado para implementar". Con
        // esto apagado, los pagos por QR se siguen cobrando igual, solo no se registran como
        // factura real todavía (quedan en estado "Pagado (generando factura)").
        'enabled' => env('COBRANZAS_FACTURACION_ENABLED', false),
        'base_url' => env('COBRANZAS_BASE_URL', 'http://localhost:6001'),
        'client_id' => env('COBRANZAS_CLIENT_ID'),
        'client_secret' => env('COBRANZAS_CLIENT_SECRET'),
        // Usuario tipo Cajero con Caja habilitada para que el sitio web cobre "como si fuera
        // una entidad financiera más" -- hay que conseguirlo de quien administra el SIIC, ver
        // NECESIDADES_SIIC_FACTURACION.md §1.
        'username' => env('COBRANZAS_USERNAME'),
        'password' => env('COBRANZAS_PASSWORD'),
        // Sigla de agencia a usar al aperturar la Caja del día (ver CajaController@aperturar
        // del lado de api-cobranzas-bancos, exige 'agencia_sigla'). Confirmar el valor correcto
        // con quien administra el SIIC -- no hay uno obvio para "sitio web" en el código fuente.
        'agencia_sigla' => env('COBRANZAS_AGENCIA_SIGLA'),
        // IDs de catálogo para el "Documento" que va en /pagar-otro-documento -- identifican
        // con qué banco/canal entró el dinero (ver FacturacionRecibo::construirDocumento()).
        // Salen de GET /v1/entes y GET /v1/bancos de api-cobranzas-bancos (catálogos reales,
        // no inventados acá) -- confirmar cuál "ente" corresponde a pago electrónico/QR y cuál
        // "banco" a Banco BISA antes de prender COBRANZAS_FACTURACION_ENABLED.
        'documento_ente_id' => env('COBRANZAS_DOCUMENTO_ENTE_ID'),
        'documento_banco_id' => env('COBRANZAS_DOCUMENTO_BANCO_ID'),
    ],

    'sms' => [
        // 'log' (por defecto, solo escribe al log) o 'tigo' (API real, ver TigoSmsProvider).
        'provider' => env('SMS_PROVIDER', 'log'),
        'tigo' => [
            'base_url' => env('TIGO_SMS_BASE_URL', 'https://rest.mobylengage.com/SenderPassThroughApi'),
            // Bearer token entregado directo por Tigo (lo que se está usando hoy -- ver
            // TigoSmsProvider). Tiene vencimiento propio (24h en el que se probó primero);
            // cuando venza, Tigo tiene que entregar uno nuevo para pegar acá.
            'token' => env('TIGO_SMS_TOKEN'),
            // Alternativa OAuth2 (client_credentials), para cuando CESSA consiga este tipo
            // de credenciales -- si 'token' está seteado, tiene prioridad y esto no se usa.
            'client_id' => env('TIGO_SMS_CLIENT_ID'),
            'client_secret' => env('TIGO_SMS_CLIENT_SECRET'),
            // Remitente del SMS, máx. 11 caracteres alfanuméricos según el doc de Tigo.
            'sender' => env('TIGO_SMS_SENDER', 'CESSA'),
        ],
    ],

    'demo_actualizar_datos' => [
        // Token compartido para los endpoints protegidos (registros, actualizar-token-sms) de
        // la página oculta de administración de la herramienta aparte. Ya no se pega a mano en
        // el frontend -- se obtiene con el login de usuario/contraseña de abajo (ver
        // DemoActualizarDatosController::login()). Generar uno largo y random igual, no una
        // palabra fácil de adivinar -- es la única protección real de esos endpoints.
        'admin_token' => env('DEMO_ACTUALIZAR_DATOS_ADMIN_TOKEN'),
        // Credenciales del login de esa página oculta -- mismo criterio que
        // services.sip.callback_username/password (secreto compartido simple, no la tabla
        // users ni el guard 'web' del panel real).
        'admin_username' => env('DEMO_ADMIN_USERNAME'),
        'admin_password' => env('DEMO_ADMIN_PASSWORD'),
    ],

];
