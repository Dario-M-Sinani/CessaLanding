<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Información Oficial
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Estructura Tarifaria</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            Consulta los cargos vigentes por categoría de suministro para cada periodo de facturación.
          </p>
        </div>

        <!-- Period Selector -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm">
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Seleccione Periodo</label>
          <select
            v-model="periodoId"
            @change="cargarDetalle"
            class="w-full sm:w-80 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:border-blue-900 text-sm font-medium"
          >
            <option value="" disabled>Seleccione Periodo</option>
            <option v-for="p in periods" :key="p.id" :value="p.id">{{ p.label }}</option>
          </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center text-sm text-gray-500 py-8">Cargando...</div>

        <!-- Error -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
          {{ error }}
        </div>

        <!-- Rate Tables -->
        <div v-if="!loading && categorias.length" class="space-y-8">
          <div v-for="(categoria, idx) in categorias" :key="idx" class="space-y-3">
            <h5 class="text-sm font-bold uppercase tracking-wider text-blue-950">{{ categoria.nombre }}</h5>
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
              <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-blue-900 text-white font-semibold uppercase tracking-wider">
                  <tr>
                    <th class="px-4 py-3">Concepto</th>
                    <th class="px-4 py-3 text-left">Unidad</th>
                    <th class="px-4 py-3 text-right">Valor</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="(fila, i) in filasDe(categoria)" :key="i" class="hover:bg-blue-50">
                    <td class="px-4 py-3 font-semibold text-gray-900">{{ fila.concepto }}</td>
                    <td class="px-4 py-3">{{ fila.unidad }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-blue-900">{{ fila.valor }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  periods: Array,
});

const periodoId = ref('');
const categorias = ref([]);
const loading = ref(false);
const error = ref(null);

const getCookie = (name) => {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
};

const toCurrency = (value) => {
  const n = parseFloat(value);
  return Number.isNaN(n) ? '0.00' : n.toFixed(2);
};

const cargarDetalle = async () => {
  if (!periodoId.value) return;

  loading.value = true;
  error.value = null;
  categorias.value = [];

  try {
    const response = await fetch(`/api/estructura-tarifaria/${periodoId.value}`, {
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
    });
    const json = await response.json();

    if (json.success) {
      categorias.value = json.data;
    } else {
      error.value = json.message || 'No se encontraron tarifas para ese periodo.';
    }
  } catch (e) {
    error.value = 'No se pudo conectar con el sistema comercial. Intenta más tarde.';
  } finally {
    loading.value = false;
  }
};

// Misma lógica de negocio que el legacy (estructura-tarifaria.js), portada a Vue.
const concepto = (demanda, kwDesde, kwHasta, cargoDescripcion, categoriaNombre, last) => {
  if (categoriaNombre === 'ALUMBRADO PUBLICO') {
    return 'Cargo por Energía';
  }
  if (demanda) {
    return `${cargoDescripcion} desde ${kwDesde} kWh`;
  }
  if (cargoDescripcion === 'CARGO MINIMO' || cargoDescripcion === 'CARGO FIJO') {
    return `Cargo mínimo (derecho a ${kwHasta} kWh-mes)`;
  }
  if (last) {
    return `Excedente a ${kwDesde} kWh`;
  }
  return `De ${kwDesde} kWh a ${kwHasta} kWh`;
};

const unidad = (descripcion, categoriaNombre) => {
  if (descripcion === 'CARGO MINIMO' || descripcion === 'CARGO FIJO') {
    return 'Bs';
  }
  if (categoriaNombre === 'CEMENTERAS' || categoriaNombre === 'INDUSTRIAL 2') {
    return 'Bs/kw-mes';
  }
  return 'Bs/kWh';
};

const filasDe = (categoria) => {
  if (categoria.nombre === 'REVENTA') {
    const rango = categoria.rangos?.[0] ?? {};
    return [
      { concepto: 'Cargo por demanda', unidad: 'Bs', valor: toCurrency(rango.cargo_demanda) },
      { concepto: 'Cargo por energía', unidad: 'Bs/kWh', valor: toCurrency(rango.cargo_rango) },
    ];
  }

  if (categoria.nombre === 'CEMENTERAS' || categoria.nombre === 'INDUSTRIAL 2') {
    const rango = categoria.rangos?.[0] ?? {};
    return [
      { concepto: 'Cargo por Potencia', unidad: unidad(rango.cargo_descripcion, categoria.nombre), valor: toCurrency(rango.cargo_demanda) },
      { concepto: 'Cargo por energía', unidad: 'Bs/kWh', valor: toCurrency(rango.cargo_rango) },
    ];
  }

  return (categoria.rangos ?? []).map((rango, i, arr) => {
    const last = i === arr.length - 1 && arr.length > 1;
    const valor = rango.es_cargo_fijo === '1' ? toCurrency(rango.cargo_fijo) : toCurrency(rango.cargo_rango);
    return {
      concepto: concepto(categoria.demanda === '1', rango.kw_desde, rango.kw_hasta, rango.cargo_descripcion, categoria.nombre, last),
      unidad: unidad(rango.cargo_descripcion, categoria.nombre),
      valor,
    };
  });
};
</script>
