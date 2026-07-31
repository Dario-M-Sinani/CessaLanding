<template>
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1">{{ label }} *</label>

    <label
      class="relative flex flex-col items-center justify-center gap-2 w-full rounded-xl border-2 border-dashed cursor-pointer transition-colors overflow-hidden"
      :class="[heightClass, borderClass]"
      @dragover.prevent="isDragging = true"
      @dragenter.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <input
        type="file"
        accept="image/jpeg,image/png,image/jpg"
        class="hidden"
        @change="onFileSelected"
      />

      <img
        v-if="previewUrl"
        :src="previewUrl"
        class="absolute inset-0 w-full h-full object-cover"
        :class="{ 'opacity-40': status === 'uploading' }"
        alt=""
      />

      <template v-if="status === 'uploading'">
        <div class="relative z-10 w-3/4 space-y-2 text-center">
          <div class="w-full h-2 bg-white/80 rounded-full overflow-hidden">
            <div class="h-full bg-blue-900 transition-all duration-150 ease-out" :style="{ width: uploadPercent + '%' }"></div>
          </div>
          <span class="text-xs font-semibold text-blue-950 bg-white/90 px-2 py-0.5 rounded-full">Subiendo... {{ uploadPercent }}%</span>
        </div>
      </template>

      <template v-else-if="status === 'done'">
        <div class="absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow">
          <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L9 11.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
        </div>
        <div class="absolute bottom-0 inset-x-0 z-10 bg-blue-950/75 text-white text-[11px] px-2 py-1 truncate">{{ fileName }}</div>
      </template>

      <template v-else>
        <!-- Ilustraciones genéricas (sin fotos de documentos reales) -->
        <svg v-if="variant === 'front'" class="w-14 h-10 text-gray-300" viewBox="0 0 48 32" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="1" y="1" width="46" height="30" rx="3" />
          <circle cx="13" cy="16" r="6" />
          <line x1="24" y1="10" x2="41" y2="10" stroke-linecap="round" />
          <line x1="24" y1="16" x2="41" y2="16" stroke-linecap="round" />
          <line x1="24" y1="22" x2="35" y2="22" stroke-linecap="round" />
        </svg>
        <svg v-else-if="variant === 'back'" class="w-14 h-10 text-gray-300" viewBox="0 0 48 32" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="1" y="1" width="46" height="30" rx="3" />
          <rect x="5" y="6" width="38" height="6" fill="currentColor" stroke="none" />
          <line x1="5" y1="18" x2="43" y2="18" stroke-linecap="round" />
          <line x1="5" y1="23" x2="30" y2="23" stroke-linecap="round" />
        </svg>
        <svg v-else-if="variant === 'invoice'" class="w-11 h-14 text-gray-300" viewBox="0 0 32 40" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
          <path d="M4 1h24v34l-3 4-3-4-3 4-3-4-3 4-3-4-3 4-3-4Z" />
          <line x1="9" y1="9" x2="23" y2="9" stroke-linecap="round" />
          <line x1="9" y1="15" x2="23" y2="15" stroke-linecap="round" />
          <line x1="9" y1="21" x2="23" y2="21" stroke-linecap="round" />
          <line x1="9" y1="27" x2="17" y2="27" stroke-linecap="round" />
        </svg>
        <svg v-else class="w-14 h-14 text-gray-300" viewBox="0 0 44 44" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="22" cy="22" r="19" />
          <line x1="22" y1="22" x2="29" y2="15" stroke-linecap="round" />
          <circle cx="22" cy="22" r="2" fill="currentColor" stroke="none" />
          <line x1="22" y1="5" x2="22" y2="8" stroke-linecap="round" />
          <line x1="39" y1="22" x2="36" y2="22" stroke-linecap="round" />
          <line x1="5" y1="22" x2="8" y2="22" stroke-linecap="round" />
        </svg>
        <span class="text-xs text-gray-500 text-center px-4">Arrastrá y soltá o hacé click<br />JPG o PNG, máx. 5MB</span>
      </template>
    </label>

    <p v-if="status === 'error'" class="mt-1 text-xs font-semibold text-red-600">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  variant: { type: String, default: 'front' }, // 'front' | 'back' | 'invoice' | 'meter'
});

const emit = defineEmits(['update:modelValue']);

const status = ref('idle'); // idle | uploading | done | error
const uploadPercent = ref(0);
const previewUrl = ref(null);
const fileName = ref('');
const errorMessage = ref('');
const isDragging = ref(false);

const MAX_SIZE = 5 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];

// La factura y el medidor se ven mejor en un recuadro más alto (menos "cuadrado")
// que el de la Cédula de Identidad.
const heightClass = computed(() => (props.variant === 'invoice' || props.variant === 'meter' ? 'h-56' : 'h-40'));

const borderClass = computed(() => {
  if (status.value === 'error') return 'border-red-400 bg-red-50';
  if (isDragging.value) return 'border-blue-900 bg-blue-50';
  if (status.value === 'done') return 'border-emerald-400 bg-gray-50';
  return 'border-gray-300 bg-white hover:border-blue-900';
});

let progressTimer = null;

const handleFile = (file) => {
  if (!file) return;

  clearInterval(progressTimer);

  if (!ALLOWED_TYPES.includes(file.type)) {
    status.value = 'error';
    errorMessage.value = 'Formato no válido. Solo se aceptan imágenes JPG o PNG.';
    emit('update:modelValue', null);
    return;
  }

  if (file.size > MAX_SIZE) {
    status.value = 'error';
    errorMessage.value = 'La imagen supera el tamaño máximo de 5MB.';
    emit('update:modelValue', null);
    return;
  }

  fileName.value = file.name;
  status.value = 'uploading';
  uploadPercent.value = 0;

  progressTimer = setInterval(() => {
    if (uploadPercent.value < 90) {
      uploadPercent.value = Math.min(90, uploadPercent.value + Math.ceil(Math.random() * 12));
    }
  }, 90);

  const reader = new FileReader();
  reader.onload = () => {
    previewUrl.value = reader.result;
    setTimeout(() => {
      clearInterval(progressTimer);
      uploadPercent.value = 100;
      setTimeout(() => {
        status.value = 'done';
      }, 200);
    }, 350);
  };
  reader.readAsDataURL(file);

  emit('update:modelValue', file);
};

const onFileSelected = (e) => {
  handleFile(e.target.files?.[0]);
  e.target.value = '';
};

const onDrop = (e) => {
  isDragging.value = false;
  handleFile(e.dataTransfer?.files?.[0]);
};

onBeforeUnmount(() => clearInterval(progressTimer));
</script>
