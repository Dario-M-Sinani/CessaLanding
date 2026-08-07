<template>
  <AppLayout>
    <div class="py-12 sm:py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 sm:space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Contacto Directo
          </span>
          <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-blue-950 tracking-tight">Contáctanos</h1>
          <p class="text-gray-600 text-sm sm:text-base max-w-2xl mx-auto">
            Estamos a tu disposición para atender tus consultas, sugerencias o requerimientos de servicio.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8">
          <!-- Info Column -->
          <div class="md:col-span-4 space-y-6">
            <div v-if="contactInfo?.address" class="p-5 sm:p-6 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                <div class="text-sm text-gray-700">
                  <span class="block font-extrabold text-blue-900 text-base sm:text-lg tracking-wide mb-1">Oficina Central</span>
                  {{ contactInfo.address }}
                </div>
              </div>
            </div>

            <div
              v-if="mapSrc"
              class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm h-56 sm:h-64"
            >
              <StaticLocationMap :lat="contactInfo.latitude" :lng="contactInfo.longitude" :zoom="16" :google-maps-api-key="googleMapsApiKey" />
            </div>

            <div v-if="phones.length" class="p-5 sm:p-6 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm space-y-3">
              <h3 class="font-extrabold text-blue-900 text-base sm:text-lg tracking-wide">Teléfonos</h3>
              <ul class="space-y-3 text-sm text-gray-700">
                <li v-for="(phone, i) in phones" :key="i" class="flex items-center gap-3">
                  <svg class="w-4 h-4 flex-shrink-0" :class="phone.highlight ? 'text-amber-500' : 'text-gray-400'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd" /></svg>
                  <span :class="phone.highlight ? 'font-bold text-blue-900' : ''">
                    <span class="text-gray-500">{{ phone.label }}:</span>
                    <a v-if="phone.link" :href="phone.link" class="hover:underline">{{ phone.number }}</a>
                    <span v-else>{{ phone.number }}</span>
                  </span>
                </li>
              </ul>
            </div>

            <div v-if="schedules.length" class="p-5 sm:p-6 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm space-y-3">
              <h3 class="font-extrabold text-blue-900 text-base sm:text-lg tracking-wide">Horario de Atención</h3>
              <ul class="space-y-3 text-sm text-gray-700">
                <li v-for="(item, i) in schedules" :key="i" class="flex items-start gap-3">
                  <svg class="w-4 h-4 mt-0.5 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                  <span>
                    <span class="block font-semibold text-blue-950">{{ item.label }}</span>
                    <span class="block whitespace-pre-line text-gray-600">{{ item.schedule }}</span>
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Form Column -->
          <div class="md:col-span-8 bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-10 space-y-6 shadow-sm md:shadow-md">
            <div class="space-y-2">
              <h3 class="font-bold text-blue-950 text-lg sm:text-2xl">Contáctenos por Email</h3>
              <p class="text-gray-600 text-sm">
                Si desea ampliar la información sobre los servicios que ofrece CESSA o realizar algún comentario y/o sugerencia,
                llene el formulario y nos pondremos en contacto con usted, lo antes posible.
              </p>
            </div>

            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
              {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-semibold">
              {{ $page.props.flash.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nombre</label>
                  <input type="text" v-model="form.first_name" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                  <p v-if="form.errors.first_name" class="text-red-600 text-xs mt-1">{{ form.errors.first_name }}</p>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Apellido</label>
                  <input type="text" v-model="form.last_name" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                  <p v-if="form.errors.last_name" class="text-red-600 text-xs mt-1">{{ form.errors.last_name }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Correo Electrónico</label>
                  <input type="email" v-model="form.email" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                  <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Número de Celular</label>
                  <input type="tel" v-model="form.phone" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                  <p v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</p>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Asunto</label>
                <input type="text" v-model="form.subject" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                <p v-if="form.errors.subject" class="text-red-600 text-xs mt-1">{{ form.errors.subject }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mensaje o Consulta</label>
                <textarea rows="4" v-model="form.message" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required></textarea>
                <p v-if="form.errors.message" class="text-red-600 text-xs mt-1">{{ form.errors.message }}</p>
              </div>

              <button type="submit" :disabled="form.processing" class="w-full py-3.5 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-2xl text-sm shadow-sm transition-colors disabled:opacity-50">
                {{ form.processing ? 'Enviando...' : 'Enviar Mensaje' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StaticLocationMap from '../../Components/StaticLocationMap.vue';

const props = defineProps({
  contactInfo: Object,
  googleMapsApiKey: String,
});

const phones = computed(() => props.contactInfo?.phones || []);
const schedules = computed(() => props.contactInfo?.schedules || []);
const mapSrc = computed(() =>
  props.contactInfo?.show_map && props.contactInfo?.latitude && props.contactInfo?.longitude
);

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
});

const submit = () => {
  form.post('/la-compania/contacto', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
