<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class BnbQrProviderTest extends PaymentsTestCase
{
    private function bnb()
    {
        return app(PaymentProviderRegistry::class)->get('bnb');
    }

    public function test_genera_qr_mapeando_la_respuesta_y_manda_el_alias_en_additional_data(): void
    {
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlGenerar() => Http::response(['success' => true, 'id' => 212039, 'qr' => self::QR_PNG_BASE64, 'message' => null]),
        ]));

        $result = $this->bnb()->generate(new QrPaymentRequest(
            alias: 'CESSA-WEB-ABCD',
            amount: 150.5,
            currency: 'BOB',
            description: 'Pago CESSA',
            expiresAt: Carbon::parse('2026-09-25 10:00:00'),
            callbackUrl: 'http://ignored',
            singleUse: true,
        ));

        $this->assertSame('212039', $result->providerQrId);
        $this->assertSame(self::QR_PNG_BASE64, $result->qrImageBase64);
        $this->assertSame('BNB', $result->destinationBank);
        $this->assertSame('1', $result->destinationAccount);
        $this->assertSame('', $result->providerTransactionId);

        Http::assertSent(function (Request $r) {
            if ($r->url() !== $this->urlGenerar()) {
                return false;
            }

            return $r->hasHeader('Authorization', 'Bearer '.self::TOKEN)
                && $r['additionalData'] === 'CESSA-WEB-ABCD'
                && (float) $r['amount'] === 150.5
                && $r['currency'] === 'BOB'
                && $r['gloss'] === 'Pago CESSA'
                && $r['singleUse'] === true
                && $r['expirationDate'] === '2026-09-25'
                && $r['destinationAccountId'] === '1';
        });
    }

    public function test_genera_pide_el_token_una_sola_vez_y_lo_reusa(): void
    {
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlGenerar() => Http::response(['success' => true, 'id' => 1, 'qr' => self::QR_PNG_BASE64]),
        ]));

        $req = fn () => new QrPaymentRequest('A'.uniqid(), 10, 'BOB', 'x', now()->addMinutes(5), 'http://x', true);
        $this->bnb()->generate($req());
        $this->bnb()->generate($req());

        Http::assertSentCount(3); // 1 token + 2 generaciones
    }

    public function test_auth_fallida_lanza_excepcion(): void
    {
        Http::fake([$this->urlToken() => Http::response(['success' => false, 'message' => 'Credenciales inválidas'], 200)]);

        $this->expectException(QrPaymentException::class);
        $this->expectExceptionMessageMatches('/Credenciales inválidas/');

        $this->bnb()->generate(new QrPaymentRequest('A', 10, 'BOB', 'x', now()->addMinutes(5), 'http://x', true));
    }

    public function test_generar_con_success_false_lanza_excepcion_con_el_message(): void
    {
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlGenerar() => Http::response(['success' => false, 'message' => 'Monto inválido'], 200),
        ]));

        $this->expectException(QrPaymentException::class);
        $this->expectExceptionMessageMatches('/Monto inválido/');

        $this->bnb()->generate(new QrPaymentRequest('A', 10, 'BOB', 'x', now()->addMinutes(5), 'http://x', true));
    }

    public function test_status_traduce_alias_a_qr_id_y_mapea_estados(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '55501']);

        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 55501, 'statusId' => 2, 'voucherId' => '3JAO702813']),
        ]));

        $estado = $this->bnb()->status($recibo->alias);

        $this->assertSame(PaymentStatus::Pagado, $estado->status);
        $this->assertSame('3JAO702813', $estado->providerOrderNumber);
        Http::assertSent(fn (Request $r) => $r->url() === $this->urlEstado() && (string) $r['qrId'] === '55501');
    }

    public function test_status_mapea_no_usado_expirado_y_error(): void
    {
        $recibo = $this->crearReciboBnb();

        Http::fake($this->fakeToken());
        Http::fakeSequence($this->urlEstado())
            ->push(['success' => true, 'id' => 1, 'statusId' => 1, 'voucherId' => '0'])
            ->push(['success' => true, 'id' => 1, 'statusId' => 3, 'voucherId' => '0'])
            ->push(['success' => true, 'id' => 1, 'statusId' => 4, 'voucherId' => '0']);

        $this->assertSame(PaymentStatus::Pendiente, $this->bnb()->status($recibo->alias)->status);
        $this->assertSame(PaymentStatus::Expirado, $this->bnb()->status($recibo->alias)->status);
        $this->assertSame(PaymentStatus::Error, $this->bnb()->status($recibo->alias)->status);
    }

    public function test_disable_cancela_por_qr_id(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '77701']);

        Http::fake(array_merge($this->fakeToken(), [
            $this->urlCancelar() => Http::response(['success' => true, 'message' => null]),
        ]));

        $this->bnb()->disable($recibo->alias);

        Http::assertSent(fn (Request $r) => $r->url() === $this->urlCancelar() && (string) $r['qrId'] === '77701');
    }

    public function test_disable_sin_provider_qr_id_guardado_lanza_excepcion_sin_llamar_al_banco(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => null]);

        Http::fake($this->fakeToken());

        $this->expectException(QrPaymentException::class);

        try {
            $this->bnb()->disable($recibo->alias);
        } finally {
            Http::assertNotSent(fn (Request $r) => $r->url() === $this->urlCancelar());
        }
    }
}
