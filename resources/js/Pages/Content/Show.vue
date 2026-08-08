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

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-12 shadow-sm">
          <ContentBody :html="content.full_text" />
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

const imageUrlFor = (url) => (url.startsWith('http') ? url : `/storage/${url}`);
</script>
