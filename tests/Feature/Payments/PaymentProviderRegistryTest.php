<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\PaymentProviderRegistry;

class PaymentProviderRegistryTest extends PaymentsTestCase
{
    private function registry(): PaymentProviderRegistry
    {
        return app(PaymentProviderRegistry::class);
    }

    public function test_rotate_next_alterna_empezando_por_bisa(): void
    {
        $r = $this->registry();

        // Primera vez (sin banco previo) -> BISA.
        $this->assertSame('sip_bisa', $r->rotateNext(null));
        // Después de BISA -> BNB.
        $this->assertSame('bnb', $r->rotateNext('sip_bisa'));
        // Después de BNB -> BISA.
        $this->assertSame('sip_bisa', $r->rotateNext('bnb'));
        // Un valor desconocido cae en BISA (no rompe).
        $this->assertSame('sip_bisa', $r->rotateNext('otro'));
    }

    public function test_label_y_selectable_keys(): void
    {
        $r = $this->registry();

        $this->assertSame('Banco BISA', $r->label('sip_bisa'));
        $this->assertSame('Banco Nacional de Bolivia (BNB)', $r->label('bnb'));
        $this->assertSame('desconocido', $r->label('desconocido'));
        $this->assertEqualsCanonicalizing(['sip_bisa', 'bnb'], $r->selectableKeys());
    }
}
