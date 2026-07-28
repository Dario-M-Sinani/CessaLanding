<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <Link href="/noticias" class="inline-flex items-center space-x-2 text-xs font-bold text-blue-900 hover:text-blue-700 transition-colors">
          <span>←</span>
          <span>Volver a Noticias</span>
        </Link>

        <div class="space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
            Prensa y Comunicados
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950 tracking-tight leading-tight">{{ news.title }}</h1>
          <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
            <span class="inline-flex items-center gap-1.5 font-bold text-blue-900 bg-blue-50 border border-blue-100 rounded-full px-3 py-1">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" /></svg>
              {{ formatFechaPublicacion(news.created_at) }}
            </span>
            <span class="font-mono">{{ news.hits }} lecturas</span>
          </div>
        </div>

        <div class="p-6 sm:p-10 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm space-y-6">
          <p class="text-gray-800 text-sm leading-relaxed font-semibold">{{ news.summary }}</p>
          <div class="text-gray-700 text-sm leading-relaxed prose prose-sm max-w-none" v-html="news.full_text"></div>
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
  news: Object,
});
</script>
