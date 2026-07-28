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

        <!-- Form Card -->
        <form @submit.prevent="submitForm" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-8">

          <!-- Step 1 -->
          <div class="space-y-4">
            <h3 class="text-base font-bold text-blue-950 flex items-center space-x-2 pb-2 border-b border-gray-200">
              <span class="w-6 h-6 rounded bg-amber-500 text-blue-950 text-xs flex items-center justify-center font-bold">1</span>
              <span>Datos Personales del Solicitante</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nombre Completo *</label>
                <input
                  v-model="form.fullname"
                  type="text"
                  placeholder="Ej. Juan Pérez Mamani"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Cédula de Identidad / NIT *</label>
                <input
                  v-model="form.document_number"
                  type="text"
                  placeholder="Ej. 6543210 CB"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="ejemplo@correo.com"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Teléfono Móvil (WhatsApp) *</label>
                <input
                  v-model="form.mobile_phone"
                  type="text"
                  placeholder="Ej. 71234567"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="space-y-4">
            <h3 class="text-base font-bold text-blue-950 flex items-center space-x-2 pb-2 border-b border-gray-200">
              <span class="w-6 h-6 rounded bg-amber-500 text-blue-950 text-xs flex items-center justify-center font-bold">2</span>
              <span>Ubicación y Dirección del Inmueble</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Zona / Barrio *</label>
                <input
                  v-model="form.zone"
                  type="text"
                  placeholder="Ej. Barrio San José"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Dirección Exacta (Calle y N°) *</label>
                <input
                  v-model="form.address"
                  type="text"
                  placeholder="Ej. Av. Jaime Mendoza N° 120"
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                />
              </div>

              <div class="sm:col-span-2" v-if="requestKind !== 'OTRAS'">
                <label class="block text-xs font-medium text-gray-700 mb-1">Referencia o Descripción del Inmueble</label>
                <textarea
                  v-model="form.reference"
                  rows="2"
                  placeholder="Ej. Frente a la plaza principal, portón color rojo..."
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="space-y-4">
            <h3 class="text-base font-bold text-blue-950 flex items-center space-x-2 pb-2 border-b border-gray-200">
              <span class="w-6 h-6 rounded bg-amber-500 text-blue-950 text-xs flex items-center justify-center font-bold">3</span>
              <span>{{ stepThreeTitle }}</span>
            </h3>

            <!-- Suspensión: tipo de suspensión + motivo -->
            <div v-if="requestKind === 'SUSPENSION'" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de Suspensión *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <label
                    class="flex items-center gap-2 px-4 py-3 bg-white border rounded-xl text-sm cursor-pointer transition-colors"
                    :class="suspensionType === 'TEMPORAL' ? 'border-blue-900 ring-1 ring-blue-900' : 'border-gray-300'"
                  >
                    <input type="radio" value="TEMPORAL" v-model="suspensionType" class="accent-blue-900" />
                    <span class="font-medium text-gray-800">Suspensión Temporal</span>
                  </label>
                  <label
                    class="flex items-center gap-2 px-4 py-3 bg-white border rounded-xl text-sm cursor-pointer transition-colors"
                    :class="suspensionType === 'DEFINITIVA' ? 'border-blue-900 ring-1 ring-blue-900' : 'border-gray-300'"
                  >
                    <input type="radio" value="DEFINITIVA" v-model="suspensionType" class="accent-blue-900" />
                    <span class="font-medium text-gray-800">Suspensión Definitiva</span>
                  </label>
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Motivo de la Suspensión</label>
                <textarea
                  v-model="form.reference"
                  rows="2"
                  placeholder="Ej. Inmueble desocupado, cambio de propietario, remodelación..."
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                ></textarea>
              </div>
            </div>

            <!-- Otras solicitudes: descripción obligatoria -->
            <div v-else-if="requestKind === 'OTRAS'" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Describe tu Solicitud *</label>
                <textarea
                  v-model="form.reference"
                  rows="4"
                  placeholder="Detalla el trámite o solicitud que necesitas realizar..."
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:border-blue-900 focus:outline-none"
                  required
                ></textarea>
              </div>
            </div>

            <!-- CI upload: relevante para todos los trámites como verificación de identidad -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Carnet de Identidad (Anverso)</label>
                <input
                  type="file"
                  @change="handleFrontUpload"
                  accept="image/*,.pdf"
                  class="w-full px-3 py-2 bg-white border border-gray-300 rounded-xl text-xs text-gray-600"
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Carnet de Identidad (Reverso)</label>
                <input
                  type="file"
                  @change="handleBackUpload"
                  accept="image/*,.pdf"
                  class="w-full px-3 py-2 bg-white border border-gray-300 rounded-xl text-xs text-gray-600"
                />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-4">
            <button
              type="submit"
              :disabled="submitting"
              class="w-full py-4 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm"
            >
              <span v-if="!submitting">{{ submitLabel }}</span>
              <span v-else>Procesando Solicitud...</span>
            </button>
          </div>

        </form>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  requestKind: { type: String, default: 'NUEVA_CONEXION' },
  pageBadge: { type: String, default: 'Trámites Digitales CESSA' },
  pageTitle: { type: String, default: 'Solicitud de Nueva Conexión' },
  pageDescription: {
    type: String,
    default: 'Completa el formulario para solicitar la instalación de un nuevo medidor de energía eléctrica.',
  },
});

const submitting = ref(false);
const suspensionType = ref('TEMPORAL');

const stepThreeTitle = computed(() => {
  if (props.requestKind === 'SUSPENSION') return 'Detalle de la Suspensión';
  if (props.requestKind === 'OTRAS') return 'Detalle de tu Solicitud';
  return 'Requisitos y Cédula de Identidad';
});

const submitLabel = computed(() => {
  if (props.requestKind === 'SUSPENSION') return 'Enviar Solicitud de Suspensión';
  if (props.requestKind === 'OTRAS') return 'Enviar Solicitud';
  return 'Enviar Solicitud de Nueva Conexión';
});

const initialServiceType = () => {
  if (props.requestKind === 'SUSPENSION') return 'SUSPENSION_TEMPORAL';
  if (props.requestKind === 'OTRAS') return 'OTRAS_SOLICITUDES';
  return 'NUEVO_SUMINISTRO';
};

const form = reactive({
  fullname: '',
  email: '',
  document_number: '',
  mobile_phone: '',
  phone: '',
  address: '',
  zone: '',
  reference: '',
  user_type: 'RESIDENCIAL',
  service_type: initialServiceType(),
  url_document_front: null,
  url_document_back: null,
});

watch(suspensionType, (value) => {
  form.service_type = `SUSPENSION_${value}`;
});

const handleFrontUpload = (e) => {
  form.url_document_front = e.target.files[0];
};

const handleBackUpload = (e) => {
  form.url_document_back = e.target.files[0];
};

const submitForm = () => {
  submitting.value = true;
  router.post('/solicitudes', form, {
    forceFormData: true,
    onFinish: () => submitting.value = false,
    onSuccess: () => {
      form.fullname = '';
      form.email = '';
      form.document_number = '';
      form.mobile_phone = '';
      form.address = '';
      form.zone = '';
      form.reference = '';
    }
  });
};
</script>
