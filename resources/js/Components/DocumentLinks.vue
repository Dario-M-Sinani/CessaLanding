<template>
  <div class="my-6 grid grid-cols-1 sm:grid-cols-2 gap-3 not-prose">
    <a
      v-for="(doc, i) in documents"
      :key="i"
      :href="doc.href"
      target="_blank"
      rel="noopener"
      class="doc-card group flex items-center gap-3 p-4 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-xl transition-all"
    >
      <span class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center" :class="iconStyle(doc.ext).bg">
        <svg class="w-6 h-6" :class="iconStyle(doc.ext).text" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" />
        </svg>
      </span>
      <span class="min-w-0">
        <span class="block text-sm font-semibold text-blue-950 group-hover:text-blue-900 leading-snug">{{ doc.label }}</span>
        <span class="block text-[11px] font-bold uppercase tracking-wider mt-0.5" :class="iconStyle(doc.ext).text">{{ doc.ext || 'Archivo' }}</span>
      </span>
    </a>
  </div>
</template>

<script setup>
defineProps({
  documents: {
    type: Array,
    required: true,
    // cada item: { href, label, ext }
  },
});

const STYLES = {
  pdf: { bg: 'bg-red-100', text: 'text-red-600' },
  xlsx: { bg: 'bg-emerald-100', text: 'text-emerald-600' },
  xls: { bg: 'bg-emerald-100', text: 'text-emerald-600' },
  doc: { bg: 'bg-blue-100', text: 'text-blue-600' },
  docx: { bg: 'bg-blue-100', text: 'text-blue-600' },
  zip: { bg: 'bg-amber-100', text: 'text-amber-600' },
};

const iconStyle = (ext) => STYLES[ext] ?? { bg: 'bg-gray-100', text: 'text-gray-500' };
</script>
