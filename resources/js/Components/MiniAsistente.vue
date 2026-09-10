<template>
  <Teleport to="body">
    <!-- Globito "¿Te ayudo?", solo antes de la primera vez que se abre -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-1"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="mostrarGlobito"
        class="fixed bottom-[4.7rem] right-5 z-[90] max-w-[11rem] bg-white text-blue-950 text-xs font-semibold px-3.5 py-2.5 rounded-2xl rounded-br-sm shadow-xl border border-gray-100"
      >
        ¿Te puedo ayudar?
      </div>
    </Transition>

    <!-- Botón flotante -->
    <span
      v-if="!open && !yaInteractuo"
      class="fixed bottom-5 right-5 z-[89] w-14 h-14 rounded-full bg-amber-400 opacity-75 animate-ping pointer-events-none"
      aria-hidden="true"
    ></span>
    <button
      type="button"
      :aria-label="open ? 'Cerrar asistente' : 'Abrir asistente'"
      class="fixed bottom-5 right-5 z-[90] w-14 h-14 rounded-full bg-blue-950 hover:bg-blue-900 text-white shadow-xl flex items-center justify-center transition-all hover:scale-105 p-2"
      @click="toggle"
    >
      <img v-if="!open" src="/img/Logo_CESSA_240x240.png" alt="" class="w-full h-full object-contain" />
      <svg v-else class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
    </button>

    <!-- Panel -->
    <div
      v-if="open"
      class="fixed bottom-24 right-5 z-[90] w-[calc(100vw-2.5rem)] max-w-sm max-h-[70vh] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
    >
      <div class="px-5 py-4 bg-blue-950 text-white shrink-0">
        <h2 class="font-extrabold text-sm">¿En qué te ayudo?</h2>
        <p class="text-[11px] text-blue-200 mt-0.5">Accesos rápidos y preguntas frecuentes</p>
      </div>

      <!-- Accesos rápidos -->
      <div class="p-4 grid grid-cols-2 gap-2 border-b border-gray-100 shrink-0">
        <Link
          v-for="accion in accesos"
          :key="accion.href"
          :href="accion.href"
          class="flex items-center justify-center text-center px-2 py-3 rounded-xl bg-gray-50 hover:bg-amber-50 border border-gray-200 hover:border-amber-300 transition-colors"
          @click="open = false"
        >
          <span class="text-[11px] font-semibold text-gray-700 leading-tight">{{ accion.label }}</span>
        </Link>
      </div>

      <!-- Buscador de FAQs -->
      <div class="p-4 flex-1 overflow-y-auto">
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar en preguntas frecuentes..."
          class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:border-blue-900 mb-3"
        />

        <div v-if="cargando" class="text-xs text-gray-500 text-center py-6">Cargando preguntas frecuentes...</div>

        <div v-else-if="error" class="text-xs text-red-600 text-center py-6">
          No se pudieron cargar las preguntas frecuentes. Intenta más tarde.
        </div>

        <div v-else-if="resultados.length" class="space-y-2">
          <div
            v-for="faq in resultados"
            :key="faq.id"
            class="border border-gray-200 rounded-xl overflow-hidden"
          >
            <button
              type="button"
              class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-blue-950 bg-gray-50 hover:bg-gray-100 transition-colors flex items-center justify-between gap-2"
              @click="expandidoId = expandidoId === faq.id ? null : faq.id"
            >
              <span>{{ faq.question }}</span>
              <svg
                class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform"
                :class="{ 'rotate-180': expandidoId === faq.id }"
                viewBox="0 0 20 20" fill="currentColor"
              ><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
            </button>
            <p
              v-if="expandidoId === faq.id"
              class="px-3.5 py-2.5 text-xs text-gray-700 leading-relaxed border-t border-gray-200"
              v-html="faq.answer"
            ></p>
          </div>
        </div>

        <div v-else class="text-center py-6 space-y-2">
          <p class="text-xs text-gray-500">
            {{ busqueda ? 'No encontramos preguntas frecuentes sobre eso.' : 'No hay preguntas frecuentes cargadas todavía.' }}
          </p>
          <a href="mailto:cessa@cessa.com.bo" class="text-xs font-semibold text-blue-900 hover:underline">
            Escribinos a cessa@cessa.com.bo
          </a>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const open = ref(false);
const yaInteractuo = ref(false);
const mostrarGlobito = ref(false);
const busqueda = ref('');
const expandidoId = ref(null);
const cargando = ref(false);
const error = ref(false);
let globitoTimer = null;
let globitoOcultarTimer = null;
const faqs = ref([]);
let cargadas = false;

const accesos = [
  { label: 'Consultar Deuda', href: '/consulta-deuda' },
  { label: 'Cortes Programados', href: '/informacion/cortes-programados' },
  { label: 'Nueva Conexión', href: '/nueva-conexion' },
  { label: 'Actualizar Datos', href: '/actualizar-datos' },
  { label: 'Calculadora', href: '/calculadora' },
  { label: 'Contáctenos', href: '/la-compania/contacto' },
];

// ̀-ͯ son las marcas diacríticas combinantes que separa NFD --
// sacándolas después de normalizar es el truco estándar para comparar texto
// sin tildes ("útil" -> "util") sin depender de un mapa manual de acentos.
const normalizar = (texto) => (texto || '')
  .toLowerCase()
  .normalize('NFD')
  .replace(/[̀-ͯ]/g, '')
  .replace(/<[^>]*>/g, ' ');

const resultados = computed(() => {
  const q = normalizar(busqueda.value).trim();
  if (!q) return faqs.value;

  return faqs.value.filter((faq) => (
    normalizar(faq.question).includes(q) || normalizar(faq.answer).includes(q)
  ));
});

const cargarFaqs = async () => {
  if (cargadas) return;
  cargando.value = true;
  error.value = false;

  try {
    const response = await fetch('/api/asistente/faqs', { headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error('respuesta no ok');
    const data = await response.json();
    faqs.value = data.faqs || [];
    cargadas = true;
  } catch (e) {
    error.value = true;
  } finally {
    cargando.value = false;
  }
};

const toggle = () => {
  open.value = !open.value;
  yaInteractuo.value = true;
  mostrarGlobito.value = false;
  if (open.value) cargarFaqs();
};

// Globito "¿Te puedo ayudar?": aparece solo una vez, a los 2.5s de cargar la
// página, y se esconde solo a los 8s -- nunca si el usuario ya abrió el
// asistente (yaInteractuo corta ambos timers).
onMounted(() => {
  globitoTimer = setTimeout(() => {
    if (!yaInteractuo.value) {
      mostrarGlobito.value = true;
      globitoOcultarTimer = setTimeout(() => {
        mostrarGlobito.value = false;
      }, 8000);
    }
  }, 2500);
});

onBeforeUnmount(() => {
  clearTimeout(globitoTimer);
  clearTimeout(globitoOcultarTimer);
});
</script>
