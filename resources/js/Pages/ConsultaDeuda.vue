<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Consulta Comercial en Línea
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Estado de Cuenta y Avisos de Cobranza</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            Verifica el estado de tus facturas ingresando tu número de abonado y tu N° de Cuenta.
          </p>
        </div>

        <!-- Search Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          <form @submit.prevent="submitSearch" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Número de Abonado</label>
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

            <div>
              <button
                type="submit"
                :disabled="loading"
                class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
              >
                Consultar
              </button>
            </div>
          </form>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center space-x-3">
          <span>{{ error }}</span>
        </div>

        <!-- Result Box -->
        <div v-if="resultado" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-200 gap-4">
            <div>
              <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Abonado Registrado</span>
              <h2 class="text-2xl font-black text-gray-900 mt-1">{{ resultado.nombre }}</h2>
              <p class="text-xs text-gray-600 mt-1">N° Cuenta: <span class="text-gray-900 font-mono font-bold">{{ resultado.nro_cuenta }}</span></p>
              <p class="text-xs text-gray-600 mt-1">{{ resultado.direccion }}</p>
              <p class="text-xs text-gray-600 mt-1">Categoría: <span class="text-gray-900 font-semibold">{{ resultado.categoria_descripcion }}</span></p>
            </div>

            <div class="flex flex-col items-end gap-3">
              <span
                class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider"
                :class="resultado.estado_codigo === '1' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'"
              >
                {{ resultado.estado_descripcion }}
              </span>
              <div class="px-5 py-3 bg-white border border-gray-200 rounded-xl text-right shadow-sm">
                <span class="block text-[11px] text-gray-500 uppercase font-semibold">Total Pendiente</span>
                <span class="text-3xl font-black text-blue-900 font-mono">Bs. {{ resultado.total_deuda }}</span>
              </div>
            </div>
          </div>

          <!-- Pending Invoices Table -->
          <div v-if="resultado.pendientes && resultado.pendientes.length" class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700">
              <thead class="bg-blue-900 text-white font-semibold uppercase tracking-wider">
                <tr>
                  <th class="px-4 py-3.5 rounded-l-lg">Periodo</th>
                  <th class="px-4 py-3.5 text-right rounded-r-lg">Importe</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 font-mono">
                <tr v-for="(p, idx) in resultado.pendientes" :key="idx" class="hover:bg-blue-50">
                  <td class="px-4 py-4 font-semibold text-gray-900">{{ mesLiteral(p.mes) }} / {{ p.anio }}</td>
                  <td class="px-4 py-4 text-right font-black text-blue-900">Bs. {{ p.importe }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-6 bg-white border border-emerald-200 rounded-xl text-center text-sm text-emerald-800 font-semibold flex items-center justify-center gap-2">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            No registra facturas pendientes de pago.
          </div>

          <!-- Payment Options CTA Bar -->
          <div v-if="resultado.pendientes && resultado.pendientes.length" class="p-5 bg-blue-900 text-white rounded-xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs space-y-0.5">
              <span class="font-bold block">Pago Rápido por QR o Banca Móvil Simple</span>
              <span class="text-blue-100">Genera tu código QR oficial y cancela tu aviso de cobranza al instante.</span>
            </div>
            <a
              href="https://recaudodigital.sintesis.com.bo/suitepagos-ui/company/CESSA"
              target="_blank"
              class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-black rounded-lg text-xs transition-all shadow-md whitespace-nowrap"
            >
              Ir a Pagar con QR
            </a>
          </div>

        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  filters: Object,
  resultado: Object,
  error: String,
});

const loading = ref(false);

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

const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
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
