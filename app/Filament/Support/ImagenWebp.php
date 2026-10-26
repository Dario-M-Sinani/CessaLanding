<?php

namespace App\Filament\Support;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * jpg/jpeg/png subidos se convierten a WebP (más liviano, misma calidad visible). Lo usan el
 * Gestor de Archivos y el picker "Elegir del Gestor de Archivos" de los formularios.
 *
 * Recibe un archivo YA guardado en el disco (así la carpeta destino existe siempre: antes el
 * picker escribía el .webp directo con imagewebp() a una carpeta que en prod no existía --
 * "comunicados-aetn" -- y la subida de imágenes de Comunicados AETN daba 500, 2026-10-26).
 * Si la conversión no se puede hacer (GD sin WebP, imagen corrupta, demasiado grande para la
 * memoria del hosting) se queda el original en vez de romper la subida.
 */
class ImagenWebp
{
    // Píxeles máximos a decodificar con GD (~4 bytes por píxel en memoria): 25 MP ≈ 100 MB.
    private const MAX_PIXELES = 25_000_000;

    /** Devuelve la ruta final (la .webp si se convirtió, la original si no). */
    public static function convertir(Filesystem $disk, string $storedPath): string
    {
        $ext = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));

        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true) || ! function_exists('imagewebp')) {
            return $storedPath;
        }

        $absoluto = $disk->path($storedPath);
        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $storedPath);

        try {
            $tamano = getimagesize($absoluto);

            if ($tamano === false || $tamano[0] * $tamano[1] > self::MAX_PIXELES) {
                return $storedPath;
            }

            $source = $ext === 'png' ? imagecreatefrompng($absoluto) : imagecreatefromjpeg($absoluto);

            if ($source === false) {
                return $storedPath;
            }

            if ($ext === 'png') {
                imagepalettetotruecolor($source);
                imagealphablending($source, true);
                imagesavealpha($source, true);
            }

            $ok = imagewebp($source, $disk->path($webpPath), 82);
            imagedestroy($source);

            if (! $ok) {
                return $storedPath;
            }
        } catch (\Throwable $e) {
            report($e);

            return $storedPath;
        }

        $disk->delete($storedPath);

        return $webpPath;
    }
}
