<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Multimedia y Eventos
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Galería de Videos</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Registro audiovisual de obras de infraestructura, proyectos comunitarios y responsabilidad social.
          </p>
        </div>

        <div v-if="videos && videos.length" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div v-for="video in videos" :key="video.id" class="p-4 bg-gray-50 border border-gray-200 rounded-2xl space-y-3 shadow-sm">
            <div class="aspect-video bg-white rounded-xl overflow-hidden border border-gray-200">
              <iframe
                v-if="embedUrl(video.url)"
                :src="embedUrl(video.url)"
                class="w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
              <a v-else :href="video.url" target="_blank" class="w-full h-full flex items-center justify-center text-white">
                <svg class="w-12 h-12" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" /></svg>
              </a>
            </div>
            <h4 class="font-bold text-blue-950 text-sm">{{ video.title }}</h4>
            <p v-if="video.description" class="text-xs text-gray-600">{{ video.description }}</p>
          </div>
        </div>

        <div v-else class="p-12 bg-gray-50 border border-gray-200 rounded-2xl text-center text-gray-500 text-xs shadow-sm flex flex-col items-center gap-2">
          <svg class="w-6 h-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.25A2.25 2.25 0 014.25 2h11.5A2.25 2.25 0 0118 4.25v8.5A2.25 2.25 0 0115.75 15H4.25A2.25 2.25 0 012 12.75v-8.5zm1.5 0a.75.75 0 01.75-.75h11.5a.75.75 0 01.75.75v8.5a.75.75 0 01-.75.75H4.25a.75.75 0 01-.75-.75v-8.5zM4 18a.75.75 0 000 1.5h12a.75.75 0 000-1.5H4z" clip-rule="evenodd" /></svg>
          No hay videos publicados por el momento.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  videos: Array,
});

const embedUrl = (url) => {
  if (!url) return null;

  const youtubeMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  if (youtubeMatch) {
    return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
  }

  if (url.includes('/embed/') || url.includes('player.vimeo.com')) {
    return url;
  }

  return null;
};
</script>
