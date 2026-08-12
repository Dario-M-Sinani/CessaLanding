<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-blue-950/70 backdrop-blur-sm"
      @click.self="close"
    >
      <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <button
          type="button"
          aria-label="Cerrar"
          class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-blue-950 flex items-center justify-center shadow-md transition-colors"
          @click="close"
        >
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>

        <div class="p-6 space-y-3 overflow-y-auto">
          <span class="inline-block px-3 py-1 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Comunicado Institucional
          </span>
          <h2 class="text-xl font-extrabold text-blue-950 leading-snug">{{ news.title }}</h2>
          <p class="text-sm text-gray-700 leading-relaxed">{{ news.summary }}</p>

          <img
            v-if="imageSrc"
            :src="imageSrc"
            :alt="news.title"
            class="w-full max-h-96 object-contain rounded-xl bg-gray-50 border border-gray-100"
          />
        </div>

        <div class="px-6 pb-6 flex items-center justify-between gap-3">
          <button
            type="button"
            class="text-xs font-semibold text-gray-500 hover:text-gray-700 transition-colors"
            @click="close"
          >
            Cerrar
          </button>
          <Link
            :href="`/noticias/${news.id}`"
            class="px-5 py-2.5 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors shadow-sm"
          >
            Seguir leyendo
          </Link>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  news: Object,
});

// A pedido del usuario: el modal se muestra en cada carga de la página,
// sin recordar que ya se cerró (antes se guardaba en sessionStorage y no
// volvía a aparecer hasta cerrar la pestaña).
const visible = ref(!!props.news);

const imageSrc = computed(() => {
  const url = props.news?.image_url;
  if (!url) return null;
  return url.startsWith('http') ? url : `/storage/${url}`;
});

const close = () => {
  visible.value = false;
};
</script>
