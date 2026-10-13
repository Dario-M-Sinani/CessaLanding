<template>
  <div class="my-10 bg-white border border-blue-100 rounded-3xl p-6 sm:p-8 shadow-md space-y-6 print:my-0 print:p-0 print:border-0 print:shadow-none print:break-after-page">
    <!-- Encabezado del Widget -->
    <div class="text-center sm:text-left flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-6">
      <div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-900 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
          ⚡ Guía Rápida de Materiales
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-blue-950">
          Selecciona tu Tipo de Instalación
        </h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-1">
          Consulta los materiales reglamentarios y especificaciones según la edificación.
        </p>
      </div>

      <!-- Botón de Acción Principal Imprimir -->
      <div class="flex flex-wrap gap-2 shrink-0 print:hidden">
        <a
          :href="printUrl"
          target="_blank"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-950 hover:bg-blue-900 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Imprimir Etiqueta
        </a>
        <a
          href="/nueva-conexion"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-blue-950 text-xs font-extrabold rounded-xl transition-all shadow-sm hover:shadow"
        >
          Solicitar en Línea →
        </a>
      </div>
    </div>

    <!-- Al imprimir no hay pestañas: se dice qué tipo quedó elegido -->
    <p class="hidden print:block text-base font-extrabold text-blue-950">
      {{ activeData.titulo }}<span v-if="activeData.variantes"> — {{ currentVariantData?.label }}</span>
    </p>

    <!-- Pestañas de Selección de Tipo de Instalación -->
    <div class="print:hidden grid grid-cols-1 sm:grid-cols-3 gap-2 p-1.5 bg-gray-100 rounded-2xl">
      <button
        v-for="tipo in tipos"
        :key="tipo.id"
        @click="selectTab(tipo.id)"
        type="button"
        class="py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all text-center flex flex-col items-center justify-center gap-1"
        :class="currentTab === tipo.id 
          ? 'bg-white text-blue-950 shadow-sm ring-1 ring-black/5 font-extrabold' 
          : 'text-gray-600 hover:text-blue-950 hover:bg-white/50'"
      >
        <span class="flex items-center gap-1.5">
          <span class="text-base">{{ tipo.icon }}</span>
          {{ tipo.label }}
        </span>
        <span class="text-[10px] text-gray-500 font-normal">{{ tipo.sublabel }}</span>
      </button>
    </div>

    <!-- Contenido del Tipo Seleccionado -->
    <div class="grid grid-cols-1 lg:grid-cols-12 print:grid-cols-12 gap-8 print:gap-5 items-start pt-2">
      <!-- Columna Izquierda: Diagramas y Croquis de Puesto de Medición -->
      <div class="lg:col-span-5 print:col-span-5 space-y-4">
        <!-- Sub-selector de Variante (1 Piso / 2 Pisos) -->
        <div v-if="activeData.variantes" class="print:hidden flex gap-2 p-1 bg-gray-50 border border-gray-200 rounded-xl">
          <button
            v-for="(v, k) in activeData.variantes"
            :key="k"
            @click="selectVariant(k)"
            type="button"
            class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all text-center"
            :class="activeVariant === k ? 'bg-blue-900 text-white shadow-xs' : 'text-gray-600 hover:text-blue-900'"
          >
            {{ v.label }}
          </button>
        </div>

        <!-- Imagen / Diagrama Técnico -->
        <div class="relative bg-gray-50 border border-gray-200 rounded-2xl p-4 overflow-hidden group">
          <div class="flex items-center justify-center min-h-[260px] max-h-[320px]">
            <img
              :src="currentVariantData?.imagen"
              :alt="activeData.titulo"
              class="max-h-[300px] w-auto object-contain mx-auto transition-transform duration-300 group-hover:scale-105"
            />
          </div>
          <div class="mt-3 text-center">
            <p class="text-xs font-semibold text-gray-700">
              {{ currentVariantData?.descripcion_variante }}
            </p>
          </div>
        </div>

        <!-- Especificaciones Técnicas Destacadas -->
        <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-4 space-y-2 text-xs">
          <h4 class="font-bold text-blue-950 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Normas Técnicas (NB 777)
          </h4>
          <ul class="space-y-1.5 text-gray-700">
            <li v-for="(req, idx) in activeData.requisitos_tecnicos" :key="idx" class="flex items-start gap-1.5">
              <span class="text-blue-600 font-bold">•</span>
              <span>{{ req }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Columna Derecha: Checklist Interactivo de Materiales -->
      <div class="lg:col-span-7 print:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-extrabold text-blue-950">
            Lista de Materiales Reglamentarios
          </h3>
          <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
            {{ checkedCount }} de {{ materiales.length }} marcados
          </span>
        </div>

        <!-- Lista de Materiales con Checkboxes -->
        <div class="space-y-2">
          <label
            v-for="(mat, idx) in materiales"
            :key="idx"
            class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer"
            :class="checkedMaterials[idx] ? 'bg-emerald-50/60 border-emerald-300 text-emerald-950' : 'bg-white border-gray-200 hover:border-blue-200 text-gray-800'"
          >
            <input
              type="checkbox"
              v-model="checkedMaterials[idx]"
              class="mt-1 w-4 h-4 rounded text-blue-900 focus:ring-blue-900 border-gray-300"
            />
            <div class="flex-1 text-xs">
              <span class="font-extrabold text-blue-950 inline-block min-w-[55px] mr-1">
                {{ mat.cantidad }}
              </span>
              <span :class="{ 'line-through opacity-70': checkedMaterials[idx] }">
                {{ mat.item }}
              </span>
            </div>
          </label>
        </div>

        <!-- Botones al Pie del Checklist -->
        <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100 print:hidden">
          <a
            :href="printUrl"
            target="_blank"
            class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-colors shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir Etiqueta de Materiales
          </a>
          <a
            href="/nueva-conexion"
            class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 bg-amber-500 hover:bg-amber-400 text-blue-950 text-xs font-extrabold rounded-xl transition-colors shadow-sm"
          >
            Comenzar Trámite de Nueva Conexión
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';

const tipos = [
  { id: 'monofasica', label: 'Monofásica', sublabel: '220V • Domiciliaria', icon: '🏠' },
  { id: 'trifasica', label: 'Trifásica', sublabel: '380V • Comercial/Taller', icon: '⚡' },
  { id: 'tablero-centralizador', label: 'Tablero Centralizador', sublabel: '3+ Medidores • Edificios', icon: '🏢' },
];

const currentTab = ref('monofasica');
const activeVariant = ref('con_baston');
const checkedMaterials = reactive({});

// Catálogo único: viene del servidor (InstalacionMaterialesController::getCatalogo), el mismo
// que usa la etiqueta imprimible -- antes había una copia acá que ya difería en los textos.
const props = defineProps({
  catalogo: { type: Object, required: true },
});

const activeData = computed(() => props.catalogo[currentTab.value]);
const currentVariantData = computed(() => activeData.value.variantes?.[activeVariant.value] ?? Object.values(activeData.value.variantes ?? {})[0]);

// Sin bastón no van los ítems 'solo_con_baston' (igual que en la etiqueta).
const materiales = computed(() => activeData.value.materiales.filter((m) => activeVariant.value === 'con_baston' || !m.solo_con_baston));
const printUrl = computed(() => `/instalaciones/imprimir/${currentTab.value}?variante=${activeVariant.value}`);

const checkedCount = computed(() => {
  return Object.values(checkedMaterials).filter(Boolean).length;
});

const resetChecks = () => Object.keys(checkedMaterials).forEach((k) => delete checkedMaterials[k]);

const selectTab = (tabId) => {
  currentTab.value = tabId;
  activeVariant.value = 'con_baston';
  resetChecks();
};

// Los checks van por índice: al cambiar de variante la lista cambia, así que se limpian.
const selectVariant = (k) => {
  activeVariant.value = k;
  resetChecks();
};
</script>
