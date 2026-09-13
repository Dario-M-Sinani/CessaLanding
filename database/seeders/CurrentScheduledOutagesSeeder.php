<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga los cortes programados vigentes reales, extraídos de
 * https://cessa.com.bo/importante/cortes-programados el 2026-09-12.
 * Preserva los IDs originales del sitio real para poder re-sincronizar sin duplicar.
 * Despublica el lote anterior (dump legacy 1-27 y el snapshot del 2026-07-25, ids 2958-2969),
 * ya vencido.
 */
class CurrentScheduledOutagesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('scheduled_outages')
            ->whereBetween('id', [1, 27])
            ->update(['published' => 'N']);

        DB::table('scheduled_outages')
            ->whereBetween('id', [2958, 2969])
            ->update(['published' => 'N']);

        $outages = [
            3058 => [
                'reason' => 'CAMBIO DE POSTES DE MEDIA TENSIÓN EN AV. MARCELO QUIROGA SANTA CRUZ SECTOR SALIDA A COCHABAMBA',
                'location' => "Av. Marcelo Q. Santa Cruz sector salida a Cochabamba, zonas y calles aledañas\nINSTITUCIONES AFECTADAS: INVERSIONES SUCRE S.A., YESERÍA CAROLINA, YESERÍA CRUZ, CONCRETEC frente a Fancesa, centros de salud, unidades educativas, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-13',
                'start_time' => '06:00',
                'finish_time' => '12:00',
            ],
            3060 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO CON ALEJAMIENTO DE LÍNEA EN RED DE BAJA TENSIÓN, BARRIO AGUAS BLANCAS',
                'location' => "Barrio Aguas Blancas, Villa Marlecita, 12 de Septiembre, Brasil\nINSTITUCIONES AFECTADAS: GAMS alumbrado público, GAMS salón multifuncional barrio Aguas Blancas, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '06:00',
                'finish_time' => '08:00',
            ],
            3061 => [
                'reason' => 'CAMBIO DE POSTES DE MEDIA TENSIÓN EN AV. MARCELO QUIROGA SANTA CRUZ SECTOR SALIDA A COCHABAMBA',
                'location' => "Av. Marcelo Q. Santa Cruz sector salida a Cochabamba y zonas aledañas\nINSTITUCIONES AFECTADAS: Cooperativa de Transporte Nacional e Internacional, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '06:00',
                'finish_time' => '13:00',
            ],
            3062 => [
                'reason' => 'REEMPLAZO DE POSTES Y ESTRUCTURAS DE MEDIA TENSIÓN EN COMUNIDAD EL CHACO',
                'location' => "El Chaco, Tambo Atajo, Sawinto, Villca Pampa, Sokok Pujyu y zonas aledañas\nINSTITUCIONES AFECTADAS: Salón comunal Sokoj Pujyu, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '08:00',
                'finish_time' => '12:00',
            ],
            3063 => [
                'reason' => 'CONVERSIÓN DE MONOFÁSICO A TRIFÁSICO DE LÍNEA DE BAJA TENSIÓN EN COMUNIDAD EL CHACO',
                'location' => "El Chaco y zonas aledañas\nINSTITUCIONES AFECTADAS: GAMS en el Chaco, parroquia el Chaco, GAMS en Carama, bombas de agua, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '08:00',
                'finish_time' => '12:00',
            ],
            3064 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE BAJA TENSIÓN DE LA COMUNIDAD PAMPAS ARRIBA DEL MUNICIPIO DE TOMINA',
                'location' => "Comunidad de Pampas Arriba y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, hospitales, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '09:00',
                'finish_time' => '12:00',
            ],
            3065 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN RED DE BAJA TENSIÓN, CALLE BATALLÓN COLORADOS ZONA SAN MATÍAS',
                'location' => "Calle Batallón Colorados, calle J. de la Mar, Antofagasta, Av. J. Mendoza y zonas aledañas\nINSTITUCIONES AFECTADAS: GAMS alumbrado público, Telefonía de celular de Bolivia, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '09:00',
                'finish_time' => '13:00',
            ],
            3066 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN COMUNIDAD CAÑON LARGO DEL MUNICIPIO DE VILLA VACA GUZMÁN',
                'location' => "Localidad Figueroa, Sauce Mayu, El Tunal, Pincal, Cañón Largo, localidades y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, hospitales, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-15',
                'start_time' => '10:00',
                'finish_time' => '12:00',
            ],
            3067 => [
                'reason' => 'CAMBIO DE POSTES EN MEDIA TENSIÓN EN COMUNIDAD EL CHACO',
                'location' => "Mojotoro, Ermitas, Limón Pampa, La Palma, Sacramento, Sotani, Chapi Molino, Chacarilla, Mosojllajta, San Francisco de Quiñal, El Chaco, Sockos Pujllu, Pampa Uma, Tambo Atajo, Chaquito, La Angostura, La Compuerta, Vilcalata, Chuqui Chuqui, Cancha Corral, El Tunal, Mojtulo, Naranjos, Surima, Quiquijana, La Culata, Talahuanca, Taoial, Valle Hermoso, Maram Pampa, Bella Vista, El Melonar, Monteroyuj, Ovejerias, Sevencani, Pucapampa, Hacoyu, Montepampa, Orituyuj, Camos, Imilla Huañusca, El Morro, Sausal, Carapari, Caraparicito, Puente arce, Catariri, Mataral, Puente Loma, Toro Pampa, Eje Pampa y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, hospitales, antenas de telecomunicación, tiendas comerciales, bombas de agua, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-16',
                'start_time' => '07:00',
                'finish_time' => '09:30',
            ],
            3068 => [
                'reason' => 'AMPLIACIÓN Y MODIFICACIÓN DE LÍNEAS DE BAJA TENSIÓN EN VILLA EL CARMEN SECTOR TOTACOA',
                'location' => "Totacoa, Villa El Carmen y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, bombas de agua, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-16',
                'start_time' => '08:00',
                'finish_time' => '12:00',
            ],
            3069 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN DE LA COMUNIDAD SAN MAURO DEL MUNICIPIO DE PADILLA',
                'location' => "Comunidad San Mauro y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-16',
                'start_time' => '09:00',
                'finish_time' => '12:00',
            ],
            3070 => [
                'reason' => 'REUBICACIÓN E INSTALACIÓN DE DOS TRANSFORMADORES CON RECONFIGURACIÓN DE LÍNEAS DE BAJA TENSIÓN EN COMUNIDAD EL CHACO',
                'location' => "El Chaco y zonas aledañas\nINSTITUCIONES AFECTADAS: GAMS en el Chaco, parroquia el Chaco, GAMS en Carama, bombas de agua, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-16',
                'start_time' => '09:30',
                'finish_time' => '13:00',
            ],
            3071 => [
                'reason' => 'CAMBIO DE POSTES EN MEDIA TENSIÓN EN AV. MARCELO QUIROGA SANTA CRUZ SECTOR SALIDA A COCHABAMBA',
                'location' => "Av. Marcelo Q. Santa Cruz sector salida a Cochabamba y zonas aledañas\nINSTITUCIONES AFECTADAS: Cooperativa de Transporte Nacional e Internacional, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-17',
                'start_time' => '06:00',
                'finish_time' => '13:00',
            ],
            3072 => [
                'reason' => 'RETIRO DE POSTES Y MEJORAS EN LÍNEA DE BAJA TENSIÓN EN AV. M. Q. SANTA CRUZ SECTOR BOSQUECILLO',
                'location' => "Av. Marcelo Q. Santa Cruz sector bosquecillo y zonas aledañas\nINSTITUCIONES AFECTADAS: EMAV SUCRE, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-17',
                'start_time' => '07:00',
                'finish_time' => '13:00',
            ],
            3073 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN RED DE MEDIA TENSIÓN COMUNIDAD TINTEROS',
                'location' => "Comunidad Tinteros y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-17',
                'start_time' => '09:00',
                'finish_time' => '13:00',
            ],
            3074 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN RED DE MEDIA TENSIÓN LOCALIDAD DE SAN JUAN DE CACHIMAYU',
                'location' => "Localidad de San Juan de Cachimayu, Duraznillo, localidad Guzmán y zonas aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-18',
                'start_time' => '08:00',
                'finish_time' => '12:00',
            ],
            3075 => [
                'reason' => 'PARA REALIZAR MANTENIMIENTO PREVENTIVO DEL PUESTO DE TRANSFORMACIÓN EN BARRIO NORTE',
                'location' => "Barrio Norte, Luis Espinal, Ckopa Ckasa, Vida Nueva, Lechuguillas, Villa Pagador, Villa Flores, alto Lechuguillas, Mesa Verde, calle San Rafael, Gaspar de la Cueva y zonas aledañas\nINSTITUCIONES AFECTADAS: GAMS, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-18',
                'start_time' => '09:00',
                'finish_time' => '10:00',
            ],
            3076 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE BAJA TENSIÓN EN LA COMUNIDAD CHILLA APACHETA DEL MUNICIPIO DE ICLA',
                'location' => "Comunidad Chilla Apacheta y zonas aledañas\nINSTITUCIONES AFECTADAS: Instituciones públicas y privadas, postas sanitarias, centros de salud, hospitales, unidades educativas, tiendas comerciales, talleres en las zonas mencionadas",
                'execution_date' => '2026-09-18',
                'start_time' => '09:00',
                'finish_time' => '12:00',
            ],
            3077 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN EN LA COMUNIDAD HUAYRA HUASI DEL MUNICIPIO DE PADILLA',
                'location' => "Localidad Wayra Huasi, Tola Urqo, Tola Urqo zona a escuela, Wayra Huasi zona escuela y zonas aledañas\nINSTITUCIONES AFECTADAS: Instituciones públicas y privadas, postas sanitarias, centros de salud, hospitales, unidades educativas, escuela Thola Orqo, escuela Wayra Huasi dependiente del GAM de Padilla, tiendas comerciales, talleres en las zonas mencionadas",
                'execution_date' => '2026-09-18',
                'start_time' => '09:00',
                'finish_time' => '13:00',
            ],
            3078 => [
                'reason' => 'CAMBIO DE POSTES DE MEDIA TENSIÓN EN AV. MARCELO QUIROGA SANTA CRUZ SECTOR PARQUE CRETÁCICO',
                'location' => "Av. Marcelo Q. Santa Cruz sector salida a Cochabamba, zonas y calles aledañas\nINSTITUCIONES AFECTADAS: PARQUE CRETÁCICO de zona Cal Orcko, PLANTA DE OXÍGENO IMPA, INPA SRL, centros de salud, unidades educativas, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-20',
                'start_time' => '06:00',
                'finish_time' => '14:00',
            ],
            3079 => [
                'reason' => 'CAMBIO DE POSTES DE MEDIA TENSIÓN EN AV. MARCELO QUIROGA SANTA CRUZ SECTOR SALIDA A COCHABAMBA',
                'location' => "Av. Marcelo Q. Santa Cruz sector salida a Cochabamba, zonas y calles aledañas\nINSTITUCIONES AFECTADAS: INVERSIONES SUCRE S.A., YESERÍA CAROLINA, YESERÍA CRUZ, CONCRETEC frente a Fancesa, centros de salud, unidades educativas, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-09-21',
                'start_time' => '06:00',
                'finish_time' => '14:00',
            ],
        ];

        foreach ($outages as $id => $data) {
            DB::table('scheduled_outages')->updateOrInsert(['id' => $id], [
                'reason' => $data['reason'],
                'location' => $data['location'],
                'execution_date' => $data['execution_date'],
                'start_time' => $data['start_time'],
                'finish_time' => $data['finish_time'],
                'published' => 'S',
                'created_by' => 'SYNC_CESSA_COM_BO',
                'modified_by' => 'SYNC_CESSA_COM_BO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
