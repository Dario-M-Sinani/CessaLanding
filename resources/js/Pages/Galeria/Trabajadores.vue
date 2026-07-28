<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Nuestra Gente
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Galería de Trabajadores</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            El equipo humano que día a día hace posible el servicio eléctrico en el Departamento de Chuquisaca.
          </p>
        </div>

        <ImageLightboxGallery :images="images" empty-text="No hay fotografías del personal publicadas por el momento." />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import ImageLightboxGallery from '../../Components/ImageLightboxGallery.vue';

defineProps({
  images: Object,
});
</script>
