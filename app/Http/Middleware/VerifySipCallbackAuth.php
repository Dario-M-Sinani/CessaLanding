<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica el Basic Auth que SIP manda al llamar a nuestro callback (ver §3.1.1 "SEGURIDAD"
 * de la especificación) contra las credenciales fijas en config/services.php -- no depende de
 * la tabla users ni del guard 'web', es un secreto compartido solo con SIP.
 */
class VerifySipCallbackAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Reenvío desde el gateway (cobranza-cessa): SIP llama al callback único del gateway y
        // este reenvía los alias CESSA-* acá con la misma API key que ya compartimos con él.
        $apiKey = config('services.cobranzas.gateway_api_key');
        $providedKey = $request->header('X-Api-Key');
        if (filled($apiKey) && is_string($providedKey) && hash_equals($apiKey, $providedKey)) {
            return $next($request);
        }

        $username = config('services.sip.callback_username');
        $password = config('services.sip.callback_password');

        $providedUser = $request->getUser();
        $providedPassword = $request->getPassword();

        $valid = filled($username) && filled($password)
            && is_string($providedUser) && is_string($providedPassword)
            && hash_equals($username, $providedUser)
            && hash_equals($password, $providedPassword);

        if (! $valid) {
            return response()->json([
                'codigo' => '9999',
                'mensaje' => 'No autorizado',
            ], 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
