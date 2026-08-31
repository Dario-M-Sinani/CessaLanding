<template>
  <div class="relative bg-gray-50 border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col items-center text-center overflow-hidden">
    <button
      v-if="person.foto"
      type="button"
      :aria-label="`Ver foto de ${person.nombre} más de cerca`"
      class="rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-900 focus-visible:ring-offset-2"
      @click="lightboxOpen = true"
    >
      <img
        :src="imageUrlFor(person.foto)"
        :alt="person.nombre"
        class="w-24 h-24 rounded-full object-cover object-[center_20%] border-2 border-white shadow-sm bg-amber-400 cursor-zoom-in hover:opacity-90 transition-opacity"
      />
    </button>
    <div
      v-else
      class="w-24 h-24 rounded-full bg-blue-100 border-2 border-white shadow-sm flex items-center justify-center text-blue-900 font-bold text-2xl"
    >
      {{ initials }}
    </div>

    <h3 class="mt-4 text-lg font-bold text-blue-950">{{ person.nombre }}</h3>

    <div class="w-full mt-3 pt-3 border-t border-gray-200 space-y-1.5 text-sm">
      <div v-if="person.tipo_sangre" class="flex items-center justify-between text-gray-700">
        <span class="font-medium">Tipo de Sangre</span>
        <span class="font-mono font-bold text-gray-900">{{ person.tipo_sangre }}</span>
      </div>
      <div class="flex items-center justify-between text-gray-700">
        <span class="font-medium">C.I.</span>
        <span class="font-mono font-bold text-gray-900">{{ person.ci }}</span>
      </div>
      <div v-if="person.celular" class="flex items-center justify-between text-gray-700">
        <span class="font-medium">Celular</span>
        <span class="font-mono font-bold text-gray-900">{{ person.celular }}</span>
      </div>
    </div>

    <p v-if="person.descripcion" class="mt-3 text-sm text-gray-600 leading-relaxed">
      {{ person.descripcion }}
    </p>

    <img
      src="/img/Logo_CESSA_240x240.png"
      alt=""
      aria-hidden="true"
      class="absolute -bottom-3 -right-3 w-16 h-16 object-contain opacity-10 pointer-events-none"
    />

    <!-- Lightbox: una sola foto por tarjeta, sin flechas de anterior/siguiente (a diferencia
         de ImageLightboxGallery.vue, que sí navega entre varias) -- mismo patrón de Teleport +
         cerrar con Escape/click afuera. -->
    <Teleport to="body">
      <div
        v-if="lightboxOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-blue-950/90 backdrop-blur-sm p-4"
        @click.self="lightboxOpen = false"
      >
        <button
          type="button"
          aria-label="Cerrar"
          class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
          @click="lightboxOpen = false"
        >
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>

        <figure class="max-w-lg w-full flex flex-col items-center gap-4" @click.stop>
          <img
            :src="imageUrlFor(person.foto)"
            :alt="person.nombre"
            class="max-h-[75vh] w-auto max-w-full rounded-2xl shadow-2xl object-contain bg-black/20"
          />
          <figcaption class="text-center space-y-1">
            <p class="text-white text-sm font-semibold">{{ person.nombre }}</p>
            <p class="text-blue-200 text-xs font-mono">C.I. {{ person.ci }}</p>
          </figcaption>
        </figure>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  person: Object,
});

const imageUrlFor = (url) => (url.startsWith('http') ? url : `/storage/${url}`);

const initials = computed(() =>
  props.person.nombre
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
);

const lightboxOpen = ref(false);

const onKeydown = (e) => {
  if (lightboxOpen.value && e.key === 'Escape') {
    lightboxOpen.value = false;
  }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>
