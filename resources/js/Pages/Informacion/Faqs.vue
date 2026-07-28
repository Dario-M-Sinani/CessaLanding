<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Atención al Usuario
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Preguntas Frecuentes (FAQ)</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Respuestas a las inquietudes más comunes sobre facturación, lectura de medidores y reclamos.
          </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 space-y-4 shadow-sm">
          <div v-if="faqs && faqs.length" class="space-y-4">
            <div v-for="faq in faqs" :key="faq.id" class="p-6 bg-white rounded-2xl border border-gray-200 space-y-2">
              <h3 class="font-bold text-blue-900 text-sm flex items-start gap-1.5">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                <span>{{ faq.question }}</span>
              </h3>
              <p class="text-gray-700 text-xs leading-relaxed" v-html="faq.answer"></p>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div class="p-6 bg-white rounded-2xl border border-gray-200 space-y-2">
              <h3 class="font-bold text-blue-900 text-sm flex items-start gap-1.5">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                <span>¿Dónde puedo consultar mis facturas pendientes?</span>
              </h3>
              <p class="text-gray-700 text-xs leading-relaxed">Puedes consultar tus avisos de cobranza en la sección "Consulta de Deuda" ingresando tu número de cuenta o ubicación técnica de medidor.</p>
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-200 space-y-2">
              <h3 class="font-bold text-blue-900 text-sm flex items-start gap-1.5">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                <span>¿Qué requisitos se necesitan para solicitar un nuevo medidor?</span>
              </h3>
              <p class="text-gray-700 text-xs leading-relaxed">Fotocopia de Cédula de Identidad, documento de propiedad del inmueble o contrato de alquiler, y croquis de ubicación en Sucre.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  faqs: Array,
});
</script>
