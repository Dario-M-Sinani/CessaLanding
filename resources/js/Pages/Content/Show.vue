<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Información Institucional
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950 tracking-tight">{{ content.title }}</h1>
          <p v-if="content.summary" class="text-gray-600 text-base max-w-2xl mx-auto">{{ content.summary }}</p>
        </div>

        <img
          v-if="content.image_url && content.show_image"
          :src="imageUrlFor(content.image_url)"
          :alt="content.title"
          class="w-full max-h-[420px] object-contain rounded-2xl bg-gray-50 border border-gray-200"
        />

        <!-- Widget Interactivo de Materiales para Nuevas Instalaciones -->
        <SelectorMaterialesInstalacion v-if="content.alias === 'nuevas-instalaciones'" />

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm">
          <ContentBody :html="content.full_text" />
          <DocumentLinks v-if="documentos.length" :documents="documentos" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import ContentBody from '../../Components/ContentBody.vue';
import DocumentLinks from '../../Components/DocumentLinks.vue';
import SelectorMaterialesInstalacion from '../../Components/SelectorMaterialesInstalacion.vue';

const props = defineProps({
  content: Object,
});

const imageUrlFor = (url) => (url.startsWith('http') ? url : `/storage/${url}`);

// content.documentos: [{ titulo, archivo (ruta relativa al disco) }] -- armado desde el panel
// (ContentResource, sección "Documentos Adjuntos"), se muestran con el mismo componente de
// tarjetas que ya usa ContentBody para las tablas de links migradas del legacy.
const documentos = computed(() => (props.content.documentos ?? []).map((doc) => ({
  href: `/storage/${doc.archivo}`,
  label: doc.titulo,
  ext: (doc.archivo.split('.').pop() || '').toLowerCase(),
})));
</script>
