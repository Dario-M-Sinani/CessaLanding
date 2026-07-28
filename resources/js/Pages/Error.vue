<template>
  <AppLayout>
    <div class="py-24 bg-white min-h-screen flex items-center">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
        <div class="flex justify-center">
          <div class="w-24 h-24 rounded-3xl bg-blue-50 border border-blue-200 flex items-center justify-center">
            <svg class="w-12 h-12 text-blue-900" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>

        <div class="space-y-3">
          <p class="text-6xl font-black text-blue-950 tracking-tight">{{ status }}</p>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-950">{{ title }}</h1>
          <p class="text-gray-600 text-sm max-w-md mx-auto leading-relaxed">{{ description }}</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <Link
            href="/"
            class="px-6 py-3 bg-amber-400 hover:bg-blue-900 text-blue-950 hover:text-white font-bold rounded-xl text-sm shadow-sm transition-colors"
          >
            Volver al Inicio
          </Link>
          <Link
            href="/la-compania/contacto"
            class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors"
          >
            Contactar a CESSA
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  status: Number,
});

const messages = {
  403: {
    title: 'Acceso Denegado',
    description: 'No tienes permiso para acceder a esta página.',
  },
  404: {
    title: 'Página No Encontrada',
    description: 'La página que buscas no existe, fue movida o la dirección es incorrecta. Verifica el enlace o vuelve al inicio.',
  },
  419: {
    title: 'Página Expirada',
    description: 'Tu sesión expiró por inactividad. Vuelve al inicio e intenta nuevamente.',
  },
  500: {
    title: 'Error del Servidor',
    description: 'Ocurrió un problema inesperado de nuestro lado. Estamos trabajando para solucionarlo.',
  },
  503: {
    title: 'Servicio No Disponible',
    description: 'Estamos realizando tareas de mantenimiento. Intenta nuevamente en unos minutos.',
  },
};

const title = computed(() => messages[props.status]?.title ?? 'Ocurrió un Error');
const description = computed(() => messages[props.status]?.description ?? 'Algo no salió como esperábamos. Vuelve al inicio e intenta nuevamente.');
</script>
