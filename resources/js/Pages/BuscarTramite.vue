<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Seguimiento de Expediente Digital
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Consulta de Estado de Trámites</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            Verifica el avance de tus solicitudes de nueva conexión, inspección técnica o cambio de nombre.
          </p>
        </div>

        <!-- Search Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm">
          <form @submit.prevent="search" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-5">
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">N° de Solicitud / Ticket</label>
              <input
                v-model="form.nro_solicitud"
                type="text"
                placeholder="Ej. 1025"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none font-mono"
              />
            </div>

            <div class="sm:col-span-5">
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">O Documento de Identidad (CI/NIT)</label>
              <input
                v-model="form.nro_documento"
                type="text"
                placeholder="Ej. 6543210 CB"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none font-mono"
              />
            </div>

            <div class="sm:col-span-2 sm:self-end">
              <button
                type="submit"
                class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm"
              >
                Buscar
              </button>
            </div>
          </form>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
          {{ error }}
        </div>

        <!-- Result Box -->
        <div v-if="resultado" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-200 gap-4">
            <div>
              <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Expediente N° #{{ resultado.id }}</span>
              <h2 class="text-2xl font-black text-gray-900 mt-1">{{ resultado.fullname }}</h2>
              <p class="text-xs text-gray-600 mt-1">Servicio: <span class="text-gray-900 font-semibold">{{ resultado.service_type }}</span></p>
            </div>

            <div class="px-4 py-2 bg-white border border-gray-300 rounded-xl">
              <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-lg"
                :class="{
                  'bg-amber-100 text-amber-900 border border-amber-300': resultado.status === 'PENDIENTE',
                  'bg-blue-100 text-blue-900 border border-blue-300': resultado.status === 'EN_PROCESO',
                  'bg-emerald-100 text-emerald-900 border border-emerald-300': resultado.status === 'APROBADO',
                  'bg-red-100 text-red-900 border border-red-300': resultado.status === 'RECHAZADO',
                }"
              >
                {{ resultado.status }}
              </span>
            </div>
          </div>

          <!-- Progress Step Timeline -->
          <div class="space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-700">Progreso del Trámite</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
              <div class="p-4 rounded-xl bg-white border border-emerald-400 text-center space-y-1">
                <span class="text-xs font-bold text-emerald-800 block">1. Solicitado</span>
                <span class="text-[10px] text-gray-500">Formulario Recibido</span>
              </div>

              <div class="p-4 rounded-xl bg-white border border-blue-400 text-center space-y-1">
                <span class="text-xs font-bold text-blue-900 block">2. Inspección</span>
                <span class="text-[10px] text-gray-500">Evaluación de Campo</span>
              </div>

              <div class="p-4 rounded-xl bg-white border border-gray-200 text-center space-y-1 opacity-60">
                <span class="text-xs font-bold text-gray-500 block">3. Dictamen</span>
                <span class="text-[10px] text-gray-400">Aprobación Técnica</span>
              </div>

              <div class="p-4 rounded-xl bg-white border border-gray-200 text-center space-y-1 opacity-60">
                <span class="text-xs font-bold text-gray-500 block">4. Instalación</span>
                <span class="text-[10px] text-gray-400">Montaje de Medidor</span>
              </div>
            </div>
          </div>

          <!-- Observations -->
          <div class="space-y-2 pt-4 border-t border-gray-200">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-700">Observaciones e Indicaciones</h4>
            <p class="text-gray-800 bg-white p-4 rounded-xl border border-gray-200 leading-relaxed text-xs">
              {{ resultado.observation }}
            </p>
          </div>

        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  filters: Object,
  resultado: Object,
  error: String,
});

const form = reactive({
  nro_solicitud: props.filters?.nro_solicitud || '',
  nro_documento: props.filters?.nro_documento || '',
});

const search = () => {
  router.get('/buscar-tramite', {
    nro_solicitud: form.nro_solicitud,
    nro_documento: form.nro_documento,
  });
};
</script>
