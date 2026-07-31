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

        <div class="max-w-lg mx-auto">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar documento por nombre..."
              class="w-full px-4 py-3 pl-11 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
              @keyup.enter="doSearch"
            />
            <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
            <button
              v-if="search"
              @click="clearSearch"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              aria-label="Limpiar búsqueda"
            >
              <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
            </button>
          </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 space-y-4 shadow-sm">
          <div v-if="documents.data && documents.data.length" class="space-y-3">
            <div v-for="doc in documents.data" :key="doc.id" class="p-4 bg-white rounded-2xl border border-gray-200 flex items-center justify-between">
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
            <template v-if="filters.q">No se encontró ningún documento que coincida con "{{ filters.q }}".</template>
            <template v-else>Actualmente no hay documentos adjuntos. Revisa el portal periódicamente.</template>
          </div>

          <div v-if="documents.links && documents.links.length > 3" class="flex flex-wrap items-center justify-center gap-2 pt-4">
            <Link
              v-for="(link, idx) in documents.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              :class="[
                'px-3.5 py-2 rounded-lg text-sm font-semibold transition-all',
                link.active ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100',
                !link.url ? 'opacity-40 pointer-events-none' : '',
              ]"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  documents: Object,
  filters: Object,
});

const search = ref(props.filters?.q ?? '');

const doSearch = () => {
  router.get('/informacion/documentos', { q: search.value || undefined }, { preserveState: true, replace: true });
};

const clearSearch = () => {
  search.value = '';
  doSearch();
};

const docUrl = (url) => (url?.startsWith('http') ? url : `/storage/${url}`);
</script>
