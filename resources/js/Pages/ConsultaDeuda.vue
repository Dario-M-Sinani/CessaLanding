<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Consulta Comercial en Línea
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Consulta Deuda</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            Verifica el estado de tus facturas ingresando tu número de abonado y tu N° de Cuenta.
          </p>
        </div>

        <!-- Search Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          <form @submit.prevent="submitSearch" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Número de Cliente</label>
              <input
                v-model="form.nro_cliente"
                type="text"
                inputmode="numeric"
                placeholder="Ejemplo: 123456"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono"
                required
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">N° de Cuenta</label>
              <input
                :value="form.nro_cuenta"
                @input="onNroCuentaInput"
                type="text"
                inputmode="numeric"
                placeholder="Ejemplo: 00-000-00000"
                maxlength="12"
                required
                class="w-full sm:w-72 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono"
              />
              <p v-if="formatError" class="text-[11px] text-red-600 mt-1.5">{{ formatError }}</p>
              <p v-else class="text-[11px] text-gray-500 mt-1.5">
                Formato Zona-Manzano-Correlativo, tal como aparece en la parte superior de tu factura.
              </p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
              <button
                type="submit"
                :disabled="loading"
                class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
              >
                Consultar
              </button>

              <button
                type="button"
                @click="mostrarAyuda = !mostrarAyuda"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-3 sm:py-2 text-blue-900 font-bold text-xs uppercase tracking-wider hover:underline"
              >
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                ¿Dónde encuentro estos datos?
              </button>
            </div>
          </form>

          <!-- Help Panel -->
          <div v-if="mostrarAyuda" class="p-4 sm:p-6 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
            <p class="text-sm text-blue-950">
              Encuentras tu <span class="font-bold">N° de Cliente</span> y tu <span class="font-bold">N° de Cuenta</span> en la parte superior de tu factura o aviso de cobro, como se muestra a continuación:
            </p>
            <img
              src="/img/ayuda/consulta-deuda.png"
              alt="Ejemplo de factura CESSA señalando la ubicación del número de cliente y del número de cuenta"
              class="w-full max-w-xl mx-auto rounded-lg border border-blue-200 shadow-sm"
            />
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center space-x-3">
          <span>{{ error }}</span>
        </div>

        <!-- Result Box -->
        <div v-if="resultado" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-200 gap-4">
            <div>
              <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Cliente Registrado</span>
              <h2 class="text-2xl font-black text-gray-900 mt-1">{{ resultado.nombre }}</h2>
              <p class="text-sm sm:text-base text-gray-700 mt-1.5">N° Cuenta: <span class="text-gray-900 font-mono font-bold">{{ resultado.nro_cuenta }}</span></p>
              <p class="text-sm sm:text-base text-gray-700 mt-1">{{ resultado.direccion }}</p>
              <p class="text-sm text-gray-700 mt-1">Categoría: <span class="text-gray-900 font-semibold">{{ resultado.categoria_descripcion }}</span></p>
            </div>

            <div class="flex flex-col items-end gap-3">
              <span
                class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider"
                :class="resultado.estado_codigo === '1' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'"
              >
                {{ resultado.estado_descripcion }}
              </span>
              <div
                class="px-5 py-3 border rounded-xl text-right shadow-sm"
                :class="totalDeudaNum < 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-gray-200'"
              >
                <span
                  class="block text-[11px] uppercase font-semibold"
                  :class="totalDeudaNum < 0 ? 'text-emerald-700' : 'text-gray-500'"
                >
                  {{ totalDeudaNum < 0 ? 'Saldo a Favor' : 'Total Pendiente' }}
                </span>
                <span
                  class="text-3xl font-black font-mono"
                  :class="totalDeudaNum < 0 ? 'text-emerald-700' : 'text-blue-900'"
                >
                  Bs. {{ Math.abs(totalDeudaNum).toFixed(2) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Saldo a favor: no es deuda, es un caso especial que requiere trámite presencial
               (SIIC no expone un endpoint de devolución/aplicación de saldo), así que la nota
               tiene que ser visible, no un detalle chico que se pueda pasar por alto. -->
          <div v-if="totalDeudaNum < 0" class="p-6 sm:p-8 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-start gap-4">
            <svg class="w-9 h-9 sm:w-10 sm:h-10 shrink-0 text-amber-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.28 11.183c.75 1.334-.213 2.987-1.744 2.987H3.72c-1.53 0-2.493-1.653-1.744-2.987L8.257 3.1zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            <div class="space-y-1.5">
              <p class="text-xl sm:text-2xl font-black text-amber-900">Tu cuenta registra saldo a favor</p>
              <p class="text-base sm:text-lg text-amber-800">
                Para gestionar la devolución, apersonate a
                <span class="font-bold">Caja Central</span>: Calle Ayacucho Nº 254, Sucre - Bolivia.
              </p>
            </div>
          </div>

          <!-- Desglose único: la misma lista sirve para mostrar cada factura/conciliación
               pendiente Y para seleccionar hasta qué mes pagar (checkbox visible solo cuando
               hay algo que se puede cobrar por QR). Antes había una tabla de solo lectura acá
               arriba y otra lista de checkboxes duplicada adentro de la barra de pago -- se
               unificaron en una sola para no mostrar el mismo detalle dos veces. -->
          <div v-if="resultado.pendientes && resultado.pendientes.length" class="space-y-2">
            <p v-if="totalDeudaNum > 0 && resultado.pendientes.length > 1" class="text-xs text-gray-500">
              Elegí hasta qué mes pagar (se marcan automáticamente los anteriores, siempre desde el más antiguo)
            </p>
            <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-200">
              <label
                v-for="(p, idx) in resultado.pendientes"
                :key="idx"
                class="flex items-center gap-3 px-4 py-3.5"
                :class="totalDeudaNum > 0 ? 'cursor-pointer hover:bg-blue-50' : ''"
              >
                <!-- Bug real reproducido: desmarcar un mes y después marcar uno posterior
                     dejaba ese último visualmente sin marcar (aunque sí quedaba incluido en el
                     total calculado). Causa: para un checkbox, los "canceled activation steps"
                     del navegador (que corren SIEMPRE que el click no termina en un toggle
                     nativo normal) revierten `checked` al valor que tenía antes del click, y
                     lo hacen DESPUÉS de que corran los listeners -- así que cualquier valor que
                     Vue le haya aplicado vía `:checked` en la misma vuelta del evento queda
                     pisado. Por eso acá se fuerza el valor del DOM a mano dentro de @change (no
                     alcanza con la reactividad de Vue sola para el checkbox que se acaba de
                     clickear -- los demás sí se actualizan bien solos). Clickear un mes
                     siempre lo deja incluido (idx < nuevo mesesAPagar es siempre true para el
                     que se clickeó), por eso se puede fijar directo en `true`. -->
                <input
                  v-if="totalDeudaNum > 0"
                  type="checkbox"
                  :checked="idx < mesesAPagar"
                  @change="mesesAPagar = idx + 1; $event.target.checked = true"
                  class="w-4 h-4 rounded accent-blue-900 shrink-0"
                />
                <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                  <span class="text-sm font-mono font-semibold text-gray-900 shrink-0">{{ mesLiteral(p.mes) }} / {{ p.anio }}</span>
                  <span
                    class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide shrink-0"
                    :class="p.es_conciliacion ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200'"
                  >
                    {{ p.es_conciliacion ? 'Conciliación' : 'Factura' }}
                  </span>
                  <span v-if="p.detalle" class="text-xs text-gray-600 truncate">{{ p.detalle }}</span>
                </div>
                <span
                  class="text-sm font-mono font-black shrink-0"
                  :class="p.importe_firmado < 0 ? 'text-emerald-700' : 'text-blue-900'"
                >
                  Bs. {{ p.importe_firmado.toFixed(2) }}
                </span>
              </label>
            </div>
          </div>
          <div v-else class="p-6 bg-white border border-emerald-200 rounded-xl text-center text-sm text-emerald-800 font-semibold flex items-center justify-center gap-2">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            No registra facturas pendientes de pago.
          </div>

          <!-- Payment Options CTA Bar: no se muestra si el saldo total es negativo (nada que cobrar por QR). -->
          <div v-if="resultado.pendientes && resultado.pendientes.length && totalDeudaNum > 0" class="p-5 sm:p-6 bg-blue-900 text-white rounded-xl space-y-4">
            <div class="text-xs space-y-0.5">
              <span class="font-bold block text-sm">Elegí cómo pagar tu aviso</span>
              <span class="text-blue-100">Pago rápido por QR, al instante.</span>
            </div>

            <!-- El total de esta selección cambia según qué meses se marquen arriba (a
                 diferencia del "Total Pendiente"/"Saldo a Favor" de la cabecera, que es fijo),
                 así que se destaca más -- es lo que el cliente tiene que estar mirando mientras
                 elige hasta qué mes pagar. -->
            <div
              class="p-4 rounded-lg border"
              :class="(montoSeleccionadoNum <= 0 || montoExcedeLimite) ? 'bg-red-500/10 border-red-400/40' : 'bg-white/10 border-white/20'"
            >
              <span
                class="block text-xs uppercase font-bold tracking-wider"
                :class="(montoSeleccionadoNum <= 0 || montoExcedeLimite) ? 'text-red-200' : 'text-blue-200'"
              >
                Total a Pagar
              </span>
              <span
                class="block text-3xl sm:text-4xl font-black font-mono"
                :class="(montoSeleccionadoNum <= 0 || montoExcedeLimite) ? 'text-red-200' : 'text-white'"
              >
                Bs. {{ montoSeleccionado }}
              </span>
              <!-- Algunas cuentas mezclan facturas con conciliaciones (créditos): si la
                   selección actual todavía no acumula un monto positivo, no hay nada que
                   cobrar por QR todavía -- hay que incluir más meses hasta que el
                   acumulado cruce a positivo (ver PagoQrController, misma validación del
                   lado del servidor). -->
              <p v-if="montoSeleccionadoNum <= 0" class="text-sm sm:text-base font-semibold text-red-100 mt-1.5">
                Esta selección incluye conciliaciones y todavía no suma un monto positivo. Marcá algún mes más reciente hasta que el total vuelva a ser positivo para poder pagar.
              </p>
              <!-- Caso real detectado en una cuenta (deuda de más de Bs. 6.000.000, un dato
                   claramente anómalo de SIIC): un monto así de grande no se puede/debe cobrar
                   por QR. Mismo límite validado en el servidor (ver PagoQrController). -->
              <p v-else-if="montoExcedeLimite" class="text-sm sm:text-base font-semibold text-red-100 mt-1.5">
                Este monto supera el límite permitido para pago por QR (Bs. {{ LIMITE_MONTO_QR_TEXTO }}). No se puede realizar esta transacción por este medio.
              </p>
            </div>

            <!-- Banco BISA vía QR CESSA (propio) -- opción principal. Deshabilitado cerca de
                 medianoche (corte diario del sistema de cobros), si la selección actual de
                 meses todavía no suma un monto positivo, o si supera el límite de QR. -->
            <button
              type="button"
              @click="mostrarPagoQr = true"
              :disabled="qrDeshabilitado"
              class="group w-full p-4 sm:p-5 bg-amber-500 hover:bg-amber-400 disabled:bg-white/10 disabled:cursor-not-allowed rounded-xl text-left transition-all flex items-center justify-between gap-3 shadow-md"
            >
              <span class="space-y-0.5">
                <span class="block text-[10px] font-bold uppercase tracking-wider" :class="qrDeshabilitado ? 'text-blue-200' : 'text-blue-950/70'">Pago Rápido · Banco BISA</span>
                <span class="block text-base font-black" :class="qrDeshabilitado ? 'text-blue-100' : 'text-blue-950'">Pagar con QR CESSA</span>
              </span>
              <svg class="w-8 h-8 shrink-0" :class="qrDeshabilitado ? 'text-blue-200' : 'text-blue-950/70'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 1v2h2V5H5zm7-2a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V3zm2 1v2h2V4h-2zM3 12a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4zm2 1v2h2v-2H5zm7 0a1 1 0 011-1h1a1 1 0 011 1 1 1 0 001 1 1 1 0 011 1v1a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1h-1a1 1 0 01-1-1v-1zm5 4a1 1 0 01-1 1h-1a1 1 0 01-1-1 1 1 0 011-1h1a1 1 0 011 1z" clip-rule="evenodd" /></svg>
            </button>
            <p v-if="horaRestringida" class="text-[11px] text-amber-300">
              El pago por QR no está disponible entre las 23:59 y las 00:00 por el corte diario del sistema. Volvé a intentar en unos minutos.
            </p>

            <!-- O desde la app: no tiene sentido ofrecerla si el monto ya supera el límite de
                 QR -- la app usa el mismo cobro por QR, así que tampoco lo va a poder cobrar. -->
            <div v-if="!montoExcedeLimite" class="pt-1 space-y-2">
              <p class="text-[11px] text-blue-100">O desde la <span class="font-semibold text-white">App CESSA</span></p>
              <div class="grid grid-cols-2 gap-2.5">
                <a
                  href="https://apps.apple.com/bo/app/cessa/id6453522570"
                  target="_blank"
                  rel="noopener"
                  class="flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl bg-black hover:bg-neutral-800 border border-white/10 transition-colors"
                >
                  <svg class="w-6 h-6 text-white shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M14.94 5.19A4.38 4.38 0 0 0 16 2 4.44 4.44 0 0 0 13 3.52a4.17 4.17 0 0 0-1 3.09 3.69 3.69 0 0 0 2.94-1.42zm2.52 7.44a4.51 4.51 0 0 1 2.16-3.81 4.66 4.66 0 0 0-3.66-2c-1.56-.16-3 .91-3.83.91s-2-.89-3.3-.87a4.92 4.92 0 0 0-4.14 2.53C2.93 12.45 4.24 17 6 19.47c.8 1.21 1.8 2.58 3.12 2.53s1.75-.82 3.28-.82 2 .82 3.3.79 2.22-1.24 3.06-2.45a11 11 0 0 0 1.38-2.85 4.41 4.41 0 0 1-2.68-4.04z" /></svg>
                  <span class="leading-tight text-left">
                    <span class="block text-[9px] text-neutral-400">Disponible en</span>
                    <span class="block text-sm font-bold text-white">App Store</span>
                  </span>
                </a>
                <a
                  href="https://play.google.com/store/apps/details?id=bo.systems.itgroup.appcessausuarios"
                  target="_blank"
                  rel="noopener"
                  class="flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl bg-black hover:bg-neutral-800 border border-white/10 transition-colors"
                >
                  <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24">
                    <path d="M3.6 2.3c-.4.3-.6.8-.6 1.4v16.6c0 .6.2 1.1.6 1.4l.1.1L13 12v-.2L3.7 2.2z" fill="#00d2ff" />
                    <path d="M16.1 15.1 13 12v-.2l3.1-3.1 6.9 3.9c1 .6 1 1.5 0 2.1z" fill="#ffcf00" />
                    <path d="M16.1 15.1 13 12 3.7 21.4c.4.4 1 .4 1.7.1z" fill="#ff3a44" />
                    <path d="M16.1 8.7 5.4 2.5c-.7-.3-1.3-.3-1.7.1L13 12z" fill="#00e876" />
                  </svg>
                  <span class="leading-tight text-left">
                    <span class="block text-[9px] text-neutral-400">Disponible en</span>
                    <span class="block text-sm font-bold text-white">Google Play</span>
                  </span>
                </a>
              </div>
            </div>

            <p class="text-[11px] text-blue-300 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
              Más bancos con QR propio de CESSA, disponibles próximamente.
            </p>
          </div>

        </div>

      </div>
    </div>

    <PagoQrModal
      v-if="mostrarPagoQr"
      :nro-cliente="filters.nro_cliente"
      :zona="filters.zona"
      :manzano="filters.manzano"
      :correlativo="filters.correlativo"
      :cantidad-meses="mesesAPagar"
      @close="mostrarPagoQr = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PagoQrModal from '../Components/PagoQrModal.vue';

const props = defineProps({
  filters: Object,
  resultado: Object,
  error: String,
});

const loading = ref(false);
const mostrarAyuda = ref(false);
const mostrarPagoQr = ref(false);

const totalDeudaNum = computed(() => parseFloat(props.resultado?.total_deuda ?? 0));

// `resultado.pendientes` ya viene ordenado del más antiguo al más reciente (ver
// ConsultaDeudaController). Marcar el checkbox del mes N selecciona en cascada 1..N,
// porque SIIC no permite pagar un mes reciente salteando uno más viejo sin pagar todavía.
const mesesAPagar = ref(1);

// `importe_firmado` (armado en el backend, ver ConsultaDeudaController) ya viene con signo
// correcto: negativo para conciliaciones/créditos. Algunas cuentas mezclan facturas y
// conciliaciones, así que el acumulado desde el mes más antiguo puede pasar por negativo
// antes de volver a positivo -- el botón de pagar se deshabilita mientras eso pase (ver
// montoSeleccionadoNum más abajo).
const montoSeleccionadoNum = computed(() => {
  const pendientes = props.resultado?.pendientes ?? [];
  return pendientes
    .slice(0, mesesAPagar.value)
    .reduce((sum, p) => sum + parseFloat(p.importe_firmado ?? 0), 0);
});

const montoSeleccionado = computed(() => montoSeleccionadoNum.value.toFixed(2));

// Caso real detectado: una cuenta con una deuda de SIIC de más de Bs. 6.000.000 (dato
// anómalo del sistema comercial, no algo que un cliente real deba pagar por QR). Mismo
// límite validado del lado del servidor (ver PagoQrController) -- esto es solo para avisar
// antes de intentarlo.
const LIMITE_MONTO_QR = 50000;
const LIMITE_MONTO_QR_TEXTO = LIMITE_MONTO_QR.toLocaleString('es-BO');
const montoExcedeLimite = computed(() => montoSeleccionadoNum.value > LIMITE_MONTO_QR);

// El sistema de cobros hace su corte diario entre 23:59 y 00:00: durante ese minuto no se
// puede generar QR de forma confiable. Se recalcula en hora de Bolivia (no la del navegador
// de quien visita el sitio) cada 15s para deshabilitar el botón en vivo si cae justo ahí.
const horaRestringida = ref(false);
let relojTimer = null;

const chequearHoraRestringida = () => {
  const partes = new Intl.DateTimeFormat('en-US', {
    timeZone: 'America/La_Paz',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).formatToParts(new Date());
  const hora = parseInt(partes.find((p) => p.type === 'hour').value, 10);
  const minuto = parseInt(partes.find((p) => p.type === 'minute').value, 10);
  horaRestringida.value = hora === 23 && minuto === 59;
};

onMounted(() => {
  chequearHoraRestringida();
  relojTimer = setInterval(chequearHoraRestringida, 15000);
});

onBeforeUnmount(() => {
  if (relojTimer) clearInterval(relojTimer);
});

const qrDeshabilitado = computed(
  () => horaRestringida.value || montoSeleccionadoNum.value <= 0 || montoExcedeLimite.value
);

watch(
  () => props.resultado,
  (nuevo) => {
    mesesAPagar.value = nuevo?.pendientes?.length ?? 1;
  },
  { immediate: true }
);

const camposUbicacion = props.filters?.zona && props.filters?.manzano && props.filters?.correlativo
  ? `${props.filters.zona}-${props.filters.manzano}-${props.filters.correlativo}`
  : '';

const form = reactive({
  nro_cliente: props.filters?.nro_cliente || '',
  nro_cuenta: camposUbicacion,
});

const formatError = ref('');

const onNroCuentaInput = (e) => {
  const digits = e.target.value.replace(/\D/g, '').slice(0, 10);
  let formatted = digits;
  if (digits.length > 5) {
    formatted = `${digits.slice(0, 2)}-${digits.slice(2, 5)}-${digits.slice(5)}`;
  } else if (digits.length > 2) {
    formatted = `${digits.slice(0, 2)}-${digits.slice(2)}`;
  }
  form.nro_cuenta = formatted;
  e.target.value = formatted;
};

const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const mesLiteral = (mes) => MESES[parseInt(mes, 10) - 1] ?? mes;

const submitSearch = () => {
  formatError.value = '';

  const partes = form.nro_cuenta.split('-').map((p) => p.trim()).filter(Boolean);
  if (partes.length !== 3) {
    formatError.value = 'El N° de Cuenta debe tener el formato Zona-Manzano-Correlativo, ej. 9-35-4000.';
    return;
  }
  const [zona, manzano, correlativo] = partes;
  const params = { nro_cliente: form.nro_cliente, zona, manzano, correlativo };

  loading.value = true;
  router.post('/consulta-deuda', params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};
</script>
