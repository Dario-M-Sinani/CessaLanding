<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Búsqueda en el Portal
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950 tracking-tight">
            Resultados para "{{ q }}"
          </h1>
        </div>

        <div v-if="hasResults" class="space-y-10">
          <div v-for="(items, categoria) in resultados" :key="categoria" class="space-y-4">
            <h2 class="text-xs font-bold text-blue-900 uppercase tracking-wider border-b border-gray-200 pb-2">
              {{ categoria }}
            </h2>
            <div class="space-y-3">
              <Link
                v-for="(item, idx) in items"
                :key="idx"
                :href="item.url"
                class="block p-4 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-900 rounded-xl transition-colors"
              >
                <h3 class="text-sm font-bold text-blue-950">{{ item.title }}</h3>
                <p v-if="item.snippet" class="text-xs text-gray-600 mt-1 line-clamp-2">{{ item.snippet }}</p>
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="p-12 bg-gray-50 border border-gray-200 rounded-2xl text-center space-y-3">
          <svg class="w-8 h-8 mx-auto text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
          <p class="text-gray-600 text-sm">
            <template v-if="q">No se encontraron resultados para "{{ q }}". Probá con otras palabras.</template>
            <template v-else>Escribí algo en el buscador para empezar.</template>
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  q: String,
  resultados: Object,
});

const hasResults = computed(() => Object.keys(props.resultados ?? {}).length > 0);
</script>
