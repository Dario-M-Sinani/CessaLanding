<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstalacionMaterialesController extends Controller
{
    /**
     * Catálogo completo de materiales y especificaciones por tipo de instalación.
     */
    public static function getCatalogo(): array
    {
        return [
            'monofasica' => [
                'id' => 'monofasica',
                'titulo' => 'Instalación Monofásica (220V)',
                'subtitulo' => 'Suministro Domiciliario y Comercial Básico',
                'tipo_servicio' => 'Monofásico 220V',
                'descripcion' => 'Acometida estándar para viviendas familiares o locales comerciales de baja potencia. Requiere puesto de medición sobre la línea municipal.',
                'variantes' => [
                    'con_baston' => [
                        'label' => '1 Piso (Con Bastón)',
                        'imagen' => '/storage/nuevas_instalaciones/monofasico-con-baston2.png',
                        'croquis' => '/storage/nuevas_instalaciones/1-piso-con-baston.png',
                        'descripcion_variante' => 'Para inmuebles de una planta. Requiere bastón galvanizado para garantizar la altura reglamentaria de la acometida aérea.',
                    ],
                    'sin_baston' => [
                        'label' => '2 Pisos o más (Sin Bastón)',
                        'imagen' => '/storage/nuevas_instalaciones/monofasico-sin-baston2.png',
                        'croquis' => '/storage/nuevas_instalaciones/2-pisos-sin-baston.png',
                        'descripcion_variante' => 'Para inmuebles de dos o más plantas donde la altura de fachada permite el ingreso directo de la acometida.',
                    ],
                ],
                'materiales' => [
                    ['cantidad' => '1 pza.', 'item' => 'Caja metálica de acero de 1 mm de espesor para medidor monofásico.', 'corto' => 'Caja metálica p/ medidor (1 mm)'],
                    ['cantidad' => '8 mts.', 'item' => 'Alambre aislado de cobre Nro. 10 AWG (o cable según carga solicitada).', 'corto' => 'Alambre Cu N° 10 AWG'],
                    ['cantidad' => '1 pza.', 'item' => 'Interruptor termomagnético bipolar (amperaje según carga calculada).', 'corto' => 'Térmico bipolar (según carga)'],
                    ['cantidad' => '1 pza.', 'item' => 'Tubo plástico conduit (tubo PVC) de 3/4 de pulgada de diámetro.', 'corto' => 'Tubo PVC conduit 3/4"'],
                    ['cantidad' => '1 pza.', 'item' => 'Bastón de cañería galvanizada de 3/4" de diámetro (para 1 piso).', 'corto' => 'Bastón galvanizado 3/4"', 'solo_con_baston' => true],
                    ['cantidad' => '1 pza.', 'item' => 'Medidor monofásico certificado (provisto por CESSA tras aprobación).', 'corto' => 'Medidor (lo provee CESSA)'],
                    ['cantidad' => '1 pza.', 'item' => 'Conector de varilla de tierra (grapa de bronce de alta presión).', 'corto' => 'Conector de varilla (bronce)'],
                    ['cantidad' => '1 pza.', 'item' => 'Varilla de tierra de cobre de 5/8" de diámetro (mínimo 0.80 m de longitud).', 'corto' => 'Varilla de tierra Cu 5/8" × 0,80 m'],
                    ['cantidad' => '6 pzas.', 'item' => 'Tornillos de encarne de 3/4" con sus respectivos tarugos.', 'corto' => 'Tornillos encarne 3/4" + tarugos'],
                    ['cantidad' => '1 pza.', 'item' => 'Rack de 2 vías con aisladores de porcelana para anclaje de acometida.', 'corto' => 'Rack de 2 vías con aisladores'],
                ],
                'requisitos_tecnicos' => [
                    'Ubicación: En el frontis del predio, sobre la línea de barda o fachada, con la cara frontal hacia la calle.',
                    'Altura máxima de empotrado de la caja: 1.40 metros desde el nivel de acera.',
                    'Distancia máxima entre el puesto de medición y la red eléctrica de CESSA: 34 metros (Norma Boliviana NB 777).',
                    'Solo se permite una acometida aérea o subterránea por predio.',
                ],
                'requisitos_documentales' => [
                    'Fotocopia de Cédula de Identidad vigente o pasaporte.',
                    'Derecho propietario, folio real, o constancia de posesión/tenencia del inmueble.',
                    'No tener deudas pendientes por suministro eléctrico con CESSA.',
                ],
            ],
            'trifasica' => [
                'id' => 'trifasica',
                'titulo' => 'Instalación Trifásica (380V / 220V)',
                'subtitulo' => 'Suministro Comercial, Talleres y Edificaciones Mayores',
                'tipo_servicio' => 'Trifásico 380V/220V',
                'descripcion' => 'Para actividades comerciales, talleres, bombas de agua o viviendas con alta demanda de potencia y equipos trifásicos.',
                'variantes' => [
                    'con_baston' => [
                        'label' => '1 Piso (Con Bastón)',
                        'imagen' => '/storage/nuevas_instalaciones/trifasico-con-baston3.png',
                        'croquis' => '/storage/nuevas_instalaciones/1-piso-con-baston.png',
                        'descripcion_variante' => 'Para predios de una sola planta con bastón galvanizado para acometida trifásica.',
                    ],
                    'sin_baston' => [
                        'label' => '2 Pisos o más (Sin Bastón)',
                        'imagen' => '/storage/nuevas_instalaciones/trifasico-sin-baston3.png',
                        'croquis' => '/storage/nuevas_instalaciones/2-pisos-sin-baston.png',
                        'descripcion_variante' => 'Ingreso de acometida trifásica directo a fachada en inmuebles altos.',
                    ],
                ],
                'materiales' => [
                    ['cantidad' => '1 pza.', 'item' => 'Caja metálica de acero de 1 mm de espesor para medidor trifásico.', 'corto' => 'Caja metálica p/ medidor trifásico'],
                    ['cantidad' => '8 mts.', 'item' => 'Alambre aislado de cobre Nro. 10 AWG (o calibre superior según potencia declarada).', 'corto' => 'Alambre Cu N° 10 AWG o mayor'],
                    ['cantidad' => '1 pza.', 'item' => 'Interruptor termomagnético tripolar (amperaje según carga trifásica).', 'corto' => 'Térmico tripolar (según carga)'],
                    ['cantidad' => '1 pza.', 'item' => 'Tubo plástico conduit (tubo PVC) de 3/4 de pulgada de diámetro.', 'corto' => 'Tubo PVC conduit 3/4"'],
                    ['cantidad' => '1 pza.', 'item' => 'Bastón de cañería galvanizada de 3/4" o 1" de diámetro (si aplica 1 piso).', 'corto' => 'Bastón galvanizado 3/4" o 1"', 'solo_con_baston' => true],
                    ['cantidad' => '1 pza.', 'item' => 'Medidor trifásico certificado (homologado por CESSA).', 'corto' => 'Medidor trifásico homologado'],
                    ['cantidad' => '1 pza.', 'item' => 'Conector de varilla de tierra de bronce.', 'corto' => 'Conector de varilla (bronce)'],
                    ['cantidad' => '1 pza.', 'item' => 'Varilla de tierra de cobre de 5/8" de diámetro (mínimo 0.80 m de longitud).', 'corto' => 'Varilla de tierra Cu 5/8" × 0,80 m'],
                    ['cantidad' => '6 pzas.', 'item' => 'Tornillos de encarne de 3/4" con tarugos de fijación.', 'corto' => 'Tornillos encarne 3/4" + tarugos'],
                    ['cantidad' => '1 pza.', 'item' => 'Rack de 4 vías con aisladores de porcelana para acometida trifásica.', 'corto' => 'Rack de 4 vías con aisladores'],
                ],
                'requisitos_tecnicos' => [
                    'Puesto de medición ubicado en la fachada frontal con acceso libre para lectura técnica.',
                    'Altura máxima de la caja: 1.40 metros sobre la acera.',
                    'Distancia máxima a la red de baja tensión de CESSA: 34 metros (NB 777).',
                    'Aterramiento obligatorio independiente con varilla de cobre certificada.',
                ],
                'requisitos_documentales' => [
                    'Fotocopia de C.I. vigente del solicitante o representante legal.',
                    'Poder notarial y NIT (en caso de empresas o personas jurídicas).',
                    'Documentación de derecho propietario o contrato de alquiler/arrendamiento.',
                    'Ausencia de facturas pendientes con CESSA.',
                ],
            ],
            'tablero-centralizador' => [
                'id' => 'tablero-centralizador',
                'titulo' => 'Tablero Centralizador de Medidores',
                'subtitulo' => 'Para 3 o más medidores (Edificios y Multifamiliares)',
                'tipo_servicio' => 'Centralizador Modular Múltiple',
                'descripcion' => 'Normativa CESSA: Cuando en un mismo predio se instala un tercer medidor, es obligatorio el uso de un tablero centralizador en vez de cajas individuales.',
                'variantes' => [
                    'con_baston' => [
                        'label' => 'Con Bastón Aéreo',
                        'imagen' => '/storage/nuevas_instalaciones/tablero-centralizador-con-baston.png',
                        'croquis' => '/storage/nuevas_instalaciones/tablero-centralizador-con-baston.png',
                        'descripcion_variante' => 'Para acometidas aéreas con bastón centralizador de 1 pulgada.',
                    ],
                    'sin_baston' => [
                        'label' => 'Sin Bastón (Acometida Directa / Subterránea)',
                        'imagen' => '/storage/nuevas_instalaciones/tablero-centralizador-sin-baston.png',
                        'croquis' => '/storage/nuevas_instalaciones/tablero-centralizador-sin-baston.png',
                        'descripcion_variante' => 'Para acometidas subterráneas o empotradas en muro de fachada.',
                    ],
                ],
                'materiales' => [
                    ['cantidad' => '1 pza.', 'item' => 'Tablero centralizador metálico homologado según cantidad de suministros.', 'corto' => 'Tablero centralizador homologado'],
                    ['cantidad' => '8 mts.', 'item' => 'Alambre aislado de cobre Nro. 10 AWG por cada medidor a instalar.', 'corto' => 'Alambre Cu N° 10 c/ medidor'],
                    ['cantidad' => '1 pza.', 'item' => 'Interruptor termomagnético por cada medidor + 1 interruptor general de corte.', 'corto' => 'Térmico por medidor + 1 general'],
                    ['cantidad' => '1 pza.', 'item' => 'Tubo plástico conduit (PVC) de 3/4" o 1" según cantidad de cables.', 'corto' => 'Tubo PVC conduit 3/4" o 1"'],
                    ['cantidad' => '1 pza.', 'item' => 'Bastón de cañería galvanizada de 1" de diámetro (si aplica acometida aérea).', 'corto' => 'Bastón galvanizado 1"', 'solo_con_baston' => true],
                    ['cantidad' => 'N pzas.', 'item' => 'Medidores certificados por cada suministro solicitado.', 'corto' => 'Medidores (1 por suministro)'],
                    ['cantidad' => '4 pzas.', 'item' => 'Conectores de varilla de tierra.', 'corto' => 'Conectores de varilla'],
                    ['cantidad' => '1 pza.', 'item' => 'Varilla de aterramiento de cobre de 5/8" (mínimo 0.80 m).', 'corto' => 'Varilla de tierra Cu 5/8" × 0,80 m'],
                    ['cantidad' => '6 pzas.', 'item' => 'Tornillos de encarne de 3/4" para anclaje del gabinete.', 'corto' => 'Tornillos encarne 3/4"'],
                    ['cantidad' => '1 pza.', 'item' => 'Conector grapa tipo Alcoa / Rack de 4 vías según acometida.', 'corto' => 'Grapa Alcoa / rack de 4 vías'],
                ],
                'requisitos_tecnicos' => [
                    'Gabinete centralizador ubicado en planta baja o ingreso principal con libre acceso.',
                    'Espacio modular con compartimiento independiente para barras generales y disyuntores.',
                    'Altura reglamentaria: centro del tablero a 1.40 metros del piso.',
                    'Distancia máxima a la red de CESSA: 34 metros según norma NB 777.',
                ],
                'requisitos_documentales' => [
                    'Cédula de Identidad de los propietarios o copropietarios.',
                    'Plano hidrosanitario y eléctrico visado (si es edificio o condominio).',
                    'Documentación que acredite derecho propietario o división de departamentos/locales.',
                    'Estado de cuenta al día sin deudas en CESSA.',
                ],
            ],
        ];
    }

    /**
     * Materiales de una instalación para la variante elegida: sin bastón no lleva los ítems
     * marcados 'solo_con_baston' (antes la lista y la etiqueta los mostraban siempre).
     */
    public static function materialesPara(array $instalacion, string $variante): array
    {
        return array_values(array_filter(
            $instalacion['materiales'],
            fn (array $m) => $variante === 'con_baston' || empty($m['solo_con_baston']),
        ));
    }

    /**
     * Muestra la vista imprimible en Blanco y Negro de la lista de materiales.
     */
    public function imprimir(Request $request, string $tipo): View
    {
        $catalogo = self::getCatalogo();

        if (!isset($catalogo[$tipo])) {
            throw new NotFoundHttpException('Tipo de instalación no encontrado.');
        }

        $instalacion = $catalogo[$tipo];
        $variante = $request->query('variante');
        if (!isset($instalacion['variantes'][$variante])) {
            $variante = 'con_baston';
        }

        return view('instalaciones.imprimir_materiales', [
            'instalacion' => $instalacion,
            'variante' => $variante,
            'materiales' => self::materialesPara($instalacion, $variante),
            'catalogo' => $catalogo,
        ]);
    }

    /**
     * Devuelve el catálogo en formato JSON para el frontend / API.
     */
    public function data(): JsonResponse
    {
        return response()->json(self::getCatalogo());
    }
}
