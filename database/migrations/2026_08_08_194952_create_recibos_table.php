<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();

            // Qué banco/pasarela generó este QR (ver App\Services\Payments\Providers) -- no se
            // asume que siempre va a ser SIP/BISA, para poder sumar más bancos después.
            $table->string('provider', 30);

            // Identificador único que nosotros elegimos y le mandamos al proveedor -- es lo que
            // usamos para buscar el recibo cuando llega el callback de confirmación.
            $table->string('alias', 50)->unique();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('glosa', 30);
            $table->string('status', 20)->default('pendiente');
            $table->date('expires_at');

            // Datos que devuelve el proveedor al generar el QR.
            $table->string('qr_image_path')->nullable();
            $table->string('provider_qr_id')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('destination_bank')->nullable();
            $table->string('destination_account')->nullable();

            // Datos que llegan recién en el callback, cuando se confirma el pago.
            $table->string('provider_order_number')->nullable();
            $table->string('payer_account')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_document')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('callback_payload')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
