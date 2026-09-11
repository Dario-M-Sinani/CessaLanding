<template>
  <div class="absolute inset-0 overflow-hidden">
    <!-- Mobile (celulares, <768px): usa imagesMobile si hay alguna curada para ese formato,
         si no cae al mismo set de escritorio. Desde md: (tablets en adelante) va como PC. -->
    <div class="absolute inset-0 md:hidden">
      <img
        v-for="{ image, index } in visibleMobileImages"
        :key="'mobile-' + image.id"
        :src="image.url"
        :alt="image.title"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-[1500ms] ease-in-out"
        :class="index === activeMobile ? 'opacity-100' : 'opacity-0'"
        :loading="index === 0 ? 'eager' : 'lazy'"
        :fetchpriority="index === 0 ? 'high' : 'auto'"
      />
    </div>

    <!-- Desktop / tablet (>=768px) -->
    <div class="absolute inset-0 hidden md:block">
      <img
        v-for="{ image, index } in visibleDesktopImages"
        :key="'desktop-' + image.id"
        :src="image.url"
        :alt="image.title"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-[1500ms] ease-in-out"
        :class="index === activeDesktop ? 'opacity-100' : 'opacity-0'"
        :loading="index === 0 ? 'eager' : 'lazy'"
        :fetchpriority="index === 0 ? 'high' : 'auto'"
      />
    </div>

    <div class="absolute inset-0" style="background: linear-gradient(rgba(0,20,40,0.6), rgba(0,20,40,0.7));"></div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  images: { type: Array, required: true },
  imagesMobile: { type: Array, default: () => [] },
  intervalMs: { type: Number, default: 8000 },
});

const mobileImages = computed(() => (props.imagesMobile.length ? props.imagesMobile : props.images));

const activeDesktop = ref(0);
const activeMobile = ref(0);

// Antes las N imágenes del set (desktop y mobile) se renderizaban todas de
// entrada, superpuestas con opacity-0/100 -- como todas son absolute inset-0
// (dentro del viewport), "loading=lazy" no las difería en nada: el navegador
// pedía las N de una sola vez en la primera carga. Ahora solo se monta la
// activa y la siguiente (precargada con margen para que el crossfade no se
// note), y recién se agrega una más al set cuando el carrusel avanza.
const loadedDesktop = ref(new Set([0, Math.min(1, props.images.length - 1)]));
const loadedMobile = ref(new Set([0, Math.min(1, mobileImages.value.length - 1)]));

// v-if y v-for no se pueden mezclar en el mismo elemento (en Vue 3 v-if se evalúa
// antes que v-for en ese caso, así que "index" no existe todavía y nada se
// renderiza) -- se filtra acá, en una computed, y el template solo itera.
const visibleDesktopImages = computed(() => props.images
    .map((image, index) => ({ image, index }))
    .filter(({ index }) => loadedDesktop.value.has(index)));

const visibleMobileImages = computed(() => mobileImages.value
    .map((image, index) => ({ image, index }))
    .filter(({ index }) => loadedMobile.value.has(index)));

let timerDesktop = null;
let timerMobile = null;

onMounted(() => {
  if (props.images.length > 1) {
    timerDesktop = setInterval(() => {
      activeDesktop.value = (activeDesktop.value + 1) % props.images.length;
      loadedDesktop.value.add((activeDesktop.value + 1) % props.images.length);
    }, props.intervalMs);
  }

  if (mobileImages.value.length > 1) {
    timerMobile = setInterval(() => {
      activeMobile.value = (activeMobile.value + 1) % mobileImages.value.length;
      loadedMobile.value.add((activeMobile.value + 1) % mobileImages.value.length);
    }, props.intervalMs);
  }
});

onBeforeUnmount(() => {
  if (timerDesktop) clearInterval(timerDesktop);
  if (timerMobile) clearInterval(timerMobile);
});
</script>
