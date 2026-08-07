<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Talento Humano
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Recursos Humanos</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            El equipo técnico y administrativo capacitado que hace posible el suministro eléctrico de la región.
          </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm space-y-6">
          <template v-if="content">
            <ContentBody :html="content.full_text" />
            <p v-if="latestStaff" class="text-gray-700 text-sm leading-relaxed">
              En la gestión <strong>{{ latestStaff.year }}</strong> se cuenta con un total de
              <strong>{{ latestStaff.employees }}</strong> trabajadores de planta.
            </p>
          </template>
          <p v-else class="text-gray-500 text-sm text-center">Contenido no disponible por el momento.</p>
        </div>

        <div v-if="composicionYears.length" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-4">
          <div class="space-y-1">
            <h2 class="text-lg sm:text-xl font-extrabold text-blue-950">Clientes y Personal</h2>
            <p class="text-xs text-gray-500">Clientes (barras) y Personal (líneas) desde 2020.</p>
          </div>
          <YearlyChart
            :years="composicionYears"
            :bar-series="clientesBarSeries"
            :line-series="composicionLineSeries"
            aria-label="Gráfico combinado de clientes y personal (total, fijos, eventuales) por año, desde 2020"
          />
        </div>

        <div v-if="latestGender" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-4">
          <h2 class="text-lg sm:text-xl font-extrabold text-blue-950">Personal Masculino / Femenino</h2>
          <p class="text-xs text-gray-500">Composición del personal de planta por género — gestión {{ latestGender.year }}.</p>
          <PieChart
            :slices="latestGenderSlices"
            aria-label="Gráfico de composición de personal por género"
          />
          <details class="text-xs text-gray-600">
            <summary class="cursor-pointer font-semibold text-blue-900 hover:underline">Ver datos en tabla</summary>
            <div class="overflow-x-auto mt-3">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-gray-300 text-gray-500 uppercase text-[11px] tracking-wider">
                    <th class="py-2 pr-4">Gestión</th>
                    <th class="py-2 pr-4">Masculino</th>
                    <th class="py-2 pr-4">% Masculino</th>
                    <th class="py-2 pr-4">Femenino</th>
                    <th class="py-2 pr-4">% Femenino</th>
                    <th class="py-2">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in genderTableRows" :key="row.year" class="border-b border-gray-200">
                    <td class="py-2 pr-4">{{ row.year }}</td>
                    <td class="py-2 pr-4">{{ formatNumber(row.male) }}</td>
                    <td class="py-2 pr-4">{{ row.malePct.toFixed(2) }}%</td>
                    <td class="py-2 pr-4">{{ formatNumber(row.female) }}</td>
                    <td class="py-2 pr-4">{{ row.femalePct.toFixed(2) }}%</td>
                    <td class="py-2">{{ formatNumber(row.total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </details>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import ContentBody from '../../Components/ContentBody.vue';
import YearlyChart from '../../Components/Charts/YearlyChart.vue';
import PieChart from '../../Components/Charts/PieChart.vue';

const props = defineProps({
  content: Object,
});

const formatNumber = (v) => new Intl.NumberFormat('es-BO').format(v);

const staffStats = computed(() => props.content?.staff_yearly_stats || []);
const genderStats = computed(() => props.content?.gender_yearly_stats || []);

const latestStaff = computed(() => staffStats.value[staffStats.value.length - 1] || null);

const composicionRows = computed(() =>
  staffStats.value.filter((s) => s.year >= 2020 && s.permanentes != null && s.eventuales != null)
);
const composicionYears = computed(() => composicionRows.value.map((s) => s.year));
const clientesBarSeries = computed(() => ({
  name: 'Clientes',
  color: '#eda100',
  values: composicionRows.value.map((s) => s.clients),
}));
const composicionLineSeries = computed(() => [
  { name: 'Total Trabajadores', color: '#2a78d6', values: composicionRows.value.map((s) => s.employees) },
  { name: 'Trabajadores Fijos', color: '#4a3aa7', values: composicionRows.value.map((s) => s.permanentes) },
  { name: 'Trabajadores Eventuales', color: '#1baf7a', values: composicionRows.value.map((s) => s.eventuales) },
]);

const latestGender = computed(() => {
  if (!genderStats.value.length) return null;
  return [...genderStats.value].sort((a, b) => a.year - b.year).at(-1);
});

const latestGenderSlices = computed(() => {
  if (!latestGender.value) return [];
  return [
    { name: 'Personal Masculino', value: latestGender.value.male, color: '#2a78d6' },
    { name: 'Personal Femenino', value: latestGender.value.female, color: '#eb6834' },
  ];
});

const genderTableRows = computed(() =>
  genderStats.value.map((g) => {
    const total = g.male + g.female;
    return {
      year: g.year,
      male: g.male,
      female: g.female,
      total,
      malePct: total ? (g.male / total) * 100 : 0,
      femalePct: total ? (g.female / total) * 100 : 0,
    };
  })
);
</script>
