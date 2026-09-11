<template>
  <div class="relative w-full h-full bg-gray-100">
    <!--
      Este div se lo entregamos entero a Google Maps -- no puede tener elementos
      propios de Vue adentro (ver ContactLocationStep.vue para el porqué).
    -->
    <div ref="mapContainer" class="absolute inset-0"></div>
    <div v-if="status !== 'ready'" class="absolute inset-0 flex items-center justify-center bg-gray-100 pointer-events-none">
      <span class="text-xs text-gray-500 px-6 text-center">{{ statusMessage }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { buildCessaLogoMarkerIcon } from '../utils/mapMarkerIcon';

const props = defineProps({
  lat: { type: Number, required: true },
  lng: { type: Number, required: true },
  zoom: { type: Number, default: 16 },
  googleMapsApiKey: { type: String, default: '' },
});

const mapContainer = ref(null);
const status = ref(props.googleMapsApiKey ? 'loading' : 'unavailable');
let observer = null;

const statusMessage = computed(() => {
  if (status.value === 'unavailable') return 'El mapa no está disponible en este momento.';
  if (status.value === 'error') return 'No se pudo cargar el mapa.';
  return 'Cargando mapa...';
});

const loadGoogleMaps = (apiKey) => {
  if (window.google?.maps) return Promise.resolve();
  if (window.__cessaGoogleMapsPromise) return window.__cessaGoogleMapsPromise;

  window.__cessaGoogleMapsPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('No se pudo cargar Google Maps'));
    document.head.appendChild(script);
  });

  return window.__cessaGoogleMapsPromise;
};

const initMap = async () => {
  try {
    await loadGoogleMaps(props.googleMapsApiKey);
  } catch {
    status.value = 'error';
    return;
  }

  const center = { lat: props.lat, lng: props.lng };

  const map = new window.google.maps.Map(mapContainer.value, {
    center,
    zoom: props.zoom,
    mapTypeId: window.google.maps.MapTypeId.ROADMAP,
    streetViewControl: false,
    mapTypeControl: false,
    fullscreenControl: false,
    // Solo mostramos la ubicación -- no es un selector, así que no necesita ser interactivo.
    gestureHandling: 'cooperative',
  });

  // Pin con el isotipo de CESSA para identificar la oficina sin ambigüedad
  // frente a otros puntos de interés que Google pueda mostrar cerca.
  new window.google.maps.Marker({ position: center, map, icon: buildCessaLogoMarkerIcon(0.85) });

  status.value = 'ready';
};

onMounted(() => {
  if (!props.googleMapsApiKey || !mapContainer.value) return;

  // El mapa suele quedar bien abajo en la página (ver Home.vue) -- sin esto, el
  // script de Google Maps (y sus tiles/fuentes) se descargaba en cada carga de
  // página aunque el usuario nunca llegara a ver el mapa. Se pide recién cuando
  // el contenedor está a 300px de entrar en pantalla, no cuando se monta el
  // componente.
  if (!('IntersectionObserver' in window)) {
    initMap();
    return;
  }

  observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      observer.disconnect();
      initMap();
    }
  }, { rootMargin: '300px' });

  observer.observe(mapContainer.value);
});

onBeforeUnmount(() => {
  observer?.disconnect();
});
</script>
