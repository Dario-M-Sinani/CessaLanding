<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-blue-950/70 backdrop-blur-sm" @click.self="close">
      <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <button
          type="button"
          aria-label="Cerrar"
          class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-blue-950 flex items-center justify-center shadow-md transition-colors"
          @click="close"
        >
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>

        <div class="p-6 pt-8 space-y-4 text-center">
          <span class="inline-block px-3 py-1 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Pago QR CESSA
          </span>

          <!-- Cargando -->
          <div v-if="estado === 'cargando'" class="py-10 space-y-3">
            <svg class="w-8 h-8 mx-auto animate-spin text-blue-900" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
            <p class="text-sm text-gray-600">{{ simular ? 'Simulando el pago...' : 'Generando tu código QR...' }}</p>
          </div>

          <!-- Error -->
          <div v-else-if="estado === 'error'" class="py-6 space-y-4">
            <p class="text-sm text-red-700">{{ mensajeError }}</p>
            <button
              type="button"
              @click="generar()"
              class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition-colors"
            >
              Reintentar
            </button>
          </div>

          <!-- Pagado -->
          <div v-else-if="estado === 'pagado'" class="py-8 space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
              <svg class="w-8 h-8" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-base font-bold text-emerald-800">¡Pago recibido!</p>
            <p class="text-xs text-gray-500">Bs. {{ monto }}</p>

            <ul class="flex flex-col w-fit gap-1.5 text-left text-xs mx-auto">
              <li class="flex items-center gap-2 text-emerald-800 font-semibold">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                Pago recibido
              </li>
              <li v-if="comprobanteUrl" class="flex items-center gap-2 text-emerald-800 font-semibold">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                Facturado
              </li>
              <li v-else-if="!facturaDemorada" class="flex items-center gap-2 text-gray-500">
                <svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25" /><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
                Emitiendo tu factura…
              </li>
            </ul>

            <a
              v-if="comprobanteUrl"
              :href="comprobanteUrl"
              target="_blank"
              rel="noopener"
              class="flex w-fit mx-auto items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold rounded-lg text-xs transition-colors"
            >
              <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
              Descargar comprobante
            </a>
            <p v-else-if="facturaDemorada" class="text-[11px] text-gray-500 max-w-xs mx-auto">
              Tu pago quedó registrado. Tu factura va a aparecer en esta misma página, en
              "Tus últimas facturas", apenas esté lista.
            </p>
            <p v-else class="text-[11px] text-gray-500 max-w-xs mx-auto">
              Tu pago ya está registrado: podés cerrar esta ventana. La factura aparece en
              segundos acá o, si cerrás, en "Tus últimas facturas" de esta página.
            </p>
          </div>

          <!-- QR listo -->
          <div v-else-if="estado === 'qr'" class="space-y-4">
            <img :src="qrImageUrl" alt="Código QR de pago" class="w-72 h-72 sm:w-80 sm:h-80 mx-auto rounded-xl border border-gray-200 bg-white" />
            <div>
              <span class="block text-[11px] text-gray-500 uppercase font-semibold">Monto a Pagar<span v-if="periodo"> · {{ periodo }}</span></span>
              <span class="text-2xl font-black text-blue-900 font-mono">Bs. {{ monto }}</span>
            </div>

            <a
              :href="qrImageUrl"
              download="qr-pago-cessa.png"
              class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-900 font-bold rounded-lg text-xs transition-colors"
            >
              <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
              Descargar QR
            </a>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1">
              <p class="text-xs font-bold text-amber-900">
                Tenés que pagar en menos de 5 minutos
              </p>
              <p class="text-lg font-black font-mono" :class="segundosRestantes <= 60 ? 'text-red-600' : 'text-amber-700'">
                {{ tiempoRestanteTexto }}
              </p>
              <p class="text-[11px] text-amber-700">Si se vence, generá un código nuevo.</p>
            </div>

            <p class="text-xs text-gray-500 inline-flex items-center gap-1.5 justify-center">
              <svg class="w-3.5 h-3.5 animate-pulse text-amber-500" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="6" /></svg>
              Esperando confirmación de pago...
            </p>

            <!-- Solo pruebas: PAGOS_SIMULACION_HABILITADA + logueado en el panel como SYSTEM -->
            <div v-if="puedeSimular" class="pt-2 border-t border-dashed border-gray-200 space-y-1">
              <button
                type="button"
                :disabled="simulando"
                @click="simularPago"
                class="px-4 py-2 bg-fuchsia-600 hover:bg-fuchsia-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs transition-colors"
              >
                {{ simulando ? 'Simulando…' : '🧪 Simular pago (prueba)' }}
              </button>
              <p v-if="errorSimulacion" class="text-[11px] text-red-600">{{ errorSimulacion }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  nroCliente: String,
  zona: [String, Number],
  manzano: [String, Number],
  correlativo: [String, Number],
  cantidadMeses: Number,
  // Banco puntual opcional ('sip_bisa' | 'bnb'). Normalmente NO se pasa: el usuario genera un
  // solo QR y el backend alterna el banco por detrás. Se deja por si el staff/panel alguna vez
  // necesita forzar un banco.
  banco: { type: String, default: null },
  // Solo pruebas: muestra el botón "Simular pago" (el backend vuelve a verificar el permiso).
  puedeSimular: { type: Boolean, default: false },
  // Solo pruebas: en vez de generar un QR, crea el pago ya simulado (sin banco) y muestra lo
  // que viene después (¡Pago recibido! → factura → comprobante).
  simular: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const estado = ref('cargando'); // cargando | qr | pagado | error
const bancoElegido = ref(props.banco);
const mensajeError = ref('');
const qrImageUrl = ref('');
const monto = ref('');
const periodo = ref('');
const comprobanteUrl = ref('');
const facturaDemorada = ref(false);
let alias = null;
let pollTimer = null;
let intentosComprobante = 0;
// Cuántos sondeos de comprobante hacer después de "pagado" antes de dejar de insistir en
// silencio (3s * 100 = 5min) -- la factura real se genera en background (ver
// RegistrarFacturacionCobranzas, corre cada minuto y reintenta si falla), no bloquea el
// "¡Pago recibido!" de arriba.
const MAX_INTENTOS_COMPROBANTE = 100;

// Cuenta regresiva propia (el vencimiento real de 5 min lo hace cumplir el backend, ver
// PagoQrController -- esto es solo la UI para que el cliente vea cuánto tiempo le queda).
const segundosRestantes = ref(0);
let expiryTimer = null;

const tiempoRestanteTexto = computed(() => {
  const s = Math.max(0, segundosRestantes.value);
  const mm = Math.floor(s / 60);
  const ss = s % 60;
  return `${mm}:${String(ss).padStart(2, '0')}`;
});

const iniciarCuentaRegresiva = (expiresAtIso) => {
  if (expiryTimer) clearInterval(expiryTimer);
  const expiraEn = new Date(expiresAtIso).getTime();

  const tick = () => {
    segundosRestantes.value = Math.max(0, Math.round((expiraEn - Date.now()) / 1000));
  };

  tick();
  expiryTimer = setInterval(tick, 1000);
};

const getCookie = (name) => {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
};

// Genera el QR. Normalmente sin banco: el backend alterna BISA/BNB por detrás. Si viene un banco
// puntual (prop), se envía para forzarlo.
const generar = async (bancoKey = null) => {
  if (bancoKey) bancoElegido.value = bancoKey;

  estado.value = 'cargando';

  try {
    const response = await fetch('/api/pagos/generar-qr', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
      body: JSON.stringify({
        nro_cliente: props.nroCliente,
        zona: props.zona,
        manzano: props.manzano,
        correlativo: props.correlativo,
        cantidad_meses: props.cantidadMeses,
        banco: bancoElegido.value,
        simular: props.simular || undefined,
      }),
    });

    const json = await response.json();

    if (!response.ok) {
      mensajeError.value = response.status === 429
        ? 'Hiciste demasiados intentos seguidos. Esperá un minuto y volvé a intentar.'
        : json.message || 'No se pudo generar el QR de pago.';
      estado.value = 'error';
      return;
    }

    alias = json.alias;
    monto.value = json.monto;
    periodo.value = json.periodo || '';

    // Pago simulado: ya viene Pagado, se salta el QR y el sondeo sigue con la factura.
    if (json.status === 'pagado') {
      estado.value = 'pagado';
      startPolling();
      return;
    }

    qrImageUrl.value = json.qr_image_url;
    estado.value = 'qr';
    iniciarCuentaRegresiva(json.expires_at);
    startPolling();
  } catch (e) {
    mensajeError.value = 'No se pudo conectar con el servidor. Verifica tu conexión e intenta de nuevo.';
    estado.value = 'error';
  }
};

// Solo pruebas: marca el QR como pagado sin pagar. El sondeo de abajo detecta el cambio y sigue
// el circuito real (¡Pago recibido! → factura → comprobante).
const simulando = ref(false);
const errorSimulacion = ref('');

const simularPago = async () => {
  if (!alias || simulando.value) return;
  simulando.value = true;
  errorSimulacion.value = '';

  try {
    const response = await fetch(`/api/pagos/simular-pago/${alias}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
    });

    if (!response.ok) {
      const json = await response.json().catch(() => ({}));
      errorSimulacion.value = json.message || `No se pudo simular (HTTP ${response.status}).`;
    }
  } catch (e) {
    errorSimulacion.value = 'No se pudo conectar con el servidor.';
  } finally {
    simulando.value = false;
  }
};

const startPolling = () => {
  pollTimer = setInterval(async () => {
    if (!alias) return;

    try {
      const response = await fetch(`/api/pagos/estado-qr/${alias}`, {
        headers: { 'Accept': 'application/json' },
      });
      const json = await response.json();

      // "pagado", "facturado" y "error_facturacion" son las 3 caras del mismo hecho para el
      // cliente: el dinero ya entró. La diferencia entre ellos es solo si el comprobante ya
      // está listo para descargar o no -- nunca se le muestra un error por algo que va a
      // resolverse en background (ver RegistrarFacturacionCobranzas).
      if (['pagado', 'facturado', 'error_facturacion'].includes(json.status)) {
        monto.value = json.amount;
        estado.value = 'pagado';

        if (json.comprobante_url) {
          comprobanteUrl.value = json.comprobante_url;
          clearInterval(pollTimer);
        } else if (++intentosComprobante >= MAX_INTENTOS_COMPROBANTE) {
          // Se deja de insistir, pero el pago ya quedó confirmado igual -- no es un error.
          facturaDemorada.value = true;
          clearInterval(pollTimer);
        }
      } else if (['expirado', 'inhabilitado', 'error'].includes(json.status)) {
        mensajeError.value = 'El código QR ya no está disponible. Generá uno nuevo.';
        estado.value = 'error';
        clearInterval(pollTimer);
      }
    } catch (e) {
      // Silencioso: un fallo de red puntual en el sondeo no debe romper la vista del QR, se reintenta en el próximo tick.
    }
  }, 3000);
};

// Avisa si se pagó, para que la página recargue la deuda y la lista de últimos pagos.
const close = () => {
  emit('close', estado.value === 'pagado');
};

// El QR se genera apenas abre el modal; el backend elige el banco (alternando) salvo que se
// pase uno puntual por prop.
onMounted(() => generar(props.banco));

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer);
  if (expiryTimer) clearInterval(expiryTimer);
});
</script>
