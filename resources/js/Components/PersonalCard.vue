<template>
  <div class="relative bg-gray-50 border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col items-center text-center overflow-hidden">
    <img
      v-if="person.foto"
      :src="imageUrlFor(person.foto)"
      :alt="person.nombre"
      class="w-24 h-24 rounded-full object-cover border-2 border-white shadow-sm bg-gray-200"
    />
    <div
      v-else
      class="w-24 h-24 rounded-full bg-blue-100 border-2 border-white shadow-sm flex items-center justify-center text-blue-900 font-bold text-2xl"
    >
      {{ initials }}
    </div>

    <h3 class="mt-4 text-base font-bold text-blue-950">{{ person.nombre }}</h3>

    <div class="w-full mt-3 pt-3 border-t border-gray-200 space-y-1.5 text-xs">
      <div v-if="person.tipo_sangre" class="flex items-center justify-between text-gray-600">
        <span class="font-medium">Tipo de Sangre</span>
        <span class="font-mono font-bold text-gray-900">{{ person.tipo_sangre }}</span>
      </div>
      <div class="flex items-center justify-between text-gray-600">
        <span class="font-medium">C.I.</span>
        <span class="font-mono font-bold text-gray-900">{{ person.ci }}</span>
      </div>
      <div v-if="person.celular" class="flex items-center justify-between text-gray-600">
        <span class="font-medium">Celular</span>
        <span class="font-mono font-bold text-gray-900">{{ person.celular }}</span>
      </div>
    </div>

    <p v-if="person.descripcion" class="mt-3 text-xs text-gray-500 leading-relaxed">
      {{ person.descripcion }}
    </p>

    <img
      src="/img/Logo_CESSA_240x240.png"
      alt=""
      aria-hidden="true"
      class="absolute -bottom-3 -right-3 w-16 h-16 object-contain opacity-10 pointer-events-none"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

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
</script>
