<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            {{ pageBadge }}
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">{{ pageTitle }}</h1>
          <p class="text-gray-600 text-sm max-w-xl mx-auto">
            {{ pageDescription }}
          </p>
        </div>

        <!-- Alert Success -->
        <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
          {{ $page.props.flash.success }}
        </div>

        <!-- Alert Error -->
        <div v-if="$page.props.flash?.error || hasValidationErrors" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm space-y-1">
          <p class="font-semibold">{{ $page.props.flash?.error || 'Revisá los siguientes datos antes de continuar:' }}</p>
          <ul v-if="hasValidationErrors" class="list-disc list-inside">
            <li v-for="(message, field) in $page.props.errors" :key="field">{{ message }}</li>
          </ul>
        </div>

        <!-- Form Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-8">

          <!-- Stepper -->
          <div class="space-y-3">
            <div class="flex items-center">
              <template v-for="step in 3" :key="step">
                <div class="flex items-center" :class="step < 3 ? 'flex-1' : ''">
                  <div
                    class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors"
                    :class="stepCircleClass(step)"
                  >
                    <svg v-if="currentStep > step" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L9 11.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    <span v-else>{{ step }}</span>
                  </div>
                  <div v-if="step < 3" class="flex-1 h-1 mx-2 rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-full bg-blue-900 transition-all duration-500 ease-out" :style="{ width: currentStep > step ? '100%' : '0%' }"></div>
                  </div>
                </div>
              </template>
            </div>
            <div class="flex justify-between text-[11px] font-semibold text-gray-500 px-1">
              <span :class="{ 'text-blue-950': currentStep === 1 }">Datos Personales</span>
              <span :class="{ 'text-blue-950': currentStep === 2 }">Datos de la Solicitud</span>
              <span :class="{ 'text-blue-950': currentStep === 3 }">Contacto y Ubicación</span>
            </div>
          </div>

          <!-- Transición con barra de carga entre pasos -->
          <div v-if="loadingNextStep" class="py-16 flex flex-col items-center justify-center gap-4">
            <div class="w-full max-w-xs h-2 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-amber-500 transition-all duration-150 ease-out" :style="{ width: loadingPercent + '%' }"></div>
            </div>
            <span class="text-xs font-semibold text-gray-500">Preparando el siguiente paso...</span>
          </div>

          <form v-else @submit.prevent="submitForm" class="space-y-8">

            <!-- Paso 1: Datos Personales -->
            <div v-if="currentStep === 1" class="space-y-4">
              <h3 class="text-base font-bold text-blue-950">Paso 1: Datos Personales</h3>
              <p class="text-xs text-gray-500">Estimado cliente, le solicitamos por favor llenar los siguientes datos para registrar su solicitud.</p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Nombres y Apellidos *</label>
                  <input
                    v-model.trim="form.fullname"
                    type="text"
                    maxlength="180"
                    placeholder="Ej. Juan Pérez Mamani"
                    class="w-full px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                    :class="touched.step1 && !form.fullname ? 'border-red-400' : 'border-gray-300'"
                  />
                  <p v-if="touched.step1 && !form.fullname" class="mt-1 text-xs font-semibold text-red-600">Este campo es obligatorio.</p>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                  <input
                    v-model.trim="form.email"
                    type="email"
                    maxlength="150"
                    placeholder="ejemplo@correo.com"
                    class="w-full px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                    :class="touched.step1 && !isEmailValid ? 'border-red-400' : 'border-gray-300'"
                  />
                  <p v-if="touched.step1 && !isEmailValid" class="mt-1 text-xs font-semibold text-red-600">Ingresa un correo electrónico válido.</p>
                </div>
              </div>

              <div class="pt-2">
                <button type="button" @click="goNext" class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-colors shadow-sm text-sm">
                  Siguiente
                </button>
              </div>
            </div>

            <!-- Paso 2: Datos de la Solicitud -->
            <div v-else-if="currentStep === 2" class="space-y-6">
              <h3 class="text-base font-bold text-blue-950">Paso 2: Datos de la Nueva Solicitud</h3>

              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-2">Área *</label>
                  <div class="grid grid-cols-2 gap-3">
                    <label v-for="opt in areaOptions" :key="opt.value" class="flex items-center gap-2 px-4 py-3 bg-white border rounded-xl text-sm cursor-pointer transition-colors" :class="form.area === opt.value ? 'border-blue-900 ring-1 ring-blue-900' : 'border-gray-300'">
                      <input type="radio" :value="opt.value" v-model="form.area" class="accent-blue-900" />
                      <span class="font-medium text-gray-800">{{ opt.label }}</span>
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de Actividad / Categoría *</label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label v-for="opt in consumerTypeOptions" :key="opt.value" class="flex items-center gap-2 px-4 py-3 bg-white border rounded-xl text-sm cursor-pointer transition-colors" :class="form.consumer_type === opt.value ? 'border-blue-900 ring-1 ring-blue-900' : 'border-gray-300'">
                      <input type="radio" :value="opt.value" v-model="form.consumer_type" class="accent-blue-900" />
                      <span class="font-medium text-gray-800">{{ opt.label }}</span>
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de Servicio *</label>
                  <div class="grid grid-cols-2 gap-3">
                    <label v-for="opt in phaseTypeOptions" :key="opt.value" class="flex items-center gap-2 px-4 py-3 bg-white border rounded-xl text-sm cursor-pointer transition-colors" :class="form.phase_type === opt.value ? 'border-blue-900 ring-1 ring-blue-900' : 'border-gray-300'">
                      <input type="radio" :value="opt.value" v-model="form.phase_type" class="accent-blue-900" />
                      <span class="font-medium text-gray-800">{{ opt.label }}</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="space-y-4 pt-2 border-t border-gray-200">
                <h4 class="text-sm font-bold text-blue-950 pt-4">Cédula de Identidad</h4>

                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Número de Documento (C.I.) *</label>
                  <input
                    :value="form.document_number"
                    @input="onDocumentNumberInput"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="15"
                    placeholder="Solo números, máx. 15 dígitos"
                    class="w-full sm:w-72 px-4 py-3 bg-white border rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                    :class="touched.step2 && !form.document_number ? 'border-red-400' : 'border-gray-300'"
                  />
                  <p v-if="touched.step2 && !form.document_number" class="mt-1 text-xs font-semibold text-red-600">Este campo es obligatorio.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <PhotoUploadField label="Anverso de la Cédula de Identidad" variant="front" v-model="form.url_document_front" />
                  <PhotoUploadField label="Reverso de la Cédula de Identidad" variant="back" v-model="form.url_document_back" />
                </div>
                <p v-if="touched.step2 && (!form.url_document_front || !form.url_document_back)" class="text-xs font-semibold text-red-600">Debes subir ambas fotos de tu Cédula de Identidad.</p>
              </div>

              <div class="pt-2 flex gap-3">
                <button type="button" @click="goPrevious" class="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                  Anterior
                </button>
                <button type="button" @click="goNext" class="flex-1 sm:flex-none sm:px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-colors shadow-sm text-sm">
                  Siguiente
                </button>
              </div>
            </div>

            <!-- Paso 3: Información de Contacto -->
            <div v-else class="space-y-4">
              <h3 class="text-base font-bold text-blue-950">Paso 3: Información de Contacto</h3>

              <ContactLocationStep
                :form="form"
                :google-maps-api-key="googleMapsApiKey"
                :active="currentStep === 3"
                :touched="touched.step3"
              />

              <div class="pt-2 flex gap-3">
                <button type="button" @click="goPrevious" class="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                  Anterior
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="flex-1 py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-60 text-blue-950 font-extrabold rounded-xl transition-colors shadow-sm text-sm"
                >
                  <span v-if="!submitting">Enviar Solicitud de Nueva Conexión</span>
                  <span v-else>Procesando Solicitud...</span>
                </button>
              </div>
            </div>

          </form>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PhotoUploadField from '../Components/PhotoUploadField.vue';
import ContactLocationStep from '../Components/ContactLocationStep.vue';

const page = usePage();

const props = defineProps({
  pageBadge: { type: String, default: 'Nuevos Suministros' },
  pageTitle: { type: String, default: 'Solicitud de Nueva Conexión' },
  pageDescription: {
    type: String,
    default: 'Completa el formulario para solicitar la instalación de un nuevo medidor de energía eléctrica.',
  },
  googleMapsApiKey: { type: String, default: '' },
});

const currentStep = ref(1);
const submitting = ref(false);
const loadingNextStep = ref(false);
const loadingPercent = ref(0);

const touched = reactive({ step1: false, step2: false, step3: false });

const areaOptions = [
  { value: 'URBAN', label: 'Urbana' },
  { value: 'RURAL', label: 'Rural' },
];
const consumerTypeOptions = [
  { value: 'RESIDENTIAL', label: 'Domiciliaria' },
  { value: 'GENERAL', label: 'General / Comercial' },
  { value: 'INDUSTRIAL', label: 'Industrial' },
];
const phaseTypeOptions = [
  { value: 'MONOPHASE', label: 'Monofásico' },
  { value: 'TRIPHASIC', label: 'Trifásico' },
];

const form = reactive({
  fullname: '',
  email: '',
  area: 'URBAN',
  consumer_type: 'RESIDENTIAL',
  phase_type: 'MONOPHASE',
  document_number: '',
  url_document_front: null,
  url_document_back: null,
  phone: '',
  mobile_phone: '',
  address: '',
  zone: '',
  reference: '',
  latitude: null,
  longitude: null,
  service_type: 'NUEVO_SUMINISTRO',
});

const STEP_FIELDS = {
  1: ['fullname', 'email'],
  2: ['area', 'consumer_type', 'phase_type', 'document_number', 'url_document_front', 'url_document_back'],
  3: ['phone', 'mobile_phone', 'address', 'zone', 'reference', 'latitude', 'longitude'],
};

const hasValidationErrors = computed(() => Object.keys(page.props.errors ?? {}).length > 0);

// Si el servidor rechaza el envío final (paso 3), saltamos al primer paso
// que tenga un campo inválido para que el error no quede invisible.
watch(() => page.props.errors, (errors) => {
  const fields = Object.keys(errors ?? {});
  if (fields.length === 0) return;
  const earliestStep = Object.entries(STEP_FIELDS).find(([, stepFields]) => fields.some((f) => stepFields.includes(f)));
  if (earliestStep) currentStep.value = Number(earliestStep[0]);
});

const isEmailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email));
const step1Valid = computed(() => !!form.fullname && isEmailValid.value);
const step2Valid = computed(() => !!form.area && !!form.consumer_type && !!form.phase_type && !!form.document_number && !!form.url_document_front && !!form.url_document_back);

const onDocumentNumberInput = (e) => {
  form.document_number = e.target.value.replace(/\D/g, '').slice(0, 15);
};

const stepCircleClass = (step) => {
  if (currentStep.value > step) return 'bg-blue-900 border-blue-900 text-white';
  if (currentStep.value === step) return 'bg-amber-500 border-amber-500 text-blue-950';
  return 'bg-white border-gray-300 text-gray-400';
};

const animateTransition = () => new Promise((resolve) => {
  loadingNextStep.value = true;
  loadingPercent.value = 0;
  const timer = setInterval(() => {
    loadingPercent.value = Math.min(100, loadingPercent.value + Math.ceil(Math.random() * 25));
    if (loadingPercent.value >= 100) {
      clearInterval(timer);
      setTimeout(() => {
        loadingNextStep.value = false;
        resolve();
      }, 150);
    }
  }, 110);
});

const goNext = async () => {
  if (currentStep.value === 1) {
    touched.step1 = true;
    if (!step1Valid.value) return;
  } else if (currentStep.value === 2) {
    touched.step2 = true;
    if (!step2Valid.value) return;
  }

  await animateTransition();
  currentStep.value += 1;
};

const goPrevious = () => {
  currentStep.value = Math.max(1, currentStep.value - 1);
};

const clearMarker = () => {
  form.latitude = null;
  form.longitude = null;
};

const submitForm = () => {
  // Guarda re-entrante: ":disabled" en el botón no alcanza solo, porque Vue recién
  // pinta ese atributo en el DOM real en el próximo tick -- un doble click/doble tap
  // (muy común en celular) puede disparar dos eventos de click antes de eso y mandar
  // la solicitud duplicada.
  if (submitting.value) return;

  touched.step3 = true;
  if (!form.mobile_phone || !form.address || !form.zone) return;

  submitting.value = true;
  router.post('/solicitudes', form, {
    forceFormData: true,
    onFinish: () => submitting.value = false,
    onSuccess: () => {
      currentStep.value = 1;
      touched.step1 = false;
      touched.step2 = false;
      touched.step3 = false;
      form.fullname = '';
      form.email = '';
      form.area = 'URBAN';
      form.consumer_type = 'RESIDENTIAL';
      form.phase_type = 'MONOPHASE';
      form.document_number = '';
      form.url_document_front = null;
      form.url_document_back = null;
      form.phone = '';
      form.mobile_phone = '';
      form.address = '';
      form.zone = '';
      form.reference = '';
      clearMarker();
    },
  });
};
</script>
