<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Contacto Directo
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Contáctanos</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            Estamos a tu disposición para atender tus consultas, sugerencias o requerimientos de servicio.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
          <!-- Info Column -->
          <div class="md:col-span-5 space-y-6">
            <div class="p-6 bg-gray-50 border border-gray-200 rounded-2xl space-y-4 shadow-sm">
              <h3 class="font-bold text-blue-950 text-lg">Oficina Central</h3>
              <ul class="space-y-3 text-xs text-gray-700">
                <li class="flex items-start space-x-3">
                  <svg class="w-4 h-4 mt-0.5 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                  <span>Dirección: Calle Ayacucho Nº 254, Sucre - Bolivia</span>
                </li>
                <li class="flex items-center space-x-3">
                  <svg class="w-4 h-4 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd" /></svg>
                  <span>Central: 176 - 46214500</span>
                </li>
                <li class="flex items-center space-x-3">
                  <svg class="w-4 h-4 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2c-4.418 0-8 3.134-8 7 0 2.09 1.048 3.976 2.723 5.264-.16.982-.457 1.803-.928 2.516a.75.75 0 00.712 1.146 8.5 8.5 0 004.293-1.375A9.72 9.72 0 0010 17c4.418 0 8-3.134 8-7s-3.582-7-8-7z" clip-rule="evenodd" /></svg>
                  <span>WhatsApp: 72876668</span>
                </li>
                <li class="flex items-center space-x-3">
                  <svg class="w-4 h-4 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
                  <span class="font-bold text-blue-900">Emergencias: 176 - 46214500</span>
                </li>
                <li class="flex items-center space-x-3">
                  <svg class="w-4 h-4 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" /><path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" /></svg>
                  <span>Email: cessa@cessa.com.bo</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Form Column -->
          <div class="md:col-span-7 bg-gray-50 border border-gray-200 rounded-2xl p-8 space-y-6 shadow-sm">
            <h3 class="font-bold text-blue-950 text-xl">Envíanos un Mensaje</h3>

            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
              {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-semibold">
              {{ $page.props.flash.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nombre Completo</label>
                <input type="text" v-model="form.name" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Correo Electrónico</label>
                <input type="email" v-model="form.email" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:border-blue-900" required />
                <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
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
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const form = useForm({
  name: '',
  email: '',
  message: '',
});

const submit = () => {
  form.post('/la-compania/contacto', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
