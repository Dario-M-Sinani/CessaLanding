<template>
  <div class="relative my-4 select-none">
    <div class="relative rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden">
      <img
        :src="images[active].src"
        :alt="images[active].alt"
        class="w-full max-h-[520px] object-contain mx-auto"
      />

      <button
        v-if="images.length > 1"
        type="button"
        @click="prev"
        class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white shadow-md border border-gray-200 flex items-center justify-center text-blue-900 transition-colors"
        aria-label="Anterior"
      >
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd" /></svg>
      </button>
      <button
        v-if="images.length > 1"
        type="button"
        @click="next"
        class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white shadow-md border border-gray-200 flex items-center justify-center text-blue-900 transition-colors"
        aria-label="Siguiente"
      >
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06 0z" clip-rule="evenodd" /></svg>
      </button>
    </div>

    <div v-if="images.length > 1" class="flex items-center justify-center gap-2 mt-3">
      <button
        v-for="(img, i) in images"
        :key="i"
        type="button"
        @click="active = i"
        class="h-2 rounded-full transition-all"
        :class="i === active ? 'w-6 bg-blue-900' : 'w-2 bg-gray-300 hover:bg-gray-400'"
        :aria-label="`Ir a la imagen ${i + 1}`"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  images: { type: Array, required: true }, // [{ src, alt }]
});

const active = ref(0);

const next = () => {
  active.value = (active.value + 1) % props.images.length;
};

const prev = () => {
  active.value = (active.value - 1 + props.images.length) % props.images.length;
};
</script>
