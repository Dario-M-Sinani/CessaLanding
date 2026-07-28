<template>
  <div>
    <div v-if="images.data && images.data.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <button
        v-for="(image, index) in images.data"
        :key="image.id"
        type="button"
        class="group block aspect-square bg-gray-50 border border-gray-200 rounded-xl overflow-hidden shadow-sm"
        @click="open(index)"
      >
        <img
          :src="image.url"
          :alt="image.title"
          loading="lazy"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
      </button>
    </div>

    <div v-else class="p-12 bg-gray-50 border border-gray-200 rounded-2xl text-center text-gray-500 text-xs shadow-sm flex flex-col items-center gap-2">
      <svg class="w-6 h-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75v-2.69l-2.22-2.219a.75.75 0 00-1.06 0l-1.91 1.909.47.47a.75.75 0 11-1.06 1.06L6.53 8.091a.75.75 0 00-1.06 0l-2.97 2.97zM12 7a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" /></svg>
      {{ emptyText }}
    </div>

    <div v-if="images.links && images.links.length > 3" class="flex flex-wrap items-center justify-center gap-2 mt-8">
      <Link
        v-for="(link, idx) in images.links"
        :key="idx"
        :href="link.url || '#'"
        v-html="link.label"
        :class="[
          'px-3.5 py-2 rounded-lg text-xs font-semibold transition-all',
          link.active ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100',
          !link.url ? 'opacity-40 pointer-events-none' : '',
        ]"
      />
    </div>

    <!-- Lightbox / Carousel -->
    <Teleport to="body">
      <div
        v-if="lightboxIndex !== null"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-blue-950/90 backdrop-blur-sm p-4"
        @click.self="close"
      >
        <button
          type="button"
          aria-label="Cerrar"
          class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
          @click="close"
        >
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>

        <button
          type="button"
          aria-label="Anterior"
          class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
          @click.stop="prev"
        >
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
        </button>

        <figure class="max-w-4xl w-full flex flex-col items-center gap-4" @click.stop>
          <img
            :src="currentImage.url"
            :alt="currentImage.title"
            class="max-h-[75vh] w-auto max-w-full rounded-xl shadow-2xl object-contain bg-black/20"
          />
          <figcaption class="text-center space-y-1">
            <p v-if="currentImage.title" class="text-white text-sm font-semibold">{{ currentImage.title }}</p>
            <p class="text-blue-200 text-xs font-mono">{{ lightboxIndex + 1 }} / {{ images.data.length }}</p>
          </figcaption>
        </figure>

        <button
          type="button"
          aria-label="Siguiente"
          class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
          @click.stop="next"
        >
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  images: Object,
  emptyText: {
    type: String,
    default: 'No hay imágenes publicadas por el momento.',
  },
});

const lightboxIndex = ref(null);

const currentImage = computed(() =>
  lightboxIndex.value !== null ? props.images.data[lightboxIndex.value] : null
);

const open = (index) => {
  lightboxIndex.value = index;
};

const close = () => {
  lightboxIndex.value = null;
};

const next = () => {
  const total = props.images.data.length;
  lightboxIndex.value = (lightboxIndex.value + 1) % total;
};

const prev = () => {
  const total = props.images.data.length;
  lightboxIndex.value = (lightboxIndex.value - 1 + total) % total;
};

const onKeydown = (e) => {
  if (lightboxIndex.value === null) return;
  if (e.key === 'Escape') close();
  if (e.key === 'ArrowRight') next();
  if (e.key === 'ArrowLeft') prev();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>
