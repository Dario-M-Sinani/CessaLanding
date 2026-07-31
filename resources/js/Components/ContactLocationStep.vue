<template>
  <div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Número de Teléfono (fijo)</label>
        <input v-model="form.phone" type="text" maxlength="15" placeholder="Opcional" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none" />
        <p class="mt-1 text-[11px] text-gray-500">Si no tenés teléfono fijo, poné tu número de celular también en este campo.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Número de Teléfono Celular *</label>
        <input
          v-model="form.mobile_phone"
          type="text"
          maxlength="15"
          placeholder="Ej. 71234567"
          class="w-full px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
          :class="touched && !form.mobile_phone ? 'border-red-400' : 'border-gray-300'"
        />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Dirección *</label>
        <input
          v-model="form.address"
          type="text"
          maxlength="150"
          placeholder="Ej. Av. Jaime Mendoza N° 120"
          class="w-full px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
          :class="touched && !form.address ? 'border-red-400' : 'border-gray-300'"
        />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Zona *</label>
        <input
          v-model="form.zone"
          type="text"
          maxlength="150"
          placeholder="Ej. Barrio San José"
          class="w-full px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
          :class="touched && !form.zone ? 'border-red-400' : 'border-gray-300'"
        />
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Referencias de la Dirección</label>
        <textarea v-model="form.reference" rows="2" placeholder="Ej. Frente a la plaza principal, portón color rojo..." class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"></textarea>
      </div>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label class="block text-xs font-medium text-gray-700">Selecciona la ubicación de tu domicilio en el mapa</label>
        <button v-if="form.latitude" type="button" @click="clearMarker" class="text-xs font-semibold text-blue-900 hover:underline">
          Borrar marcador
        </button>
      </div>
      <div class="relative w-full h-72 rounded-xl border border-gray-300 bg-gray-100 overflow-hidden">
        <!--
          Este div se lo entregamos entero a Google Maps (mapContainer.value pasa a ser
          su contenedor real) -- Google inserta y borra nodos ahí adentro por su cuenta,
          así que Vue NO puede tener elementos propios (v-if, etc.) dentro de él: si lo
          hace, la próxima vez que Vue intente parchear ese nodo (p.ej. al pasar
          mapStatus a "ready") el nodo real ya no existe y explota con
          "Cannot read properties of null (reading 'insertBefore')". El mensaje de
          estado va afuera, superpuesto con position:absolute.
        -->
        <div ref="mapContainer" class="absolute inset-0"></div>
        <div v-if="mapStatus !== 'ready'" class="absolute inset-0 flex items-center justify-center bg-gray-100 pointer-events-none">
          <span class="text-xs text-gray-500 px-6 text-center">{{ mapStatusMessage }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { buildCessaMarkerIcon } from '../utils/mapMarkerIcon';

const props = defineProps({
  form: { type: Object, required: true }, // objeto reactive del wizard padre -- se muta directamente
  googleMapsApiKey: { type: String, default: '' },
  active: { type: Boolean, default: false }, // true mientras este paso está visible
  touched: { type: Boolean, default: false },
});

const SUCRE_CENTER = { lat: -19.0481017, lng: -65.2603843 };

const mapContainer = ref(null);
const mapStatus = ref(props.googleMapsApiKey ? 'loading' : 'unavailable');
let map = null;
let marker = null;
let googleMapsPromise = null;

const mapStatusMessage = computed(() => {
  if (mapStatus.value === 'unavailable') return 'El mapa no está disponible en este momento. Podés continuar sin marcar tu ubicación; lo confirmaremos por teléfono.';
  if (mapStatus.value === 'error') return 'No se pudo cargar el mapa. Podés continuar sin marcar tu ubicación.';
  return 'Cargando mapa...';
});

const loadGoogleMaps = (apiKey) => {
  if (window.google?.maps) return Promise.resolve();
  if (googleMapsPromise) return googleMapsPromise;

  googleMapsPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('No se pudo cargar Google Maps'));
    document.head.appendChild(script);
  });

  return googleMapsPromise;
};

const placeMarker = (position) => {
  if (marker) marker.setMap(null);
  marker = new window.google.maps.Marker({ position, map, icon: buildCessaMarkerIcon() });
};

const clearMarker = () => {
  if (marker) {
    marker.setMap(null);
    marker = null;
  }
  props.form.latitude = null;
  props.form.longitude = null;
};

const initMap = async () => {
  if (!props.googleMapsApiKey || !mapContainer.value) return;

  try {
    await loadGoogleMaps(props.googleMapsApiKey);
  } catch {
    mapStatus.value = 'error';
    return;
  }

  const center = props.form.latitude && props.form.longitude
    ? { lat: props.form.latitude, lng: props.form.longitude }
    : SUCRE_CENTER;

  map = new window.google.maps.Map(mapContainer.value, {
    center,
    zoom: 15,
    mapTypeId: window.google.maps.MapTypeId.ROADMAP,
    // El "pegman" de Street View y el selector de tipo de mapa no aportan nada acá,
    // solo confunden -- lo único que necesitamos es marcar un punto.
    streetViewControl: false,
    mapTypeControl: false,
  });

  if (props.form.latitude && props.form.longitude) {
    placeMarker(center);
  }

  map.addListener('click', (event) => {
    const position = event.latLng;
    props.form.latitude = position.lat();
    props.form.longitude = position.lng();
    placeMarker(position);
  });

  mapStatus.value = 'ready';
};

watch(() => props.active, async (isActive) => {
  if (!isActive) return;

  // El <div> del mapa se destruye cada vez que el wizard sale de este paso (v-if
  // en el padre), así que hay que volver a crear la instancia de Google Maps.
  map = null;
  marker = null;
  if (mapStatus.value !== 'unavailable') {
    mapStatus.value = 'loading';
    await nextTick();
    initMap();
  }
}, { immediate: true });
</script>
