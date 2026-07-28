<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Prensa y Comunicados
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Noticias Institucionales</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Entérate de las últimas novedades, obras y comunicados oficiales de CESSA.
          </p>
        </div>

        <div v-if="newsList && newsList.length" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <Link
            v-for="item in newsList"
            :key="item.id"
            :href="`/noticias/${item.id}`"
            class="group p-6 bg-gray-50 border border-gray-200 hover:border-blue-900 rounded-2xl space-y-4 shadow-sm hover:shadow-md transition-all"
          >
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-900 bg-blue-50 border border-blue-100 rounded-full px-3 py-1">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" /></svg>
              {{ formatFechaPublicacion(item.created_at) }}
            </span>
            <h3 class="text-lg font-bold text-blue-950 leading-snug group-hover:text-blue-700 transition-colors">{{ item.title }}</h3>
            <p class="text-gray-600 text-xs leading-relaxed">{{ item.summary }}</p>
            <span class="text-xs font-bold text-blue-900">Leer más →</span>
          </Link>
        </div>

        <div v-else class="p-12 bg-gray-50 border border-gray-200 rounded-2xl text-center text-gray-500 text-xs shadow-sm flex flex-col items-center gap-2">
          <svg class="w-6 h-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 002 4.25v11.5A2.25 2.25 0 004.25 18h11.5A2.25 2.25 0 0018 15.75V4.25A2.25 2.25 0 0015.75 2H4.25zM5 6.75A.75.75 0 015.75 6h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 015 6.75zm0 3A.75.75 0 015.75 9h8.5a.75.75 0 010 1.5h-8.5A.75.75 0 015 9.75zm0 3a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5a.75.75 0 01-.75-.75z" clip-rule="evenodd" /></svg>
          No hay publicaciones recientes registradas.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatFechaPublicacion } from '../../utils/formatFecha';

defineProps({
  newsList: Array,
});
</script>
