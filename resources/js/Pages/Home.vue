<template>
  <AppLayout>
    <!-- Hero Banner Section -->
    <!-- El Navbar es fixed en esta página (ver Navbar.vue), así que compensamos
         con el alto real medido vía --nav-height para que el contenido no quede tapado.
         min-h-screen: para que la imagen ocupe casi toda la pantalla al cargar y
         "Trámites en Línea" quede debajo del scroll inicial, dándole protagonismo. -->
    <section
      class="relative overflow-hidden min-h-screen flex items-center pb-16 lg:pb-24"
      style="padding-top: calc(var(--nav-height, 120px) + 2.5rem);"
      @mousemove="onHeroMouseMove"
      @mouseleave="resetMagnet"
    >
      <HeroBackgroundCarousel
        v-if="galleryHighlights && galleryHighlights.length"
        :images="galleryHighlights"
        :images-mobile="galleryHighlightsMobile ?? []"
        :interval-ms="8000"
      />
      <div
        v-else
        class="absolute inset-0 bg-cover bg-center"
        style="background-image: linear-gradient(rgba(0,20,40,0.6), rgba(0,20,40,0.7)), url('/img/casa-libertad-hero.webp');"
      ></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 w-full">

        <!-- Headline -->
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight">
          Compañía Eléctrica Sucre <span class="text-amber-400">todo tu servicio, un solo lugar</span>
        </h1>

        <p class="text-blue-100 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
          Consulta tus avisos de cobranza en segundos, simula tus costos tarifarios mensuales y gestiona tus nuevos suministros eléctricos sin filas.
        </p>

        <!-- CTA: sin campo de texto -- un foco/halo sutil se enciende alrededor del
             botón cuando el cursor se acerca, sin moverlo del lugar. -->
        <div class="pt-4 flex flex-col items-center gap-4">
          <button
            ref="consultarBtn"
            @click="buscarDeuda"
            :style="focusStyle"
            class="px-10 py-4 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-2xl shadow-xl text-base sm:text-lg tracking-wide transition-shadow duration-200 ease-out"
          >
            Consultar Deuda
          </button>
          <div class="flex items-center justify-center space-x-4 text-xs text-blue-100 font-medium">
            <span>✓ Consulta gratuita 24/7</span>
            <span>•</span>
            <span>✓ Detalle de avisos</span>
            <span>•</span>
            <span>✓ Pago por QR</span>
          </div>
        </div>

      </div>
    </section>

    <!-- Solicitar Servicios Section -->
    <section class="py-16 lg:py-20 bg-blue-950 relative overflow-hidden">
      <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <div class="text-center space-y-3 max-w-2xl mx-auto">
          <span class="px-4 py-1.5 bg-amber-400/10 border border-amber-400/30 text-amber-400 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
            Trámites en Línea
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            Ahora puede solicitar servicios desde nuestro sitio web
          </h2>
          <p class="text-blue-200 text-base">
            Gestiona tus trámites de conexión, suspensión y otras solicitudes sin necesidad de acercarte a nuestras oficinas.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Card 1: Nueva Conexión -->
          <Link
            href="/nueva-conexion"
            class="group bg-white rounded-2xl p-7 space-y-5 shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col"
          >
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-900 group-hover:bg-blue-900 group-hover:text-white transition-colors">
              <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.983 1.907a.75.75 0 00-1.292-.657l-8.5 9.5A.75.75 0 002.75 12h4.803l-1.545 6.093a.75.75 0 001.292.657l8.5-9.5A.75.75 0 0015.25 8h-4.803l1.536-6.093z" clip-rule="evenodd" /></svg>
            </div>
            <div class="space-y-2 grow">
              <h3 class="text-lg font-bold text-blue-950">Nueva Conexión</h3>
              <p class="text-gray-600 text-sm leading-relaxed">Registre una nueva solicitud de servicio eléctrico para su inmueble.</p>
            </div>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-blue-900">
              <span>Saber más</span>
              <span class="group-hover:translate-x-1 transition-transform">→</span>
            </div>
          </Link>

          <!-- Card 2: Suspensión Temporal o Definitiva -->
          <Link
            href="/suspension-servicio"
            class="group bg-white rounded-2xl p-7 space-y-5 shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col"
          >
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-900 group-hover:bg-blue-900 group-hover:text-white transition-colors">
              <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 01.75.75v8a.75.75 0 01-1.5 0V6a.75.75 0 01.75-.75zm6.5 0a.75.75 0 01.75.75v8a.75.75 0 01-1.5 0V6a.75.75 0 01.75-.75z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-1.5a6.5 6.5 0 100-13 6.5 6.5 0 000 13z" clip-rule="evenodd" /></svg>
            </div>
            <div class="space-y-2 grow">
              <h3 class="text-lg font-bold text-blue-950">Suspensión Temporal o Definitiva</h3>
              <p class="text-gray-600 text-sm leading-relaxed">Solicite la suspensión temporal o definitiva de su servicio eléctrico.</p>
            </div>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-blue-900">
              <span>Saber más</span>
              <span class="group-hover:translate-x-1 transition-transform">→</span>
            </div>
          </Link>

          <!-- Card 3: Otras Solicitudes -->
          <Link
            href="/otras-solicitudes"
            class="group bg-white rounded-2xl p-7 space-y-5 shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col"
          >
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-900 group-hover:bg-blue-900 group-hover:text-white transition-colors">
              <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 002 4.25v11.5A2.25 2.25 0 004.25 18h11.5A2.25 2.25 0 0018 15.75V4.25A2.25 2.25 0 0015.75 2H4.25zM5 6.75A.75.75 0 015.75 6h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 015 6.75zm0 3A.75.75 0 015.75 9h8.5a.75.75 0 010 1.5h-8.5A.75.75 0 015 9.75zm0 3a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5a.75.75 0 01-.75-.75z" clip-rule="evenodd" /></svg>
            </div>
            <div class="space-y-2 grow">
              <h3 class="text-lg font-bold text-blue-950">Otras Solicitudes</h3>
              <p class="text-gray-600 text-sm leading-relaxed">Registre otros tipos de solicitudes o trámites relacionados a su servicio.</p>
            </div>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-blue-900">
              <span>Saber más</span>
              <span class="group-hover:translate-x-1 transition-transform">→</span>
            </div>
          </Link>

        </div>
      </div>
    </section>

    <!-- Services Grid Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <div class="text-center space-y-5">
          <h2 class="text-3xl font-extrabold text-blue-950 tracking-tight">Servicios Virtuales Disponibles</h2>
          <p class="text-gray-600 text-base max-w-xl mx-auto">
            Accede a todas las operaciones comerciales directamente desde tu dispositivo.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

          <!-- Card 1: Consulta Deuda -->
          <Link
            href="/consulta-deuda"
            class="group p-8 bg-gray-50 border border-gray-200 hover:border-blue-900 rounded-2xl transition-all duration-300 hover:shadow-xl space-y-4 flex flex-col justify-between"
          >
            <div class="space-y-3">
              <span class="text-xs font-bold text-amber-600 uppercase tracking-widest block">Servicio Comercial</span>
              <h3 class="text-xl font-bold text-blue-950 group-hover:text-blue-700 transition-colors">
                Consulta de Avisos y Deuda
              </h3>
              <p class="text-gray-600 text-sm leading-relaxed">
                Ingresa tu código de cliente o ubicación de medidor para revisar tus facturas emitidas, fechas de vencimiento y monto total.
              </p>
            </div>
            
            <div class="pt-4 border-t border-gray-200 flex items-center justify-between text-xs font-bold text-blue-900">
              <span>Consultar estado de cuenta</span>
              <span>→</span>
            </div>
          </Link>

          <!-- Card 2: Calculadora -->
          <Link
            href="/calculadora"
            class="group p-8 bg-gray-50 border border-gray-200 hover:border-blue-900 rounded-2xl transition-all duration-300 hover:shadow-xl space-y-4 flex flex-col justify-between"
          >
            <div class="space-y-3">
              <span class="text-xs font-bold text-amber-600 uppercase tracking-widest block">Simulador Tarifario</span>
              <h3 class="text-xl font-bold text-blue-950 group-hover:text-blue-700 transition-colors">
                Calculadora de Consumo
              </h3>
              <p class="text-gray-600 text-sm leading-relaxed">
                Estima el costo proyectado de tu aviso mensual ingresando los kilovatios-hora consumidos en tu hogar o negocio.
              </p>
            </div>

            <div class="pt-4 border-t border-gray-200 flex items-center justify-between text-xs font-bold text-blue-900">
              <span>Calcular consumo proyectado</span>
              <span>→</span>
            </div>
          </Link>

        </div>

      </div>
    </section>

    <!-- Cortes Programados Preview -->
    <section class="py-16 bg-gray-50 border-y border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
          <div class="space-y-3">
            <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
              Información de Servicio
            </span>
            <h2 class="text-3xl font-extrabold text-blue-950 tracking-tight">Cortes Programados</h2>
          </div>
          <Link href="/informacion/cortes-programados" class="text-xs font-bold text-blue-900 hover:text-blue-700 whitespace-nowrap">
            Ver todos los cortes →
          </Link>
        </div>

        <!-- Cortes de emergencia: imprevistos (choque de poste, falla, clima). En rojo mientras
             están en atención, en verde unas horas después de restablecido el servicio. -->
        <div v-if="emergencies && emergencies.length" class="space-y-4">
          <div
            v-for="emergency in emergencies"
            :key="emergency.id"
            :class="[
              'rounded-2xl shadow-sm overflow-hidden border-2',
              emergency.restored_at ? 'bg-emerald-50 border-emerald-600' : 'bg-red-50 border-red-600',
            ]"
            role="alert"
          >
            <div
              :class="[
                'flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-3.5 text-white',
                emergency.restored_at ? 'bg-emerald-700' : 'bg-red-700',
              ]"
            >
              <div class="flex items-center gap-3">
                <span v-if="!emergency.restored_at" class="relative flex h-3 w-3 shrink-0">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                </span>
                <svg v-else class="w-6 h-6 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                <span class="text-lg font-extrabold uppercase tracking-wide">
                  {{ emergency.restored_at ? 'Servicio restablecido' : 'Corte de emergencia' }}
                </span>
              </div>
              <span class="sm:ml-auto text-sm font-semibold">
                {{ formatFechaLarga(emergency.execution_date) }} · desde las {{ formatHora(emergency.start_time) }}
              </span>
            </div>
            <div class="p-5 space-y-3">
              <h3 :class="['text-lg font-bold', emergency.restored_at ? 'text-emerald-900' : 'text-red-900']">
                Causa: {{ emergency.reason }}
              </h3>
              <p class="text-base text-gray-700 leading-relaxed">
                <span class="font-semibold">Zonas afectadas:</span> {{ emergency.location }}
              </p>
              <p v-if="emergency.affected_institutions" class="text-base text-gray-700 leading-relaxed">
                <span class="font-semibold">Instituciones afectadas:</span> {{ emergency.affected_institutions }}
              </p>
              <p v-if="emergency.restored_at" class="text-sm font-semibold text-emerald-800">
                El suministro fue restablecido a las {{ formatHoraTimestamp(emergency.restored_at) }}. Gracias por su comprensión.
              </p>
              <p v-else class="text-sm font-semibold text-red-800">
                CESSA ya tiene conocimiento de este corte y nuestro personal técnico está trabajando para restablecer el servicio<template v-if="emergency.finish_time"> (reposición estimada: {{ formatHora(emergency.finish_time) }})</template>.
              </p>
            </div>
          </div>
        </div>

        <div v-if="outages && outages.length" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <button
            v-for="outage in outages"
            :key="outage.id"
            type="button"
            @click="selectedOutage = outage"
            class="text-left bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hover:border-blue-900 hover:shadow-md transition-all cursor-pointer"
          >
            <div class="bg-blue-950 text-white px-5 py-3.5 space-y-2">
              <span class="block text-lg font-extrabold leading-snug">{{ formatFechaLarga(outage.execution_date) }}</span>
              <span class="inline-flex items-center gap-2 bg-amber-400 text-blue-950 px-3.5 py-1.5 rounded-full font-mono font-extrabold text-base">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                {{ formatHora(outage.start_time) }} - {{ formatHora(outage.finish_time) }}
              </span>
            </div>
            <p class="p-5 text-sm text-gray-600 leading-relaxed line-clamp-3">{{ outage.location }}</p>
          </button>
        </div>
        <div v-else class="p-8 bg-white border border-gray-200 rounded-2xl text-center text-base text-emerald-800 font-semibold flex items-center justify-center gap-2">
          <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
          No hay cortes programados en los próximos días.
        </div>
      </div>
    </section>

    <!-- Modal de Corte Programado (detalle completo, sin navegar a "todos los cortes") -->
    <Teleport to="body">
      <div
        v-if="selectedOutage"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-blue-950/70 backdrop-blur-sm"
        @click.self="selectedOutage = null"
      >
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
          <button
            type="button"
            aria-label="Cerrar"
            class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-blue-950 flex items-center justify-center shadow-md transition-colors"
            @click="selectedOutage = null"
          >
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
          </button>

          <div class="bg-blue-950 text-white px-6 py-5 space-y-3">
            <div class="flex items-center gap-3">
              <svg class="w-6 h-6 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" /></svg>
              <span class="text-lg font-extrabold leading-snug">{{ formatFechaLarga(selectedOutage.execution_date) }}</span>
            </div>
            <span class="inline-flex items-center gap-2 bg-amber-400 text-blue-950 px-3.5 py-1.5 rounded-full font-mono font-extrabold text-base">
              <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
              {{ formatHora(selectedOutage.start_time) }} a {{ formatHora(selectedOutage.finish_time) }}
            </span>
          </div>

          <div class="p-6 space-y-3 overflow-y-auto">
            <h3 class="text-lg font-bold text-blue-950">Motivo: {{ selectedOutage.reason }}</h3>
            <p class="text-base text-gray-600 leading-relaxed flex items-start gap-2">
              <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
              <span><span class="font-semibold">Zonas afectadas:</span> <span class="text-gray-800">{{ selectedOutage.location }}</span></span>
            </p>
          </div>

          <div class="px-6 pb-6">
            <Link
              href="/informacion/cortes-programados"
              class="text-xs font-bold text-blue-900 hover:text-blue-700"
            >
              Ver todos los cortes →
            </Link>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Parallax Sucre Panel -->
    <section
      class="relative py-24 lg:py-32 bg-cover bg-center"
      style="background-image: linear-gradient(rgba(0,20,40,0.6), rgba(0,20,40,0.7)), url('/storage/galeria/95.webp');"
    >
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
          CESSA. <span class="text-amber-400">La Energía del Desarrollo.</span>
        </h2>
      </div>
    </section>

    <!-- Documentos Quick Links -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
            Transparencia
          </span>
          <h2 class="text-3xl font-extrabold text-blue-950 tracking-tight">Documentos</h2>
        </div>

        <div v-if="documentGroups && documentGroups.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <Link
            v-for="group in documentGroups"
            :key="group.key"
            :href="`/procesos?group=${group.key}`"
            class="group p-6 bg-gray-50 border border-gray-200 hover:border-blue-900 rounded-2xl text-center space-y-3 transition-all"
          >
            <svg class="w-8 h-8 mx-auto text-blue-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7.914a2 2 0 00-.586-1.414l-3.914-3.914A2 2 0 0012.086 2H4zm0 2h7v3a2 2 0 002 2h3v9H4V4zm9 .5L15.5 7H13V4.5z" clip-rule="evenodd" /></svg>
            <span class="text-sm font-bold text-blue-950 block leading-snug">{{ group.label }}</span>
            <span class="text-[11px] font-bold text-blue-700 group-hover:text-blue-900">{{ group.count }} disponibles →</span>
          </Link>
        </div>
        <div class="text-center">
          <Link href="/informacion/documentos" class="text-xs font-bold text-blue-900 hover:text-blue-700">
            Ver Documentos Institucionales →
          </Link>
        </div>
      </div>
    </section>

    <!-- Consejos Importantes + Video Consejo -->
    <section
      class="relative py-16 bg-cover bg-center"
      style="background-image: linear-gradient(rgba(15,17,23,0.85), rgba(15,17,23,0.9)), url('/storage/galeria/140.webp');"
    >
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="space-y-4">
          <h2 class="text-2xl font-extrabold text-white tracking-tight">Consejos Importantes</h2>
          <div v-if="consejos" class="space-y-3">
            <h3 class="text-lg font-bold text-amber-400">{{ consejos.title }}</h3>
            <p class="text-gray-300 text-base leading-relaxed line-clamp-5">{{ consejos.summary }}</p>
            <Link :href="`/contenido/${consejos.alias ?? 'consejos-de-seguridad'}`" class="inline-block text-xs font-bold text-amber-400 hover:text-amber-300">
              Leer consejos completos →
            </Link>
          </div>
          <p v-else class="text-gray-400 text-sm">Próximamente más consejos de seguridad eléctrica.</p>
        </div>

        <div class="space-y-4">
          <h2 class="text-2xl font-extrabold text-white tracking-tight">Video Consejo</h2>
          <div v-if="video && embedUrl(video.url)" class="aspect-video rounded-2xl overflow-hidden border border-gray-700 shadow-xl">
            <iframe
              :src="embedUrl(video.url)"
              class="w-full h-full"
              frameborder="0"
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- Mapa de Ubicación -->
    <section class="relative">
      <div class="relative h-[420px] w-full">
        <StaticLocationMap
          :lat="-19.046007"
          :lng="-65.262855"
          :zoom="16"
          :google-maps-api-key="googleMapsApiKey"
        />

        <div class="absolute top-6 left-6 max-w-xs p-5 bg-white rounded-2xl shadow-2xl border border-gray-200 space-y-2">
          <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest block">Oficina Central</span>
          <h3 class="text-base font-extrabold text-blue-950">Compañía Eléctrica Sucre S.A.</h3>
          <p class="text-xs text-gray-600">Calle Ayacucho Nº 254, Sucre - Bolivia</p>
          <a
            href="https://www.google.com/maps?q=-19.046007,-65.262855"
            target="_blank"
            class="inline-block text-xs font-bold text-blue-900 hover:text-blue-700 pt-1"
          >
            Cómo llegar →
          </a>
        </div>
      </div>
    </section>
  </AppLayout>

  <PopupNews v-if="popupNews" :news="popupNews" />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PopupNews from '../Components/PopupNews.vue';
import StaticLocationMap from '../Components/StaticLocationMap.vue';
import HeroBackgroundCarousel from '../Components/HeroBackgroundCarousel.vue';
import { formatFechaLarga, formatHora, formatHoraTimestamp } from '../utils/formatFecha';

defineProps({
  outages: Array,
  emergencies: { type: Array, default: () => [] },
  documentGroups: Array,
  consejos: Object,
  video: Object,
  popupNews: Object,
  galleryHighlights: Array,
  galleryHighlightsMobile: Array,
  googleMapsApiKey: { type: String, default: '' },
});

// Foco sutil: si el cursor pasa dentro de FOCUS_RADIUS del centro del botón, se
// enciende un halo (box-shadow) proporcional a qué tan cerca está, sin mover el
// botón de su lugar -- versión más discreta que el efecto "magnético" anterior.
const consultarBtn = ref(null);
const focusIntensity = ref(0);
const FOCUS_RADIUS = 110;

const onHeroMouseMove = (e) => {
  const el = consultarBtn.value;
  if (!el) return;

  const rect = el.getBoundingClientRect();
  const dx = e.clientX - (rect.left + rect.width / 2);
  const dy = e.clientY - (rect.top + rect.height / 2);
  const dist = Math.hypot(dx, dy);

  focusIntensity.value = Math.max(0, 1 - dist / FOCUS_RADIUS);
};

const resetMagnet = () => {
  focusIntensity.value = 0;
};

const focusStyle = computed(() => ({
  boxShadow: `0 0 0 ${4 + focusIntensity.value * 8}px rgba(245, 158, 11, ${(focusIntensity.value * 0.3).toFixed(3)})`,
}));

const buscarDeuda = () => {
  router.get('/consulta-deuda');
};

const selectedOutage = ref(null);

const embedUrl = (url) => {
  if (!url) return null;
  const youtubeMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  return youtubeMatch ? `https://www.youtube.com/embed/${youtubeMatch[1]}` : null;
};
</script>
