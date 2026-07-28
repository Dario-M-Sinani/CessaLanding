<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class LaCompaniaController extends Controller
{
    protected function contentByAlias(string $alias): ?array
    {
        $content = Content::where('alias', $alias)->where('published', 'S')->first();

        return $content ? [
            'title' => $content->title,
            'summary' => $content->summary,
            'full_text' => $content->full_text,
        ] : null;
    }

    public function quienesSomos(): Response
    {
        return Inertia::render('LaCompania/QuienesSomos', [
            'content' => $this->contentByAlias('quienes-somos'),
        ]);
    }

    public function historia(): Response
    {
        return Inertia::render('LaCompania/Historia', [
            'content' => $this->contentByAlias('historia'),
        ]);
    }

    public function misionVision(): Response
    {
        return Inertia::render('LaCompania/MisionVision', [
            'content' => $this->contentByAlias('mision-y-vision'),
        ]);
    }

    public function estructura(): Response
    {
        return Inertia::render('LaCompania/Estructura', [
            'content' => $this->contentByAlias('estructura-organizacional'),
        ]);
    }

    public function rrhh(): Response
    {
        return Inertia::render('LaCompania/Rrhh', [
            'content' => $this->contentByAlias('recursos-humanos'),
        ]);
    }

    public function contacto(): Response
    {
        return Inertia::render('LaCompania/Contacto');
    }

    public function contactoStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:180',
            'email' => 'required|email|max:180',
            'message' => 'required|string|max:2000',
        ]);

        try {
            Mail::raw(
                "Nuevo mensaje de contacto recibido desde el sitio web:\n\n" .
                "Nombre: {$validated['name']}\n" .
                "Correo: {$validated['email']}\n\n" .
                "Mensaje:\n{$validated['message']}",
                function ($mail) use ($validated) {
                    $mail->to(config('services.contact.notify_email'))
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject('Ha recibido un nuevo mensaje desde la Página Web de la Compañía Eléctrica Sucre S.A.');
                }
            );

            Mail::raw(
                "Estimado(a) {$validated['name']},\n\n" .
                "Gracias por contactarse con la Compañía Eléctrica Sucre S.A. Hemos recibido su mensaje y nos pondremos en contacto a la brevedad.\n\n" .
                "Atentamente,\nCompañía Eléctrica Sucre S.A.",
                function ($mail) use ($validated) {
                    $mail->to($validated['email'])
                        ->subject('Gracias por contactarse con la Compañía Eléctrica Sucre S.A.');
                }
            );

            return redirect()->back()->with('success', 'Gracias por contactarte con CESSA. Te responderemos a la brevedad.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo enviar tu mensaje. Intenta nuevamente más tarde.');
        }
    }
}
