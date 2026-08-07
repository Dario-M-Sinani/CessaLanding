<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Licitaciones y Contrataciones
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Procesos de Adquisición</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Convocatorias públicas y pliegos para contrataciones de bienes, obras y servicios.
          </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2">
          <Link
            href="/procesos"
            preserve-scroll
            class="px-4 py-2 rounded-xl text-xs font-bold transition-colors"
            :class="!activeGroup ? 'bg-blue-900 text-white' : 'bg-gray-50 border border-gray-200 text-gray-700 hover:border-blue-900'"
          >
            Todos
          </Link>
          <Link
            v-for="(label, key) in groupLabels"
            :key="key"
            :href="`/procesos?group=${key}`"
            preserve-scroll
            class="px-4 py-2 rounded-xl text-xs font-bold transition-colors"
            :class="activeGroup === key ? 'bg-blue-900 text-white' : 'bg-gray-50 border border-gray-200 text-gray-700 hover:border-blue-900'"
          >
            {{ label }} <span class="opacity-70">({{ groupCounts[key] ?? 0 }})</span>
          </Link>
        </div>

        <div v-if="publications.data && publications.data.length" class="space-y-6">
          <div v-for="pub in publications.data" :key="pub.id" class="p-6 sm:p-8 bg-gray-50 border border-gray-200 rounded-2xl space-y-4 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 border-b border-gray-200 pb-4">
              <span class="px-3 py-1 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-[11px] font-bold uppercase tracking-wider inline-block w-fit">
                {{ typeLabels[pub.type] ?? pub.type }}
              </span>
              <span v-if="pub.expired_date" class="text-xs font-mono text-blue-700">
                Vence: {{ pub.expired_date }}
              </span>
            </div>

            <h3 class="text-lg font-bold text-blue-950 leading-snug">{{ pub.title }}</h3>
            <p v-if="pub.description" class="text-gray-600 text-xs leading-relaxed">{{ pub.description }}</p>

            <div v-if="pub.documents && pub.documents.length" class="flex flex-wrap gap-3 pt-2">
              <a
                v-for="doc in pub.documents"
                :key="doc.id"
                :href="docUrl(doc.url)"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors"
              >
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" /></svg>
                {{ doc.title }}
              </a>
            </div>
          </div>
        </div>

        <div v-else class="bg-gray-50 border border-gray-200 rounded-2xl p-8 space-y-6 shadow-sm">
          <div class="p-6 bg-white border border-gray-200 rounded-2xl text-center text-xs text-gray-500 flex flex-col items-center gap-2">
            <svg class="w-6 h-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" /></svg>
            No hay procesos de licitación abiertos en este momento. Las convocatorias públicas se publican en cumplimiento de la normativa vigente.
          </div>
        </div>

        <div v-if="publications.links && publications.links.length > 3" class="flex flex-wrap items-center justify-center gap-2 pt-4">
          <Link
            v-for="(link, idx) in publications.links"
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

defineProps({
  publications: Object,
  typeLabels: Object,
  groupLabels: Object,
  groupCounts: Object,
  activeGroup: String,
});

const docUrl = (url) => (url?.startsWith('http') ? url : `/storage/${url}`);
</script>
