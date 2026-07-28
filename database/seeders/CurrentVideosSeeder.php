<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza los 3 videos del dump legacy (desactualizado) por los 38 videos reales
 * extraídos de https://cessa.com.bo/galeria/videos (2 páginas) el 2026-07-26.
 * El sitio real repite algunos IDs de YouTube en más de una tarjeta (distinto título/
 * descripción) y tiene una URL de Facebook y una URL de youtube studio (privada, no
 * pública) — se preservan tal cual para reflejar fielmente el sitio real; el componente
 * Vue (Galeria/Index.vue) ya degrada a un link simple cuando no puede armar un embed.
 */
class CurrentVideosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('videos')->truncate();

        $videos = [
            ['title' => 'CONSEJOS PARA EVITAR ACCIDENTES EN LAS CONSTRUCCIONES', 'description' => 'Tenga cuidado cuando manipule fierro, madera, cables, cuando esta construyendo una vivienda y esta cerca de las redes eléctricas', 'url' => 'https://www.youtube.com/watch?v=F83T8K1pHpA', 'position' => 1],
            ['title' => 'CUIDADO CON SUS MASCOTA', 'description' => 'Permita que el personal de CESSA tome la lectura de su medidor y evítese molestias', 'url' => 'https://youtu.be/UujulmhyD4c', 'position' => 2],
            ['title' => 'CESSA EN INVIERNO', 'description' => 'Recomendaciones en invierno', 'url' => 'https://youtu.be/n45HPaA_m5M', 'position' => 3],
            ['title' => 'PAGO PUNTUAL', 'description' => 'RECOMENDACIONES PAGO PUNTUAL DE SUS FACTURAS', 'url' => 'https://www.youtube.com/watch?v=aXpzlyLbP6o', 'position' => 4],
            ['title' => 'Chat bot, para recibir una mejor atencion', 'description' => 'Mejoramos para darle una servicio a su alcance', 'url' => 'https://www.facebook.com/share/v/17yjMK3x4b/', 'position' => 5],
            ['title' => 'TUTORIAL PAGO QR', 'description' => 'TUTORIAL PARA PAGO QR DEL SERVICIO DE ENERGÍA ELÉCTRICA', 'url' => 'https://youtu.be/Bav5ftOc2tc', 'position' => 6],
            ['title' => 'CESSA EN LA FEXPO SUCRE 2025', 'description' => 'CESSA AGRADECE SU VISITA', 'url' => 'https://youtu.be/QwduF87mkvs', 'position' => 7],
            ['title' => 'GENERACIÓN DISTRIBUIDA', 'description' => 'GENERACIÓN DISTRIBUIDA', 'url' => 'https://www.youtube.com/watch?v=uKj9A4jKx2Y', 'position' => 8],
            ['title' => 'GENERACIÓN DISTRIBUIDA EN QUECHUA', 'description' => 'GENERACIÓN DISTRIBUIDA', 'url' => 'https://youtu.be/HEux5mmRBio', 'position' => 9],
            ['title' => 'CESSA LES DESEA UNA FELIZ  NAVIDAD Y UN BENDECIDO 2025', 'description' => 'SALUDO DE NAVIDAD', 'url' => 'https://youtu.be/3QtzovophSg', 'position' => 10],
            ['title' => 'TUTORIAL COMO REALIZAR UNA INSTALACION NUEVA PARA TABLERO CENTRALIZADOR', 'description' => 'TUTORIAL COMO REALIZAR UNA INSTALACION NUEVA PARA TABLERO CENTRALIZADOR', 'url' => 'https://www.youtube.com/watch?v=3vLERQYWhzM', 'position' => 11],
            ['title' => 'PESE AL MAL TIEMPO SEGUIMOS TRABAJANDO POR USTED', 'description' => 'El terreno de desliza y nuestros postes caen', 'url' => 'https://www.youtube.com/watch?v=apvRGR2vcek', 'position' => 12],
            ['title' => 'CESSA capacita a su personal del área urbana y rural', 'description' => 'Trabajos con tensión, en 24.9 kV', 'url' => 'https://youtu.be/mJewbOBQXtY', 'position' => 13],
            ['title' => 'CESSA en la capacitación de Trabajos con Tensión de 24.9 kV', 'description' => 'capacitación con la empresa Amper y Circuitos de Colombia', 'url' => 'https://youtu.be/VXqi8KUblaM', 'position' => 14],
            ['title' => 'Fuertes lluvias y crecida de ríos en Chuquisaca', 'description' => 'Personal de CESSA trabaja por  usted y su familia, pese a la lluvia, seguimos trabajando por usted. TOME SUS PREVISIONES', 'url' => 'https://youtu.be/4lyUaR7Kxw0', 'position' => 15],
            ['title' => 'PREVENIR ACCIDENTES', 'description' => 'Como prevenir accidentes cuando se construya una casa', 'url' => 'https://youtu.be/lrDT7l-xlLA', 'position' => 16],
            ['title' => 'CESSA informa', 'description' => 'Personal del área rural  capacitado', 'url' => 'https://www.youtube.com/watch?v=ya2D8IzYbqY', 'position' => 17],
            ['title' => 'CESSA INFORMA  que realizamos', 'description' => 'trabajos de prevención de incidentes eléctricos, mantenimientos y seguridad en la continuidad del servicio', 'url' => 'https://youtu.be/wq8xjpYMaho', 'position' => 18],
            ['title' => 'PAGUE SUS FACTURAS DE LUZ POR QR', 'description' => 'Le enseñamos paso a paso cómo generar código QR para que pueda pagar sus facturas de luz, desde su celular', 'url' => 'https://youtu.be/Bav5ftOc2tc?si=CBYoU55X59ja-Ug-', 'position' => 19],
            ['title' => 'accidentes', 'description' => 'evitar accidentes es responsabilidad de todos', 'url' => 'https://www.youtube.com/watch?v=P2XHo-mZwHk', 'position' => 20],
            ['title' => 'Información al cliente', 'description' => 'Información al cliente', 'url' => 'https://www.youtube.com/watch?v=Ca1beLnYHOU', 'position' => 21],
            ['title' => 'Puntos de Cobranza', 'description' => 'Puntos de Cobranza', 'url' => 'https://www.youtube.com/watch?v=Y7MAuThqoB4', 'position' => 22],
            ['title' => 'TUTORIAL MEDIDOR DE LUZ TRIFÁSICO', 'description' => 'TUTORIAL COMO REALIZAR UNA INSTALACIÓN DE MEDIDOR DE LUZ TRIFÁSICO- CESSA INFORMA', 'url' => 'https://www.youtube.com/watch?v=gn2MERXLemM', 'position' => 23],
            ['title' => 'FACTURACION ELECTRONICA', 'description' => 'TUTORIAL REGISTRO DE DATOS FACTURACION ELECTRONICA', 'url' => 'https://www.youtube.com/watch?v=8HRotZUECB8', 'position' => 24],
            ['title' => 'TUTORIAL COMO REALIZAR UNA INSTALACIÓN DE LUZ MONOFASICO', 'description' => 'TUTORIAL COMO REALIZAR UNA INSTALACIÓN DE LUZ MONOFASICO', 'url' => 'https://studio.youtube.com/video/VOY9TnBi15M/edit', 'position' => 25],
            ['title' => 'LEY 1886 \"DESCUENTO TERCERA EDAD\"', 'description' => 'LEY 1886 \"DESCUENTO TERCERA EDAD\"', 'url' => 'https://www.youtube.com/watch?v=2ttzDjfWhwg', 'position' => 26],
            ['title' => 'CINCO REGLAS DE ORO PARA TRABAJOS ELECTRICOS', 'description' => 'CINCO REGLAS DE ORO PARA TRABAJOS ELECTRICOS', 'url' => 'https://www.youtube.com/watch?v=KmlitE_RJKA', 'position' => 27],
            ['title' => 'CONSEJOS PARA EVITAR ACCIDENTES EN LA ÉPOCA DE NAVIDAD', 'description' => 'Cuidado con recargar los toma corrientes', 'url' => 'https://youtu.be/1WcQk8qK0Qw', 'position' => 28],
            ['title' => 'CESSA participó en la entrada de navidad GAMS 2024', 'description' => 'ENTRADA DE NAVIDAD', 'url' => 'https://youtube.com/shorts/igse90LmGDQ', 'position' => 29],
            ['title' => 'CESSA  en la entrada de navidad 2024', 'description' => 'CESSA en navidad', 'url' => 'https://youtu.be/kPUz_joYGqY', 'position' => 30],
            ['title' => 'prevenir accidentes', 'description' => 'accidentes', 'url' => 'https://youtu.be/lv5eWVkhrJo', 'position' => 31],
            ['title' => 'USO EFICIENTE PARA NIÑOS', 'description' => 'desenchufar los electrodomésticos que no este utilizando', 'url' => 'https://youtu.be/KiqU_X7p8yA', 'position' => 32],
            ['title' => 'CESSA INFORMA PREVENIR ROBOS', 'description' => 'Denuncie a quienes están robando lo NUESTRO', 'url' => 'https://youtu.be/BJY78apdFwY', 'position' => 33],
            ['title' => 'PAGUE PUNTUALMENTE SUS FACTURAS DE LUZ  Y EVITE CORTES', 'description' => 'EVITE CORTES POR FALTA DE PAGO', 'url' => 'https://www.youtube.com/watch?v=CMXOpYWoeok', 'position' => 34],
            ['title' => 'Prevención de Accidentes', 'description' => 'Prevención de Accidentes', 'url' => 'https://www.youtube.com/watch?v=cPJ_B4kSFjE', 'position' => 35],
            ['title' => 'NO SE APROXIME', 'description' => 'CESSA LE RECOMIENDA NO APROXIMARSE A LOS CABLES CAÍDOS', 'url' => 'https://youtu.be/BkhMHYx24Zc', 'position' => 36],
            ['title' => 'TUTORIAL FUGAS DE ENERGIA', 'description' => 'TUTORIAL PARA IDENTIFICAR FUGAS DE ENERGIA EN EL DOMICILIO', 'url' => 'https://www.youtube.com/watch?v=WD7jcKLErZ8', 'position' => 37],
            ['title' => 'PREVENGA ACCIDENTES CUANDO PODE LOS ÁRBOLES', 'description' => 'CESSA LE RECOMIENDA NO PODAR ÁRBOLES QUE ESTAN CERCA DE LAS LÍNEAS DE ENERGÍA ELÉCTRICA...ES PELIGROSO', 'url' => 'https://www.youtube.com/watch?v=P2XHo-mZwHk', 'position' => 38],
        ];

        foreach ($videos as $v) {
            DB::table('videos')->insert([
                'title' => $v['title'],
                'description' => $v['description'],
                'url' => $v['url'],
                'position' => $v['position'],
                'published' => 'S',
                'created_by' => 'SYNC_CESSA_COM_BO',
                'modified_by' => 'SYNC_CESSA_COM_BO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
