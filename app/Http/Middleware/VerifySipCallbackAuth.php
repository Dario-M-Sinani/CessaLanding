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
