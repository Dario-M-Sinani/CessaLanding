<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Transparencia Institucional
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Documentos y Resoluciones</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Reglamentos, estados financieros y memorias institucionales descargables.
          </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 space-y-4 shadow-sm">
          <div v-if="documents && documents.length" class="space-y-3">
            <div v-for="doc in documents" :key="doc.id" class="p-4 bg-white rounded-2xl border border-gray-200 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-blue-900 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" /></svg>
                <span class="text-xs font-bold text-blue-950">{{ doc.title }}</span>
              </div>
              <a :href="docUrl(doc.url)" target="_blank" class="px-4 py-2 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors">
                Descargar PDF
              </a>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-500 text-xs">
            Actualmente no hay documentos adjuntos. Revisa el portal periódicamente.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  documents: Array,
});

const docUrl = (url) => (url?.startsWith('http') ? url : `/storage/${url}`);
</script>
