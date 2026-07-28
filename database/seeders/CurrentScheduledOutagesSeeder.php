<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga los cortes programados vigentes reales, extraídos de
 * https://cessa.com.bo/importante/cortes-programados el 2026-07-25.
 * Preserva los IDs originales del sitio real para poder re-sincronizar sin duplicar.
 * Despublica los 27 registros de 2022 que vinieron del dump legacy (datos de ejemplo, ya vencidos).
 */
class CurrentScheduledOutagesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('scheduled_outages')
            ->whereBetween('id', [1, 27])
            ->update(['published' => 'N']);

        $outages = [
            2958 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN DE LA COMUNIDAD PISILI MUNICIPIO DE TARABUCO',
                'location' => "Localidad de Pisil, Jucuyo Mayu, Sipuco, Collacamani, Jatun Charicana, Pisili Churicanita, Kollpa Pampa, Rurritayo\nINSTITUCIONES AFECTADAS: Unidades educativas, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-28',
                'start_time' => '09:00',
                'finish_time' => '11:00',
            ],
            2959 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO EN RED DE BAJA TENSIÓN CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN EL BARRIO AMAZONAS LA GUARDIA',
                'location' => "Urbanización Lomar, barrio Amazonas, La Guardia, Los Ángeles\nINSTITUCIONES AFECTADAS: GAMS alumbrado público, salón multifuncional La Amazona unidades educativas, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-28',
                'start_time' => '09:00',
                'finish_time' => '11:00',
            ],
            2960 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO EN RED DE BAJA TENSIÓN CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN LA CALLE GREGORIO REYNOLDS',
                'location' => "Av. German Mendoza, calle M. A. Padilla, Av. Jaime Mendoza, calle G. Reynolds, Lemoine\nINSTITUCIONES AFECTADAS: GAMS alumbrado público, EMAS, unidades educativas, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-29',
                'start_time' => '09:00',
                'finish_time' => '12:00',
            ],
            2961 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTEINIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN DE LA LOCALIDAD ZAMORA DEL MUNICIPIO DE VILLA SERRANO',
                'location' => "Localidad de Zamora y otras adyacentes\nINSTITUCIONES AFECTADAS: Unidades educativas, centros de salud, antenas de telecomunicación, hospitales, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-29',
                'start_time' => '09:00',
                'finish_time' => '13:00',
            ],
            2962 => [
                'reason' => 'PARA REALIZAR MANTENIMIENTO PREVENTIVO DEL PUESTO DE TRANSFORMACIÓN EN LA AV. MARCELO QUIROGA SANTA CRUZ',
                'location' => "Av. Marcelo Quiroga Santa Cruz, calle San Rafael, Daniel Camacho, Pasaje San Rafael, Alto Mesa Verde, J.A. Tonelli\nINSTITUCIONES AFECTADAS: CAJERO BANCO ECONÓMICO, BNB, IMPORT EXPORT LAS LOMAS, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-31',
                'start_time' => '07:00',
                'finish_time' => '09:00',
            ],
            2963 => [
                'reason' => 'MANTENIMIENTO PREVENTIVO CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN RED DE BAJA TESNIÓN, CALLE DANIEL SANCHEZ BUSTAMANTE',
                'location' => "Calle Victorino Vega, Daniel Sánchez Bustamante, O. Campero, Pando, Pasaje M. Aldunate\nINSTITUCIONES AFECTADAS: COTES, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-31',
                'start_time' => '07:00',
                'finish_time' => '10:00',
            ],
            2964 => [
                'reason' => 'PARA REALIZAR MANTENIMIENTO PREVENTIVO DEL PUESTO DE TRANSFORMACIÓN EN LA CALLE GREGORIO PACHECO',
                'location' => "Calle Gregorio Pacheco, Marzana, R. Calvo Arana, Adolfo Vilar, Av. M. Peredo, Tomás Frías, J. Gantier, bajo Delicias\nINSTITUCIONES AFECTADAS: FUNDACIÓN PRO MUJER I.F.D., GAMS, BNB, BANCO BISA, IMPORT EXPOERT LAS LOMAS, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-31',
                'start_time' => '10:00',
                'finish_time' => '11:00',
            ],
            2965 => [
                'reason' => 'PARA REALIZAR MANTENIMIENTO PREVENTIVO DEL PUESTO DE TRANSFORMACIÓN EN LA ZONA ALDEAS SOS',
                'location' => "Alto Tucsupaya, barrio El Niño, El Rollo, barrio Unidad, Estados Unidos, calle Melitón Sanjinés, Felix Morales\nINSTITUCIONES AFECTADAS: COTES, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-31',
                'start_time' => '12:00',
                'finish_time' => '13:00',
            ],
            2966 => [
                'reason' => 'PARA REALIZAR MEJORAS CON EL MANTENIMIENTO EN LA RED DE MEDIA TENSIÓN TRAMO BOHORQUEZ-BARTOLO DEL MUNICIPIO DE MONTEAGUDO',
                'location' => "Localidad de Cruce Heredia, Bohórquez, Bartolo, antena ENTEL Bartolo y localidades aledañas\nINSTITUCIONES AFECTADAS: Centros de salud, talleres, hospitales, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-07-31',
                'start_time' => '12:00',
                'finish_time' => '14:00',
            ],
            2967 => [
                'reason' => 'MODIFICACIÓN DE LÍNEA DE MEDIA TENSIÓN EN DERIVACIÓN AL HOSPITAL CRISTO DE LAS AMÉRICAS EN LA AV. JAPÓN ESQUINA PITANTORA',
                'location' => "Av. Japón y calles adyacentes\nINSTITUCIONES AFECTADAS: HOSPITAL CRISTO DE LAS AMÉRICAS, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-08-02',
                'start_time' => '06:00',
                'finish_time' => '08:00',
            ],
            2968 => [
                'reason' => 'MODIFICACIÓN DE LÍNEA DE MEDIA TENSIÓN EN DERIVACIÓN AL PSICOPEDAGÓGICO DE NIÑOS EN LA AV. JAPON ESQUINA MACHA',
                'location' => "Av. Japón y calles adyacentes\nINSTITUCIONES AFECTADAS: SEDES PSICOPEDAGÓGICO, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-08-02',
                'start_time' => '06:00',
                'finish_time' => '08:00',
            ],
            2969 => [
                'reason' => 'MODIFICACIÓN DE LÍNEA DE MEDIA TENSIÓN EN DERIVACIÓN AL T-121 CALLE MACHA ESQUNA AV. JAPÓN',
                'location' => "Cotagaita, O. Molina, A. Vargas, Pazña, Macha, El Villar, Pitantora, Pasaje Carrillo y calles adyacentes\nINSTITUCIONES AFECTADAS: TELECEL, GAMS en la calle Cotagaita y Pazña, centros de salud, antenas de telecomunicación, tiendas comerciales, instituciones públicas y privadas en las zonas mencionadas",
                'execution_date' => '2026-08-02',
                'start_time' => '06:00',
                'finish_time' => '09:00',
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
