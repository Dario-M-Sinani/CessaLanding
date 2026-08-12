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
            <p class="text-sm text-gray-600">Generando tu código QR...</p>
          </div>

          <!-- Error -->
          <div v-else-if="estado === 'error'" class="py-6 space-y-4">
            <p class="text-sm text-red-700">{{ mensajeError }}</p>
            <button
              type="button"
              @click="generar"
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
});

const emit = defineEmits(['close']);

const estado = ref('cargando'); // cargando | qr | pagado | error
const mensajeError = ref('');
const qrImageUrl = ref('');
const monto = ref('');
const periodo = ref('');
let alias = null;
let pollTimer = null;

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

const generar = async () => {
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
      }),
    });

    const json = await response.json();

    if (!response.ok) {
      mensajeError.value = json.message || 'No se pudo generar el QR de pago.';
      estado.value = 'error';
      return;
    }

    alias = json.alias;
    qrImageUrl.value = json.qr_image_url;
    monto.value = json.monto;
    periodo.value = json.periodo || '';
    estado.value = 'qr';
    iniciarCuentaRegresiva(json.expires_at);
    startPolling();
  } catch (e) {
    mensajeError.value = 'No se pudo conectar con el servidor. Verifica tu conexión e intenta de nuevo.';
    estado.value = 'error';
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

      if (json.status === 'pagado') {
        monto.value = json.amount;
        estado.value = 'pagado';
        clearInterval(pollTimer);
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

const close = () => {
  emit('close');
};

onMounted(generar);

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer);
  if (expiryTimer) clearInterval(expiryTimer);
});
</script>
