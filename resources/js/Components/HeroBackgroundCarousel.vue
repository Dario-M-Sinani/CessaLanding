<template>
  <div class="absolute inset-0 overflow-hidden">
    <!-- Mobile (celulares, <768px): usa imagesMobile si hay alguna curada para ese formato,
         si no cae al mismo set de escritorio. Desde md: (tablets en adelante) va como PC. -->
    <div class="absolute inset-0 md:hidden">
      <img
        v-for="(image, index) in mobileImages"
        :key="'mobile-' + image.id"
        :src="image.url"
        :alt="image.title"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-[1500ms] ease-in-out"
        :class="index === activeMobile ? 'opacity-100' : 'opacity-0'"
        loading="lazy"
      />
    </div>

    <!-- Desktop / tablet (>=768px) -->
    <div class="absolute inset-0 hidden md:block">
      <img
        v-for="(image, index) in images"
        :key="'desktop-' + image.id"
        :src="image.url"
        :alt="image.title"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-[1500ms] ease-in-out"
        :class="index === activeDesktop ? 'opacity-100' : 'opacity-0'"
        loading="lazy"
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
let timerDesktop = null;
let timerMobile = null;

onMounted(() => {
  if (props.images.length > 1) {
    timerDesktop = setInterval(() => {
      activeDesktop.value = (activeDesktop.value + 1) % props.images.length;
    }, props.intervalMs);
  }

  if (mobileImages.value.length > 1) {
    timerMobile = setInterval(() => {
      activeMobile.value = (activeMobile.value + 1) % mobileImages.value.length;
    }, props.intervalMs);
  }
});

onBeforeUnmount(() => {
  if (timerDesktop) clearInterval(timerDesktop);
  if (timerMobile) clearInterval(timerMobile);
});
</script>
