<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-5">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Servicio al Cliente
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Puntos de Cobranza</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Puede pagar su factura en cualquiera de las siguientes entidades y sucursales.
          </p>
        </div>

        <div v-if="banks && banks.length" class="space-y-3">
          <div v-for="bank in banks" :key="bank.id" class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden">
            <button
              type="button"
              class="w-full flex items-center gap-4 p-4 sm:p-5 text-left hover:bg-gray-100/60 transition-colors"
              @click="alternar(bank.id)"
            >
              <img
                v-if="bank.img_url"
                :src="bank.img_url"
                :alt="bank.name"
                class="w-14 h-14 object-contain bg-white rounded-lg border border-gray-200 shrink-0 p-1"
              />
              <span class="flex-1 font-bold text-blue-950 text-sm">{{ bank.name }}</span>
              <svg
                class="w-4 h-4 text-blue-900 shrink-0 transition-transform"
                :class="{ 'rotate-180': abiertos.has(bank.id) }"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </button>

            <div v-if="abiertos.has(bank.id)" class="border-t border-gray-200 divide-y divide-gray-200">
              <div
                v-for="punto in bank.collections_points"
                :key="punto.id"
                class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 bg-white"
              >
                <div class="flex-1 flex items-start gap-2 text-sm">
                  <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.274 1.765 11.842 11.842 0 00.984.543l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                  <span>
                    <span class="font-bold text-blue-950">{{ punto.name }}</span>
                    <span v-if="punto.address" class="text-gray-600">: {{ punto.address }}</span>
                  </span>
                </div>
                <a
                  v-if="punto.latitude && punto.longitude"
                  :href="mapaUrl(punto)"
                  target="_blank"
                  rel="noopener"
                  class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors"
                >
                  Ver en mapa
                  <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.94l-9.31 8.443a.75.75 0 00-.053 1.06z" clip-rule="evenodd" /></svg>
                </a>
              </div>

              <p v-if="!bank.collections_points || !bank.collections_points.length" class="p-4 text-xs text-gray-500 text-center">
                No hay sucursales publicadas para esta entidad todavía.
              </p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500 text-sm">
          Actualmente no hay puntos de cobranza publicados.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  banks: Array,
});

const abiertos = ref(new Set());

function alternar(bankId) {
  if (abiertos.value.has(bankId)) {
    abiertos.value.delete(bankId);
  } else {
    abiertos.value.add(bankId);
  }
  // Set no es reactivo por referencia -- forzar un Set nuevo para que Vue detecte el cambio.
  abiertos.value = new Set(abiertos.value);
}

function mapaUrl(punto) {
  return `https://www.google.com/maps/search/?api=1&query=${punto.latitude},${punto.longitude}`;
}
</script>
