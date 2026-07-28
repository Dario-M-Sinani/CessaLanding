<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Header -->
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Filosofía Institucional
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Misión, Visión y Valores</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Los pilares estratégicos que guían el compromiso de CESSA con el desarrollo eléctrico de Chuquisaca.
          </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm">
          <ContentBody v-if="content" :html="content.full_text" />
          <p v-else class="text-gray-500 text-sm text-center">Contenido no disponible por el momento.</p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import ContentBody from '../../Components/ContentBody.vue';

defineProps({
  content: Object,
});
</script>
