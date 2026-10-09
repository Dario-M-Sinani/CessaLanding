<template>
  <Teleport to="body">
    <!-- print:hidden: el botón flotante y el panel no salen al imprimir una página -->
    <div class="print:hidden">
    <!-- Globito "¿Te ayudo?", solo antes de la primera vez que se abre -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-1"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="mostrarGlobito && !open"
        class="fixed bottom-[4.4rem] right-5 z-[95] max-w-[8rem] sm:max-w-[11rem] bg-white/90 text-blue-900 text-[10px] sm:text-xs font-medium px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl rounded-br-sm shadow-md border border-gray-200/70"
      >
        ¿Te puedo ayudar?
      </div>
    </Transition>

    <!-- Botón flotante -->
    <span
      v-if="!open"
      class="fixed bottom-5 right-5 z-[89] w-14 h-14 md:w-20 md:h-20 rounded-full bg-amber-400 blur-md md:blur-lg opacity-60 md:opacity-80 animate-pulse pointer-events-none"
      aria-hidden="true"
    ></span>
    <span
      v-if="!open"
      class="hidden md:block fixed bottom-5 right-5 z-[89] w-16 h-16 rounded-full border-2 border-amber-300 opacity-70 animate-ping pointer-events-none"
      aria-hidden="true"
    ></span>
    <button
      type="button"
      :aria-label="open ? 'Cerrar asistente' : 'Abrir asistente'"
      class="fixed bottom-5 right-5 z-[90] w-14 h-14 md:w-16 md:h-16 rounded-full bg-blue-950 hover:bg-blue-900 text-white shadow-xl flex items-center justify-center transition-all hover:scale-105"
      @click="toggle"
    >
      <svg v-if="!open" class="w-6 h-6 md:w-7 md:h-7 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .75a8.25 8.25 0 0 0-4.135 15.39c.686.398 1.115 1.008 1.291 1.664l.319 1.192a.75.75 0 0 0 .724.554h3.602a.75.75 0 0 0 .724-.554l.319-1.192c.176-.656.605-1.266 1.29-1.664A8.25 8.25 0 0 0 12 .75Z" /><path fill-rule="evenodd" d="M9.013 19.9a.75.75 0 0 1 .877-.597 11.319 11.319 0 0 0 4.22 0 .75.75 0 1 1 .28 1.473 12.819 12.819 0 0 1-4.78 0 .75.75 0 0 1-.597-.876ZM9.754 22.344a.75.75 0 0 1 .824-.668 13.682 13.682 0 0 0 2.844 0 .75.75 0 1 1 .156 1.492 15.156 15.156 0 0 1-3.156 0 .75.75 0 0 1-.668-.824Z" clip-rule="evenodd" /></svg>
      <svg v-else class="w-6 h-6 md:w-7 md:h-7" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
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
          class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 mb-3"
        />

        <!-- Agente estático: respuesta directa por intención detectada en lo que
             se escribió, antes de la lista de FAQs (ver `intenciones` más abajo) -->
        <div
          v-if="intencionDetectada"
          class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-200"
        >
          <p class="text-xs text-blue-950 leading-relaxed">{{ intencionDetectada.respuesta }}</p>
          <div v-if="intencionDetectada.acciones.length" class="flex flex-wrap gap-2 mt-2">
            <Link
              v-for="accion in intencionDetectada.acciones"
              :key="accion.href"
              :href="accion.href"
              class="text-[11px] font-bold text-blue-900 bg-white border border-blue-200 rounded-full px-3 py-1 hover:bg-blue-50 transition-colors"
              @click="open = false"
            >
              {{ accion.label }}
            </Link>
          </div>
        </div>

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
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const open = ref(false);
const mostrarGlobito = ref(false);
const busqueda = ref('');
const expandidoId = ref(null);
const cargando = ref(false);
const error = ref(false);
let globitoTimer = null;
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

// "Agente" estático: nada de IA/LLM, solo intención por palabras clave
// resuelta 100% en el navegador (ver intencionDetectada más abajo). Cubre
// preguntas frecuentes en lenguaje natural que no son búsqueda de FAQ sino
// "llevame a la acción correcta", complementando los accesos rápidos de
// arriba (que solo cubren un click directo, no una frase escrita).
const intenciones = [
  {
    id: 'emergencia',
    palabrasClave: 'no tengo luz sin luz se corto la luz se fue la luz corte no programado emergencia apagon urgente',
    respuesta: 'Si es una emergencia o un corte no programado, comunicate al 176 o al (591-4) 64-51200 (Atención al Cliente 24/7).',
    acciones: [],
  },
  {
    id: 'pagar',
    palabrasClave: 'pagar factura pago recibo como pago mi deuda planilla aviso de cobro con qr',
    respuesta: 'Podés generar tu código QR para pagar tu factura desde Consultar Deuda.',
    acciones: [{ label: 'Consultar Deuda', href: '/consulta-deuda' }],
  },
  {
    id: 'reclamo',
    palabrasClave: 'reclamo queja denuncia mal servicio problema con la atencion mala atencion',
    respuesta: 'Podés dejarnos tu reclamo o consulta desde el formulario de Contáctenos.',
    acciones: [{ label: 'Contáctenos', href: '/la-compania/contacto' }],
  },
  {
    id: 'tarifas',
    palabrasClave: 'tarifa tarifas precio del kwh cuanto cuesta la energia estructura tarifaria costo',
    respuesta: 'La estructura de tarifas vigente está detallada acá.',
    acciones: [{ label: 'Estructura Tarifaria', href: '/importante/estructura-tarifaria' }],
  },
  {
    id: 'empleo',
    palabrasClave: 'trabajo empleo vacante convocatoria postular recursos humanos',
    respuesta: 'Las convocatorias y vacantes vigentes se publican en Recursos Humanos.',
    acciones: [{ label: 'Recursos Humanos', href: '/la-compania/rrhh' }],
  },
  {
    id: 'requisitos-conexion',
    palabrasClave: 'requisitos documentos que necesito para una nueva conexion instalar medidor',
    respuesta: 'Los requisitos y documentos para trámites están acá.',
    acciones: [
      { label: 'Documentos', href: '/informacion/documentos' },
      { label: 'Nueva Conexión', href: '/nueva-conexion' },
    ],
  },
  {
    id: 'puntos-cobranza',
    palabrasClave: 'donde pago en efectivo puntos de cobranza sucursales agencias',
    respuesta: 'Estos son los puntos de cobranza habilitados para pagar en efectivo.',
    acciones: [{ label: 'Puntos de Cobranza', href: '/informacion/puntos-de-cobranza' }],
  },
  {
    id: 'suspension',
    palabrasClave: 'dar de baja suspender mi servicio cortar el servicio ya no quiero el servicio',
    respuesta: 'Para suspender tu servicio, hacé la solicitud acá.',
    acciones: [{ label: 'Suspensión de Servicio', href: '/suspension-servicio' }],
  },
  {
    id: 'sms-no-recibido',
    palabrasClave: 'no recibi no me llego no llega el sms mensaje de texto codigo de verificacion',
    respuesta: 'Si no te llegó el código por SMS: esperá un par de minutos y volvé a solicitarlo, y revisá que tu número de celular esté bien escrito. Si el problema sigue, escribinos.',
    acciones: [
      { label: 'Actualizar Datos', href: '/actualizar-datos' },
      { label: 'Contáctenos', href: '/la-compania/contacto' },
    ],
  },
];

// Las respuestas son HTML guardado desde el editor del panel (muchas migradas
// del legacy) y bastantes tienen los acentos como entidades ("categor&iacute;a"
// en vez de "categoría") en lugar del caracter real -- decodificarlas por un
// <div> temporal (innerHTML -> textContent) es lo único que también saca las
// etiquetas de forma confiable, a diferencia de un regex que no entiende
// entidades. Sin este paso, buscar "categoria" nunca encontraba nada aunque
// la palabra estuviera ahí, porque quedaba comparando contra el texto crudo
// con la entidad todavía adentro.
const decodificarHtml = (html) => {
  const contenedor = document.createElement('div');
  contenedor.innerHTML = html || '';
  return contenedor.textContent || '';
};

// ̀-ͯ son las marcas diacríticas combinantes que separa NFD --
// sacándolas después de normalizar es el truco estándar para comparar texto
// sin tildes ("útil" -> "util") sin depender de un mapa manual de acentos.
const normalizar = (texto) => decodificarHtml(texto)
  .toLowerCase()
  .normalize('NFD')
  .replace(/[̀-ͯ]/g, '');

// Distancia de Levenshtein clásica (DP de una fila) -- mide cuántas letras
// hay que cambiar/agregar/quitar para pasar de una palabra a otra. Se usa
// para tolerar errores de tipeo en la búsqueda de FAQs.
const distanciaLevenshtein = (a, b) => {
  if (!a.length) return b.length;
  if (!b.length) return a.length;

  const fila = Array.from({ length: b.length + 1 }, (_, j) => j);
  for (let i = 1; i <= a.length; i++) {
    let anterior = fila[0];
    fila[0] = i;
    for (let j = 1; j <= b.length; j++) {
      const temp = fila[j];
      fila[j] = a[i - 1] === b[j - 1]
        ? anterior
        : 1 + Math.min(anterior, fila[j], fila[j - 1]);
      anterior = temp;
    }
  }
  return fila[b.length];
};

// Palabras muy cortas ("de", "el", "luz") no toleran typos -- con esas
// pocas letras casi cualquier palabra del texto "se parece" y la búsqueda
// dejaría de filtrar nada.
const toleranciaTypo = (largo) => {
  if (largo <= 3) return 0;
  if (largo <= 6) return 1;
  return 2;
};

// Puntúa una palabra de búsqueda contra un texto: coincidencia exacta
// (substring) puntúa más que una palabra "parecida" tolerando 1-2 letras
// de diferencia, así una errata de tipeo no hace desaparecer el resultado,
// solo lo deja más abajo en el orden.
const puntuarPalabra = (palabra, texto, palabrasTexto) => {
  if (texto.includes(palabra)) return 3;

  const tolerancia = toleranciaTypo(palabra.length);
  if (!tolerancia) return 0;

  const hayParecida = palabrasTexto.some((palabraTexto) => (
    Math.abs(palabraTexto.length - palabra.length) <= tolerancia
    && distanciaLevenshtein(palabra, palabraTexto) <= tolerancia
  ));
  return hayParecida ? 1.5 : 0;
};

const resultados = computed(() => {
  const qNormalizada = normalizar(busqueda.value).trim();
  if (!qNormalizada) return faqs.value;

  const palabrasBusqueda = qNormalizada.split(/\s+/).filter(Boolean);

  return faqs.value
    .map((faq) => {
      const pregunta = normalizar(faq.question);
      const respuesta = normalizar(faq.answer);
      const palabrasPregunta = pregunta.split(/\s+/);
      const palabrasRespuesta = respuesta.split(/\s+/);

      // Todas las palabras buscadas tienen que aparecer (en la pregunta o
      // la respuesta, en cualquier orden) para que la FAQ cuente como
      // resultado -- pero el puntaje de la pregunta pesa más, así una FAQ
      // cuyo título coincide sube por encima de una que solo la menciona
      // de paso en la respuesta.
      let puntaje = 0;
      for (const palabra of palabrasBusqueda) {
        const puntajePregunta = puntuarPalabra(palabra, pregunta, palabrasPregunta);
        const puntajeRespuesta = puntuarPalabra(palabra, respuesta, palabrasRespuesta);
        const mejor = Math.max(puntajePregunta, puntajeRespuesta * 0.5);
        if (!mejor) return { faq, puntaje: 0, coincide: false };
        puntaje += mejor;
      }
      return { faq, puntaje, coincide: true };
    })
    .filter((resultado) => resultado.coincide)
    .sort((a, b) => b.puntaje - a.puntaje)
    .map((resultado) => resultado.faq);
});

// Reusa el mismo puntuarPalabra() de las FAQs contra las palabras clave de
// cada intención. Umbral >= 3 porque puntuarPalabra da exactamente 3 a una
// coincidencia exacta de una palabra real -- así una sola palabra de relleno
// (que igual da 0) nunca alcanza para disparar una intención por error.
const intencionDetectada = computed(() => {
  const qNormalizada = normalizar(busqueda.value).trim();
  if (!qNormalizada) return null;

  const palabrasBusqueda = qNormalizada.split(/\s+/).filter(Boolean);

  let mejor = null;
  for (const intencion of intenciones) {
    const textoClave = normalizar(intencion.palabrasClave);
    const palabrasClave = textoClave.split(/\s+/);

    let puntaje = 0;
    for (const palabra of palabrasBusqueda) {
      puntaje += puntuarPalabra(palabra, textoClave, palabrasClave);
    }

    if (puntaje >= 3 && (!mejor || puntaje > mejor.puntaje)) {
      mejor = { ...intencion, puntaje };
    }
  }
  return mejor;
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
  if (open.value) cargarFaqs();
};

// Globito "¿Te puedo ayudar?" y el efecto de luz del botón: se muestran de
// forma permanente (no una sola vez) a partir de los 2.5s de cargar la
// página. Solo se apagan mientras el usuario tiene el panel abierto
// (usándolo) y vuelven a aparecer apenas lo cierra -- ver los "v-if=!open"
// del template.
onMounted(() => {
  globitoTimer = setTimeout(() => {
    mostrarGlobito.value = true;
  }, 2500);
});

onBeforeUnmount(() => {
  clearTimeout(globitoTimer);
});
</script>
