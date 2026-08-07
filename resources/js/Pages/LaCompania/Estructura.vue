<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Organigrama Institucional
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Estructura Organizacional</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            La organización administrativa y operativa de la Compañía Eléctrica Sucre S.A.
          </p>
        </div>

        <div
          v-if="content && content.show_org_chart && (orgChartSrc || peiSrc)"
          class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm space-y-6"
        >
          <img
            v-if="orgChartSrc"
            :src="orgChartSrc"
            alt="Organigrama CESSA"
            class="w-full max-h-[600px] object-contain rounded-xl bg-white border border-gray-200"
          />
          <a
            v-if="peiSrc"
            :href="peiSrc"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-xs transition-colors shadow-sm"
          >
            Descargar Plan Estratégico Institucional (PEI)
          </a>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm space-y-8">
          <template v-if="content">
            <h2 v-if="content.title" class="text-2xl font-extrabold text-blue-950 tracking-tight">{{ content.title }}</h2>
            <img
              v-if="content.show_image && content.image_url"
              :src="imageSrc"
              alt="Plantel Ejecutivo CESSA"
              class="w-full max-h-[600px] object-contain rounded-xl bg-white border border-gray-200"
            />
            <ContentBody :html="content.full_text" />
          </template>
          <p v-else class="text-gray-500 text-sm text-center">Contenido no disponible por el momento.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import ContentBody from '../../Components/ContentBody.vue';

const props = defineProps({
  content: Object,
});

const resolveUrl = (url) => {
  if (!url) return null;
  if (url.startsWith('http') || url.startsWith('/')) return url;
  return `/storage/${url}`;
};

const imageSrc = computed(() => resolveUrl(props.content?.image_url));
const orgChartSrc = computed(() => resolveUrl(props.content?.org_chart_image));
const peiSrc = computed(() => resolveUrl(props.content?.pei_document));
</script>
