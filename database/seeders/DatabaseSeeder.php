<?php

namespace Database\Seeders;

use App\Models\CessaRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User for Filament (/rcadmin)
        User::updateOrCreate(
            ['email' => 'admin@cessa.com.bo'],
            [
                'name' => 'Administrador CESSA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Atención al Cliente: único rol con acceso a Solicitudes (fotos de C.I., factura, etc.)
        User::updateOrCreate(
            ['email' => 'atencion@cessa.com.bo'],
            [
                'name' => 'Atención al Cliente CESSA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => User::ROLE_CUSTOMER_SERVICE,
            ]
        );

        // Sistemas: control absoluto -- único rol con acceso a Reportes y a gestión de Usuarios.
        User::updateOrCreate(
            ['email' => 'sistemas@cessa.com.bo'],
            [
                'name' => 'Sistemas CESSA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => User::ROLE_SYSTEM,
            ]
        );

        // Initial Sample Request
        CessaRequest::updateOrCreate(
            ['document_number' => '6543210 CB'],
            [
                'fullname' => 'JUAN PEREZ MAMANI',
                'email' => 'juan.perez@ejemplo.com',
                'user_type' => 'RESIDENCIAL',
                'service_type' => 'NUEVO_SUMINISTRO',
                'mobile_phone' => '71234567',
                'phone' => '6451200',
                'address' => 'Av. Jaime Mendoza N° 120',
                'zone' => 'Barrio San José',
                'reference' => 'Frente a la plaza principal',
                'status' => 'PENDIENTE',
                'send_date' => now(),
                'observation' => 'Solicitud recibida. Pendiente de inspección técnica en terreno.',
                'created_by' => 'PORTAL_WEB',
            ]
        );
    }
}
