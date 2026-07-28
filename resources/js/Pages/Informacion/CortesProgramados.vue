<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Información de Servicio
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Cortes Programados de Energía</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Mantenimientos preventivos y mejoras en la red de distribución eléctrica.
          </p>
        </div>

        <div class="space-y-6">
          <div v-if="outages && outages.length" class="space-y-5">
            <div v-for="outage in outages" :key="outage.id" class="bg-gray-50 border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
              <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-blue-950 text-white px-5 py-4">
                <div class="flex items-center gap-3">
                  <svg class="w-6 h-6 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" /></svg>
                  <span class="text-sm sm:text-base font-extrabold">{{ formatFechaLarga(outage.execution_date) }}</span>
                </div>
                <div class="flex items-center gap-2 sm:ml-auto bg-amber-400 text-blue-950 px-4 py-1.5 rounded-full font-mono font-extrabold text-sm sm:text-base whitespace-nowrap">
                  <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                  <span>{{ formatHora(outage.start_time) }} a {{ formatHora(outage.finish_time) }}</span>
                </div>
              </div>
              <div class="p-5 space-y-3">
                <h3 class="text-base font-bold text-blue-950">Motivo: {{ outage.reason }}</h3>
                <p class="text-xs text-gray-600 flex items-start gap-1.5">
                  <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                  <span>Zonas afectadas: <span class="text-gray-800">{{ outage.location }}</span></span>
                </p>
              </div>
            </div>
          </div>

          <div v-else class="p-8 bg-gray-50 border border-gray-200 rounded-2xl text-center space-y-3 shadow-sm">
            <svg class="w-8 h-8 mx-auto text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <h3 class="text-lg font-bold text-blue-950">No hay cortes programados a la fecha</h3>
            <p class="text-gray-600 text-xs">La red eléctrica de CESSA opera con normalidad sin trabajos de mantenimiento previstos.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatFechaLarga, formatHora } from '../../utils/formatFecha';

defineProps({
  outages: Array,
});
</script>
