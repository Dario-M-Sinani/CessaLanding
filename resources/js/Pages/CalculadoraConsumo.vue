<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Simulador Tarifario Oficial
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Calculadora de Consumo Eléctrico</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            Estima el importe de tu aviso mensual según tu categoría de suministro, periodo y kWh proyectados.
          </p>
        </div>

        <!-- Calculator Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8">

          <!-- Left Controls (Col 7) -->
          <div class="md:col-span-7 space-y-6">

            <!-- Category Select -->
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                1. Categoría Tarifaria de Suministro
              </label>
              <select
                v-model="categoria"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:border-blue-900 text-sm font-medium"
              >
                <option v-for="cat in categories" :key="cat.codigo" :value="cat.codigo">
                  {{ cat.nombre }}
                </option>
              </select>
            </div>

            <!-- Period Select -->
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                2. Periodo de Facturación
              </label>
              <select
                v-model="periodo"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:border-blue-900 text-sm font-medium"
              >
                <option v-for="p in periods" :key="p.fecha_vigencia" :value="p.fecha_vigencia">
                  {{ p.label }}
                </option>
              </select>
            </div>

            <!-- kWh Slider & Input -->
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                  3. Consumo Mensual Proyectado (kWh)
                </label>
                <span class="text-xl font-black text-blue-900 font-mono bg-blue-50 px-3 py-1 rounded-lg border border-blue-200">
                  {{ consumo }} kWh
                </span>
              </div>

              <input
                v-model.number="consumo"
                type="range"
                min="0"
                max="1000"
                step="5"
                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-amber-500 border border-gray-300"
              />

              <input
                v-model.number="consumo"
                type="number"
                min="0"
                max="10000"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm font-mono font-bold"
                placeholder="Ingrese kWh..."
              />
            </div>

            <!-- Demanda Input (only for categories that require it) -->
            <div v-if="requiresDemanda">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                4. Demanda Contratada (kW)
              </label>
              <input
                v-model.number="demanda"
                type="number"
                min="0"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm font-mono font-bold"
                placeholder="Ingrese demanda en kW..."
              />
            </div>

            <!-- Pre-set Buttons -->
            <div>
              <span class="block text-xs text-gray-500 mb-2 font-medium">Consumos de referencia habituales:</span>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="preset in [50, 120, 250, 500]"
                  :key="preset"
                  @click="consumo = preset"
                  :class="[
                    consumo === preset ? 'bg-amber-500 text-blue-950 font-bold' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100',
                    'px-3.5 py-2 rounded-lg text-xs font-mono transition-all'
                  ]"
                >
                  {{ preset }} kWh
                </button>
              </div>
            </div>

            <button
              @click="calcular"
              :disabled="loading"
              class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
            >
              {{ loading ? 'Calculando...' : 'Calcular Importe' }}
            </button>

          </div>

          <!-- Right Estimate Card (Col 5) -->
          <div class="md:col-span-5 bg-white border border-gray-200 rounded-xl p-6 flex flex-col justify-between space-y-6 shadow-sm">

            <div>
              <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest block mb-1">
                Aviso de Cobranza Estimado
              </span>
              <div class="text-4xl font-black text-blue-900 font-mono">
                Bs. {{ resultado ? resultado.total : '0.00' }}
              </div>
              <span class="text-[11px] text-gray-500 mt-1 block">Importe aproximado a pagar</span>
            </div>

            <div v-if="error" class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-3">
              {{ error }}
            </div>

            <!-- Items Breakdown -->
            <div v-if="resultado" class="space-y-2.5 text-xs text-gray-700 border-t border-gray-200 pt-4 font-mono">
              <div class="flex justify-between">
                <span class="text-gray-600">Importe por Energía:</span>
                <span class="font-bold text-gray-900">Bs. {{ resultado.importe_energia }}</span>
              </div>
              <div v-if="parseFloat(resultado.importe_demanda) > 0" class="flex justify-between">
                <span class="text-gray-600">Importe por Demanda:</span>
                <span class="font-bold text-gray-900">Bs. {{ resultado.importe_demanda }}</span>
              </div>
            </div>
            <div v-else class="text-xs text-gray-500 border-t border-gray-200 pt-4">
              Completa el formulario y presiona "Calcular Importe" para ver el detalle.
            </div>

            <div class="text-[11px] text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-200">
              Valores oficiales calculados en línea con el sistema comercial SIIC de CESSA.
            </div>

          </div>

        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  categories: Array,
  periods: Array,
});

const categoria = ref(props.categories?.[0]?.codigo ?? '');
const periodo = ref(props.periods?.[0]?.fecha_vigencia ?? '');
const consumo = ref(150);
const demanda = ref(null);
const loading = ref(false);
const error = ref(null);
const resultado = ref(null);

const requiresDemanda = computed(() => {
  const cat = props.categories?.find((c) => c.codigo === categoria.value);
  return cat?.demanda === '1';
});

const getCookie = (name) => {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
};

const calcular = async () => {
  loading.value = true;
  error.value = null;
  resultado.value = null;

  try {
    const response = await fetch('/api/calculo-consumo', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
      body: JSON.stringify({
        consumo: consumo.value,
        categoria: categoria.value,
        periodo: periodo.value,
        demanda: requiresDemanda.value ? demanda.value : null,
      }),
    });

    const json = await response.json();

    if (json.success) {
      resultado.value = json.data;
    } else {
      error.value = json.message || 'No se pudo calcular el importe.';
    }
  } catch (e) {
    error.value = 'No se pudo conectar con el sistema comercial. Intenta más tarde.';
  } finally {
    loading.value = false;
  }
};
</script>
