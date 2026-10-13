<?php

namespace Tests\Feature;

use App\Http\Controllers\InstalacionMaterialesController;
use Tests\TestCase;

/**
 * Etiqueta imprimible de materiales (/instalaciones/imprimir/{tipo}), rehecha 2026-10-13: una
 * sola etiqueta de hasta 10 materiales con nombre corto, y respeta la variante (sin bastón no
 * lleva el bastón).
 */
class EtiquetaMaterialesTest extends TestCase
{
    public function test_cada_material_tiene_nombre_corto_para_la_etiqueta(): void
    {
        foreach (InstalacionMaterialesController::getCatalogo() as $tipo => $instalacion) {
            foreach ($instalacion['materiales'] as $m) {
                $this->assertNotEmpty($m['corto'] ?? null, "{$tipo}: falta 'corto' en «{$m['item']}»");
                $this->assertLessThanOrEqual(36, mb_strlen($m['corto']), "{$tipo}: «{$m['corto']}» no entra en la columna");
            }
        }
    }

    public function test_con_baston_lista_el_baston_en_una_sola_etiqueta(): void
    {
        $this->get('/instalaciones/imprimir/monofasica?variante=con_baston')
            ->assertOk()
            ->assertSee('Bastón galvanizado 3/4&quot;', false)
            ->assertSee('1 Piso (Con Bastón)')
            ->assertSee('10 materiales')
            ->assertDontSee('Etiqueta 1 de');
    }

    public function test_sin_baston_no_lista_el_baston(): void
    {
        foreach (array_keys(InstalacionMaterialesController::getCatalogo()) as $tipo) {
            $this->get("/instalaciones/imprimir/{$tipo}?variante=sin_baston")
                ->assertOk()
                ->assertDontSee('Bastón galvanizado')
                ->assertDontSee('Bastón de cañería')
                ->assertSee('9 materiales');
        }
    }

    public function test_variante_invalida_cae_en_con_baston_y_tipo_invalido_da_404(): void
    {
        $this->get('/instalaciones/imprimir/trifasica?variante=<script>')
            ->assertOk()
            ->assertSee('1 Piso (Con Bastón)')
            ->assertDontSee('<script>', false);

        $this->get('/instalaciones/imprimir/no-existe')->assertNotFound();
    }
}
