<?php

namespace Tests\Feature;

use App\Models\News;
use Tests\TestCase;

/**
 * Pase de cessa.com.bo del sitio CakePHP a Laravel: las URLs viejas (Google, Facebook, favoritos)
 * redirigen con 301 a la página equivalente en vez de dar 404. SQLite en memoria solo con la
 * tabla de noticias (no usa RefreshDatabase: no todas las migraciones corren en SQLite).
 */
class LegacyRedirectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_07_24_000002_create_news_table.php',
            'database/migrations/2026_07_27_135005_add_image_url_to_news_table.php',
            'database/migrations/2026_08_03_000001_add_images_to_news_table.php',
        ]]);
    }

    public function test_urls_fijas_del_sitio_anterior_redirigen_con_301(): void
    {
        foreach ([
            '/importante/cortes-programados' => '/informacion/cortes-programados',
            '/servicios/consulta-tu-deuda' => '/consulta-deuda',
            '/pages/scheduled-power-outages' => '/informacion/cortes-programados',
            '/solicitudes/nueva-conexion' => '/nueva-conexion',
            '/consumidor/preguntas-frecuentes' => '/informacion/faqs',
            '/la-compania/mision-y-vision' => '/la-compania/mision-vision',
            '/personal/personal-externo-autorizado-para-cortes-y-reconexiones-de-cessa' => '/personal/cortes-reconexiones',
            '/documentos/convocatorias' => '/informacion/documentos',
            '/pago-qr' => '/consulta-deuda',
        ] as $viejo => $nuevo) {
            $this->get($viejo)->assertStatus(301)->assertRedirect($nuevo);
        }
    }

    public function test_contenidos_genericos_de_consumidor_y_la_compania_van_a_contenido(): void
    {
        $this->get('/consumidor/derechos-y-obligaciones')->assertStatus(301)->assertRedirect('/contenido/derechos-y-obligaciones');
        $this->get('/la-compania/algo-nuevo')->assertStatus(301)->assertRedirect('/contenido/algo-nuevo');
    }

    public function test_noticia_por_alias_viejo_redirige_a_su_id_y_alias_inexistente_da_404(): void
    {
        $news = News::create([
            'title' => 'Comunicado AETN',
            'alias' => 'comunicado-aetn',
            'summary' => 'resumen',
            'full_text' => 'texto',
            'published' => 'S',
        ]);

        $this->get('/noticias/comunicado-aetn')->assertStatus(301)->assertRedirect("/noticias/{$news->id}");
        $this->get('/noticias/no-existe')->assertNotFound();
    }
}
