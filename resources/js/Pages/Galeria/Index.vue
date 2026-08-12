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
                v-if="embedFor(video.url).type === 'iframe'"
                :src="embedFor(video.url).src"
                class="w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
              <blockquote
                v-else-if="embedFor(video.url).type === 'instagram'"
                class="instagram-media w-full h-full"
                :data-instgrm-permalink="embedFor(video.url).permalink"
                data-instgrm-version="14"
                style="margin: 0"
              ></blockquote>
              <blockquote
                v-else-if="embedFor(video.url).type === 'tiktok'"
                class="tiktok-embed w-full h-full"
                :cite="video.url"
                :data-video-id="embedFor(video.url).tiktokId"
                style="margin: 0"
              ><section></section></blockquote>
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
import { onMounted, nextTick } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  videos: Array,
});

// A diferencia de YouTube/Vimeo/Facebook (que sí tienen una URL de iframe pensada para
// embeberse sola, ver los 2 primeros casos y el plugin de Facebook), Instagram y TikTok
// bloquean el iframe directo a secas -- exigen su propio script oficial de embed (`embed.js`
// de cada uno), que reemplaza un <blockquote> con los datos del post por el reproductor real.
// Por eso esos 2 casos no devuelven una URL de iframe, sino los datos que ese blockquote
// necesita (permalink completo para Instagram, ID numérico para TikTok).
const embedFor = (url) => {
  if (!url) return { type: 'link' };

  const youtubeMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  if (youtubeMatch) {
    return { type: 'iframe', src: `https://www.youtube.com/embed/${youtubeMatch[1]}` };
  }

  if (url.includes('/embed/') || url.includes('player.vimeo.com')) {
    return { type: 'iframe', src: url };
  }

  // Facebook sí tiene un iframe oficial pensado para esto (su "Video Plugin"), con la URL
  // original completa como parámetro `href` -- funciona igual para facebook.com/watch,
  // facebook.com/{página}/videos/{id} y enlaces cortos fb.watch, mientras el video sea público.
  if (url.includes('facebook.com/') || url.includes('fb.watch/')) {
    return { type: 'iframe', src: `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(url)}&show_text=false` };
  }

  // Instagram: publicación o reel público -- el script oficial busca el permalink completo
  // (con barra final) en este atributo, no arma nada a partir de un iframe src.
  const instagramMatch = url.match(/instagram\.com\/(p|reel|tv)\/([a-zA-Z0-9_-]+)/);
  if (instagramMatch) {
    return { type: 'instagram', permalink: `https://www.instagram.com/${instagramMatch[1]}/${instagramMatch[2]}/` };
  }

  // TikTok: solo funciona con la URL completa (tiktok.com/@usuario/video/{id numérico}) -- los
  // links cortos (vm.tiktok.com/...) no traen ese ID visible, son un redirect del lado de
  // TikTok que no se puede resolver desde el navegador sin otra llamada. Esos quedan con el
  // link de respaldo hasta que se pegue la URL completa.
  const tiktokMatch = url.match(/tiktok\.com\/@[\w.-]+\/video\/(\d+)/);
  if (tiktokMatch) {
    return { type: 'tiktok', tiktokId: tiktokMatch[1] };
  }

  return { type: 'link' };
};

const loadScriptOnce = (src) => new Promise((resolve) => {
  const existing = document.querySelector(`script[src="${src}"]`);
  if (existing) {
    if (existing.dataset.loaded === 'true') {
      resolve();
    } else {
      existing.addEventListener('load', resolve, { once: true });
    }
    return;
  }

  const script = document.createElement('script');
  script.src = src;
  script.async = true;
  script.onload = () => {
    script.dataset.loaded = 'true';
    resolve();
  };
  document.body.appendChild(script);
});

onMounted(async () => {
  const kinds = (props.videos ?? []).map((video) => embedFor(video.url).type);

  if (kinds.includes('instagram')) {
    await loadScriptOnce('https://www.instagram.com/embed.js');
    await nextTick();
    // El script de Instagram solo reemplaza los <blockquote> que existían en el DOM al
    // cargar -- si ya estaba cargado de una visita anterior a esta página (SPA/Inertia), hay
    // que pedirle explícitamente que revise el DOM de nuevo.
    window.instgrm?.Embeds?.process();
  }

  if (kinds.includes('tiktok')) {
    // El script de TikTok escanea el DOM una sola vez, al momento en que se ejecuta -- no
    // vuelve a mirar solo si se navega a esta página de nuevo sin recargar el documento
    // completo (típico en Inertia). Se saca y se vuelve a insertar para forzar una relectura.
    document.querySelectorAll('script[src="https://www.tiktok.com/embed.js"]').forEach((el) => el.remove());
    await nextTick();
    const script = document.createElement('script');
    script.src = 'https://www.tiktok.com/embed.js';
    script.async = true;
    document.body.appendChild(script);
  }
});
</script>
