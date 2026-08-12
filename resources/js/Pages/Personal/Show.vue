<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-xs text-gray-500 flex items-center gap-1.5">
          <Link href="/" class="hover:text-blue-900">Inicio</Link>
          <span>&gt;</span>
          <span>Personal</span>
          <span>&gt;</span>
          <span class="text-gray-700 font-medium">{{ categorias[categoriaActual] }}</span>
        </div>

        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Personal
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950 tracking-tight">{{ categorias[categoriaActual] }}</h1>
        </div>

        <div class="flex flex-wrap justify-center gap-2">
          <Link
            v-for="(label, slug) in categorias"
            :key="slug"
            :href="`/personal/${slug}`"
            :class="[
              'px-4 py-2 rounded-full text-xs font-semibold transition-all border',
              slug === categoriaActual
                ? 'bg-blue-900 border-blue-900 text-white'
                : 'bg-white border-gray-200 text-gray-700 hover:border-blue-300 hover:text-blue-900',
            ]"
          >
            {{ label }}
          </Link>
        </div>

        <div v-if="personal.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <PersonalCard v-for="person in personal" :key="person.id" :person="person" />
        </div>
        <p v-else class="text-center text-sm text-gray-500 py-12">
          Todavía no hay personal cargado en esta categoría.
        </p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PersonalCard from '../../Components/PersonalCard.vue';

defineProps({
  categoriaActual: String,
  categorias: Object,
  personal: Array,
});
</script>
