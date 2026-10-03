<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-5">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Información Institucional
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Comunicados AETN</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Comunicados oficiales de la Autoridad de Electricidad y Tecnología Nuclear (AETN), publicados para conocimiento de nuestros consumidores.
          </p>
        </div>

        <div v-if="communications.data && communications.data.length" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div v-for="item in communications.data" :key="item.id" class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden shadow-sm flex flex-col">
            <img :src="item.image_url" :alt="item.title" class="w-full h-auto object-contain bg-white border-b border-gray-200" />
            <div class="p-5 space-y-2 flex-1 flex flex-col">
              <span class="text-xs font-mono text-blue-700">{{ formatFechaLarga(item.published_date) }}</span>
              <h3 class="text-lg font-bold text-blue-950 leading-snug">{{ item.title }}</h3>
              <p v-if="item.description" class="text-sm text-gray-600 leading-relaxed">{{ item.description }}</p>
              <a
                v-if="item.document_url"
                :href="item.document_url"
                target="_blank"
                class="mt-auto inline-flex items-center gap-1.5 self-start px-4 py-2 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors"
              >
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" /></svg>
                Ver documento
              </a>
            </div>
          </div>
        </div>

        <div v-else class="p-12 bg-gray-50 border border-gray-200 rounded-2xl text-center text-gray-500 text-xs shadow-sm flex flex-col items-center gap-2">
          <svg class="w-6 h-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.25A2.25 2.25 0 014.25 2h11.5A2.25 2.25 0 0118 4.25v8.5A2.25 2.25 0 0115.75 15H4.25A2.25 2.25 0 012 12.75v-8.5zm1.5 0a.75.75 0 01.75-.75h11.5a.75.75 0 01.75.75v8.5a.75.75 0 01-.75.75H4.25a.75.75 0 01-.75-.75v-8.5zM4 18a.75.75 0 000 1.5h12a.75.75 0 000-1.5H4z" clip-rule="evenodd" /></svg>
          No hay comunicados de la AETN publicados por el momento.
        </div>

        <div v-if="communications.links && communications.links.length > 3" class="flex flex-wrap items-center justify-center gap-2 pt-4">
          <Link
            v-for="(link, idx) in communications.links"
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
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatFechaLarga } from '../../utils/formatFecha';

defineProps({
  communications: Object,
});
</script>
