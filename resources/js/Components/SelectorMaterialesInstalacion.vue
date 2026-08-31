<template>
  <div class="my-10 bg-white border border-blue-100 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
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
      <div class="flex flex-wrap gap-2 shrink-0">
        <a
          :href="`/instalaciones/imprimir/${currentTab}`"
          target="_blank"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-950 hover:bg-blue-900 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Imprimir Lista (B&N)
        </a>
        <a
          href="/nueva-conexion"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-blue-950 text-xs font-extrabold rounded-xl transition-all shadow-sm hover:shadow"
        >
          Solicitar en Línea →
        </a>
      </div>
    </div>

    <!-- Pestañas de Selección de Tipo de Instalación -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-1.5 bg-gray-100 rounded-2xl">
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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start pt-2">
      <!-- Columna Izquierda: Diagramas y Croquis de Puesto de Medición -->
      <div class="lg:col-span-5 space-y-4">
        <!-- Sub-selector de Variante (1 Piso / 2 Pisos) -->
        <div v-if="activeData.variantes" class="flex gap-2 p-1 bg-gray-50 border border-gray-200 rounded-xl">
          <button
            v-for="(v, k) in activeData.variantes"
            :key="k"
            @click="activeVariant = k"
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
      <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-extrabold text-blue-950">
            Lista de Materiales Reglamentarios
          </h3>
          <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
            {{ checkedCount }} de {{ activeData.materiales.length }} marcados
          </span>
        </div>

        <!-- Lista de Materiales con Checkboxes -->
        <div class="space-y-2">
          <label
            v-for="(mat, idx) in activeData.materiales"
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
        <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100">
          <a
            :href="`/instalaciones/imprimir/${currentTab}`"
            target="_blank"
            class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-colors shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir Lista de Materiales (B&N)
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

const catalogo = {
  monofasica: {
    titulo: 'Instalación Monofásica (220V)',
    variantes: {
      con_baston: {
        label: '1 Piso (Con Bastón)',
        imagen: '/storage/nuevas_instalaciones/monofasico-con-baston2.png',
        descripcion_variante: 'Para casas de una sola planta con bastón galvanizado para dar la altura reglamentaria a la acometida.',
      },
      sin_baston: {
        label: '2 Pisos o más (Sin Bastón)',
        imagen: '/storage/nuevas_instalaciones/monofasico-sin-baston2.png',
        descripcion_variante: 'Para inmuebles de dos o más plantas con ingreso directo de acometida aérea a la fachada.',
      },
    },
    materiales: [
      { cantidad: '1 pza.', item: 'Caja metálica de acero de 1 mm de espesor para medidor monofásico.' },
      { cantidad: '8 mts.', item: 'Alambre aislado de cobre Nro. 10 AWG (o cable según carga solicitada).' },
      { cantidad: '1 pza.', item: 'Interruptor termomagnético bipolar (amperaje según carga calculada).' },
      { cantidad: '1 pza.', item: 'Tubo plástico conduit (tubo PVC) de 3/4 de pulgada de diámetro.' },
      { cantidad: '1 pza.', item: 'Bastón de cañería galvanizada de 3/4" de diámetro (para 1 piso).' },
      { cantidad: '1 pza.', item: 'Medidor monofásico certificado (provisto por CESSA tras aprobación).' },
      { cantidad: '1 pza.', item: 'Conector de varilla de tierra (grapa de bronce de alta presión).' },
      { cantidad: '1 pza.', item: 'Varilla de tierra de cobre de 5/8" de diámetro (mínimo 0.80 m de longitud).' },
      { cantidad: '6 pzas.', item: 'Tornillos de encarne de 3/4" con sus respectivos tarugos.' },
      { cantidad: '1 pza.', item: 'Rack de 2 vías con aisladores de porcelana para anclaje de acometida.' },
    ],
    requisitos_tecnicos: [
      'Puesto de medición ubicado en la línea municipal frontal hacia la calle.',
      'Altura máxima de empotrado de la caja: 1.40 metros.',
      'Distancia máxima a la red eléctrica de CESSA: 34 metros (NB 777).',
      'Una sola acometida por predio.',
    ],
  },
  trifasica: {
    titulo: 'Instalación Trifásica (380V / 220V)',
    variantes: {
      con_baston: {
        label: '1 Piso (Con Bastón)',
        imagen: '/storage/nuevas_instalaciones/trifasico-con-baston3.png',
        descripcion_variante: 'Para predios de una planta con bastón galvanizado trifásico.',
      },
      sin_baston: {
        label: '2 Pisos o más (Sin Bastón)',
        imagen: '/storage/nuevas_instalaciones/trifasico-sin-baston3.png',
        descripcion_variante: 'Ingreso directo a fachada de acometida trifásica.',
      },
    },
    materiales: [
      { cantidad: '1 pza.', item: 'Caja metálica de acero de 1 mm de espesor para medidor trifásico.' },
      { cantidad: '8 mts.', item: 'Alambre aislado de cobre Nro. 10 AWG (o calibre superior según potencia declarada).' },
      { cantidad: '1 pza.', item: 'Interruptor termomagnético tripolar (amperaje según carga trifásica).' },
      { cantidad: '1 pza.', item: 'Tubo plástico conduit (tubo PVC) de 3/4 de pulgada de diámetro.' },
      { cantidad: '1 pza.', item: 'Bastón de cañería galvanizada de 3/4" o 1" de diámetro (si aplica 1 piso).' },
      { cantidad: '1 pza.', item: 'Medidor trifásico certificado (homologado por CESSA).' },
      { cantidad: '1 pza.', item: 'Conector de varilla de tierra de bronce.' },
      { cantidad: '1 pza.', item: 'Varilla de tierra de cobre de 5/8" de diámetro (mínimo 0.80 m de longitud).' },
      { cantidad: '6 pzas.', item: 'Tornillos de encarne de 3/4" con tarugos de fijación.' },
      { cantidad: '1 pza.', item: 'Rack de 4 vías con aisladores de porcelana para acometida trifásica.' },
    ],
    requisitos_tecnicos: [
      'Puesto de medición en fachada frontal con libre acceso para lectura.',
      'Altura máxima de la caja: 1.40 metros sobre acera.',
      'Distancia máxima a la red de baja tensión de CESSA: 34 metros (NB 777).',
      'Aterramiento independiente con varilla de cobre certificada.',
    ],
  },
  'tablero-centralizador': {
    titulo: 'Tablero Centralizador de Medidores',
    variantes: {
      con_baston: {
        label: 'Con Bastón Aéreo',
        imagen: '/storage/nuevas_instalaciones/tablero-centralizador-con-baston.png',
        descripcion_variante: 'Para acometidas aéreas con bastón centralizador de 1 pulgada.',
      },
      sin_baston: {
        label: 'Sin Bastón (Acometida Directa)',
        imagen: '/storage/nuevas_instalaciones/tablero-centralizador-sin-baston.png',
        descripcion_variante: 'Para acometidas subterráneas o empotradas en muro.',
      },
    },
    materiales: [
      { cantidad: '1 pza.', item: 'Tablero centralizador metálico homologado según cantidad de suministros.' },
      { cantidad: '8 mts.', item: 'Alambre aislado de cobre Nro. 10 AWG por cada medidor a instalar.' },
      { cantidad: '1 pza.', item: 'Interruptor termomagnético por cada medidor + 1 interruptor general de corte.' },
      { cantidad: '1 pza.', item: 'Tubo plástico conduit (PVC) de 3/4" o 1" según cantidad de cables.' },
      { cantidad: '1 pza.', item: 'Bastón de cañería galvanizada de 1" de diámetro (si aplica acometida aérea).' },
      { cantidad: 'N pzas.', item: 'Medidores certificados por cada suministro solicitado.' },
      { cantidad: '4 pzas.', item: 'Conectores de varilla de tierra.' },
      { cantidad: '1 pza.', item: 'Varilla de aterramiento de cobre de 5/8" (mínimo 0.80 m).' },
      { cantidad: '6 pzas.', item: 'Tornillos de encarne de 3/4" para anclaje del gabinete.' },
      { cantidad: '1 pza.', item: 'Conector grapa tipo Alcoa / Rack de 4 vías según acometida.' },
    ],
    requisitos_tecnicos: [
      'Gabinete centralizador ubicado en planta baja o ingreso principal.',
      'Espacio modular con compartimiento para barras generales y térmicos.',
      'Altura reglamentaria: centro del tablero a 1.40 metros del piso.',
      'Obligatorio a partir del 3er medidor en un mismo predio.',
    ],
  },
};

const activeData = computed(() => catalogo[currentTab.value]);
const currentVariantData = computed(() => activeData.value.variantes?.[activeVariant.value] ?? Object.values(activeData.value.variantes ?? {})[0]);

const checkedCount = computed(() => {
  return Object.values(checkedMaterials).filter(Boolean).length;
});

const selectTab = (tabId) => {
  currentTab.value = tabId;
  activeVariant.value = 'con_baston';
  // reset checks
  Object.keys(checkedMaterials).forEach((k) => delete checkedMaterials[k]);
};
</script>
