<template>
  <header
    :class="[
      isHomePage ? 'fixed top-0 left-0 right-0' : 'sticky top-0',
      'z-50 border-b transition-all duration-700',
      transparentActive ? 'border-transparent shadow-none nav-transparent' : 'bg-white border-gray-200 shadow-sm',
    ]"
    @mouseenter="onHeaderEnter"
    @mouseleave="onHeaderLeave"
  >

    <!-- Wrapper medido por el ResizeObserver para --nav-height: solo la barra superior +
         la barra de nav, sin el drawer mobile (si el drawer entrara en esta medición, abrirlo
         hace crecer el alto medido, lo que reduce el max-height del propio drawer -- ver bug
         corregido en §3.18bis del continuity doc). -->
    <div ref="navContentRef">
    <!-- Top Header Bar -->
    <div
      :class="transparentActive ? 'bg-transparent border-transparent' : 'bg-blue-900 border-b border-blue-950'"
      class="py-1.5 sm:py-2 px-4 sm:px-6 lg:px-8 text-xs text-white transition-all duration-700"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
        <div class="flex items-center space-x-4">
          <span class="flex items-baseline space-x-1 text-amber-400 font-bold">
            <span>Emergencias 24/7:</span>
            <span class="text-white font-mono">176 - 46214500</span>
          </span>
          <span class="hidden md:inline text-blue-300">•</span>
          <span class="hidden md:inline text-blue-100">Atención al Cliente: (591-4) 64-51200</span>
        </div>

        <!-- Header Search Box -->
        <div class="relative flex items-center">
          <!-- Mobile: solo ícono de lupa, el input se despliega al tocar (se ve más limpio,
               sobre todo con el header transparente -- menos elementos "flotando"). -->
          <button
            v-if="!mobileSearchOpen"
            type="button"
            @click="mobileSearchOpen = true"
            class="sm:hidden w-7 h-7 flex items-center justify-center text-blue-200 hover:text-amber-400 transition-colors"
            aria-label="Buscar"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
          </button>

          <div v-if="mobileSearchOpen" class="sm:hidden flex items-center gap-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar..."
              autofocus
              class="w-32 px-3 py-1 bg-blue-950 border border-blue-800 rounded-full text-white text-[11px] placeholder-blue-300 focus:outline-none focus:border-amber-400"
              @keyup.enter="handleSearch"
              @keyup.escape="mobileSearchOpen = false"
            />
            <button @click="handleSearch" type="button" aria-label="Buscar" class="text-blue-300 hover:text-amber-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
            </button>
            <button @click="mobileSearchOpen = false" type="button" aria-label="Cerrar búsqueda" class="text-blue-300 hover:text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <!-- Desktop / tablet: input completo con el texto "Buscar", siempre visible -->
          <div class="relative hidden sm:block">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar en el portal..."
              class="w-40 sm:w-56 px-3 py-1 bg-blue-950 border border-blue-800 rounded-full text-white text-[11px] placeholder-blue-300 focus:outline-none focus:border-amber-400 transition-all"
              @keyup.enter="handleSearch"
            />
            <button @click="handleSearch" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-blue-300 hover:text-amber-400 text-xs">
              Buscar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 lg:h-20 flex items-center justify-between">

      <!-- Brand Logo -->
      <!-- Dos versiones superpuestas con cruce suave: la de texto negro (normal, sobre fondo
           blanco del nav) y la de texto blanco (cuando el nav está transparente sobre la foto
           del hero -- "CESSA" negro ahí sería casi ilegible). -->
      <Link href="/" class="flex items-center space-x-3 group">
        <div class="relative h-10 sm:h-12 lg:h-16">
          <img
            src="/img/Logo_CESSA_240x240.png"
            alt="CESSA Logo"
            class="h-full w-auto object-contain transition-opacity duration-700"
            :class="transparentActive ? 'opacity-0' : 'opacity-100'"
            @error="handleImageError"
          />
          <img
            src="/img/Logo_CESSA_240x240_white-text.png"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-auto object-contain transition-opacity duration-700 pointer-events-none"
            :class="transparentActive ? 'opacity-100' : 'opacity-0'"
          />
        </div>
        <div v-if="imageError" class="flex items-center space-x-2">
          <div class="w-10 h-10 rounded-xl bg-blue-900 flex items-center justify-center text-amber-400 font-black text-xl border-2 border-amber-400">
            C
          </div>
          <div>
            <span class="text-xl font-extrabold text-blue-900 tracking-tight">CESSA</span>
            <span class="block text-[10px] text-gray-500 font-medium">Compañía Eléctrica Sucre S.A.</span>
          </div>
        </div>
      </Link>

      <!-- Desktop Dropdowns & Links -->
      <nav class="hidden lg:flex items-center space-x-1">
        
        <Link
          href="/"
          :class="[
            $page.url === '/' ? 'text-blue-900 font-bold border-b-2 border-amber-500' : 'text-gray-700 hover:text-blue-900 hover:bg-gray-50',
            'px-3.5 py-2 rounded-lg text-xs font-semibold transition-all'
          ]"
        >
          Inicio
        </Link>

        <!-- Dropdown 1: La Compañía -->
        <div class="relative group" @mouseenter="openDropdown = 'compania'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': $page.url.startsWith('/la-compania') }"
          >
            <span>La Compañía</span>
            <svg class="w-3.5 h-3.5 text-amber-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div
            v-show="openDropdown === 'compania'"
            class="absolute top-full left-0 w-60 pt-1 shadow-xl z-50 transition-all duration-200"
          >
            <div class="bg-white border border-gray-200 rounded-xl p-2 space-y-1 shadow-2xl">
              <Link href="/la-compania/quienes-somos" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Quiénes Somos
              </Link>
              <Link href="/la-compania/historia" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Historia Institucional
              </Link>
              <Link href="/la-compania/mision-vision" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Misión, Visión y Valores
              </Link>
              <Link href="/la-compania/estructura" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Estructura Organizacional
              </Link>
              <Link href="/la-compania/rrhh" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Recursos Humanos
              </Link>
              <Link href="/la-compania/contacto" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Contáctanos
              </Link>
            </div>
          </div>
        </div>

        <!-- Dropdown 2: Información -->
        <div class="relative group" @mouseenter="openDropdown = 'informacion'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': $page.url.startsWith('/informacion') }"
          >
            <span>Información</span>
            <svg class="w-3.5 h-3.5 text-amber-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div
            v-show="openDropdown === 'informacion'"
            class="absolute top-full left-0 w-60 pt-1 shadow-xl z-50 transition-all duration-200"
          >
            <div class="bg-white border border-gray-200 rounded-xl p-2 space-y-1 shadow-2xl">
              <Link href="/informacion/cortes-programados" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Cortes Programados
              </Link>
              <Link href="/informacion/documentos" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Documentos Institucionales
              </Link>
              <Link href="/informacion/faqs" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Preguntas Frecuentes
              </Link>
              <Link href="/contenido/personal-autorizado-cessa" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Personal Autorizado
              </Link>
              <Link href="/contenido/tramites-derechos-y-requisitos" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Trámites, Derechos y Requisitos
              </Link>
            </div>
          </div>
        </div>

        <!-- Dropdown 2bis: Consumidor -->
        <div class="relative group" @mouseenter="openDropdown = 'consumidor'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': $page.url.startsWith('/informacion/consejos-de-seguridad') }"
          >
            <span>Consumidor</span>
            <svg class="w-3.5 h-3.5 text-amber-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div
            v-show="openDropdown === 'consumidor'"
            class="absolute top-full left-0 w-64 pt-1 shadow-xl z-50 transition-all duration-200"
          >
            <div class="bg-white border border-gray-200 rounded-xl p-2 space-y-1 shadow-2xl">
              <Link href="/contenido/nuevas-instalaciones" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Nuevas Instalaciones
              </Link>
              <Link href="/contenido/descuentos-y-privilegios-para-personas-de-la-tercera-edad" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Descuentos Tercera Edad
              </Link>
              <Link href="/contenido/otros-tramites-y-servicios" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Otros Trámites y Servicios
              </Link>
              <Link href="/contenido/procedimiento-para-reclamos" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Procedimiento para Reclamos
              </Link>
              <Link href="/contenido/derechos-y-obligaciones" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Derechos y Obligaciones
              </Link>
              <Link href="/contenido/generacion-distribuida" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Generación Distribuida
              </Link>
              <Link href="/informacion/consejos-de-seguridad" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Consejos de Seguridad
              </Link>
              <Link href="/contenido/comunicados-aetn" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Comunicados AETN
              </Link>
            </div>
          </div>
        </div>

        <!-- Dropdown 3: Servicios -->
        <div class="relative group" @mouseenter="openDropdown = 'servicios'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': ['/consulta-deuda', '/calculadora', '/nueva-conexion', '/suspension-servicio', '/otras-solicitudes', '/buscar-tramite', '/importante/estructura-tarifaria'].includes($page.url) }"
          >
            <span>Servicios</span>
            <svg class="w-3.5 h-3.5 text-amber-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div
            v-show="openDropdown === 'servicios'"
            class="absolute top-full left-0 w-60 pt-1 shadow-xl z-50 transition-all duration-200"
          >
            <div class="bg-white border border-gray-200 rounded-xl p-2 space-y-1 shadow-2xl">
              <Link href="/consulta-deuda" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Consulta de Deuda
              </Link>
              <Link href="/calculadora" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Calculadora de Consumo
              </Link>
              <Link href="/importante/estructura-tarifaria" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Estructura Tarifaria
              </Link>
              <Link href="/nueva-conexion" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Nueva Conexión
              </Link>
              <Link href="/suspension-servicio" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Suspensión Temporal o Definitiva
              </Link>
              <Link href="/otras-solicitudes" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Otras Solicitudes
              </Link>
              <Link href="/buscar-tramite" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Buscar Trámite
              </Link>
            </div>
          </div>
        </div>

        <Link href="/noticias" class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 transition-all">
          Noticias
        </Link>
        <Link href="/procesos" class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 transition-all">
          Procesos
        </Link>
        <!-- Dropdown 4: Galería -->
        <div class="relative group" @mouseenter="openDropdown = 'galeria'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': $page.url.startsWith('/galeria') }"
          >
            <span>Galería</span>
            <svg class="w-3.5 h-3.5 text-amber-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div
            v-show="openDropdown === 'galeria'"
            class="absolute top-full left-0 w-60 pt-1 shadow-xl z-50 transition-all duration-200"
          >
            <div class="bg-white border border-gray-200 rounded-xl p-2 space-y-1 shadow-2xl">
              <Link href="/galeria" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Galería de Videos
              </Link>
              <Link href="/galeria/imagenes" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Galería de Fotos
              </Link>
            </div>
          </div>
        </div>

      </nav>

      <!-- Mobile Toggle Button -->
      <button
        @click="mobileOpen = !mobileOpen"
        class="lg:hidden p-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    </div>

    <!-- Mobile Drawer -->
    <!-- Solo en el Home el header es fixed (queda fuera del flujo del documento), así que ahí
         el drawer necesita su propio scroll interno acotado a la altura real de nav (si no, el
         gesto de scroll se le escapa a la página de atrás). En el resto de las páginas el header
         es sticky (sigue en el flujo normal), así que el drawer simplemente empuja el contenido
         y la página entera scrollea -- no hace falta (ni conviene) recortarlo ahí. -->
    <div
      v-if="mobileOpen"
      :class="[
        'lg:hidden bg-white border-b border-gray-200 px-4 py-4 space-y-3',
        isHomePage ? 'overflow-y-auto overscroll-contain' : '',
      ]"
      :style="isHomePage ? { maxHeight: 'calc(100vh - var(--nav-height, 120px))' } : null"
    >
      <div class="space-y-1">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">La Compañía</span>
        <Link href="/la-compania/quienes-somos" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Quiénes Somos</Link>
        <Link href="/la-compania/historia" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Historia</Link>
        <Link href="/la-compania/mision-vision" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Misión, Visión y Valores</Link>
        <Link href="/la-compania/estructura" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Estructura Organizacional</Link>
        <Link href="/la-compania/rrhh" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Recursos Humanos</Link>
        <Link href="/la-compania/contacto" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Contáctanos</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Información</span>
        <Link href="/informacion/cortes-programados" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Cortes Programados</Link>
        <Link href="/informacion/documentos" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Documentos Institucionales</Link>
        <Link href="/informacion/faqs" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Preguntas Frecuentes</Link>
        <Link href="/contenido/personal-autorizado-cessa" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Personal Autorizado</Link>
        <Link href="/contenido/tramites-derechos-y-requisitos" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Trámites, Derechos y Requisitos</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Consumidor</span>
        <Link href="/contenido/nuevas-instalaciones" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Nuevas Instalaciones</Link>
        <Link href="/contenido/descuentos-y-privilegios-para-personas-de-la-tercera-edad" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Descuentos Tercera Edad</Link>
        <Link href="/contenido/otros-tramites-y-servicios" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Otros Trámites y Servicios</Link>
        <Link href="/contenido/procedimiento-para-reclamos" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Procedimiento para Reclamos</Link>
        <Link href="/contenido/derechos-y-obligaciones" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Derechos y Obligaciones</Link>
        <Link href="/contenido/generacion-distribuida" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Generación Distribuida</Link>
        <Link href="/informacion/consejos-de-seguridad" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Consejos de Seguridad</Link>
        <Link href="/contenido/comunicados-aetn" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Comunicados AETN</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Servicios</span>
        <Link href="/consulta-deuda" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Consulta de Deuda</Link>
        <Link href="/calculadora" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Calculadora de Consumo</Link>
        <Link href="/importante/estructura-tarifaria" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Estructura Tarifaria</Link>
        <Link href="/nueva-conexion" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Nueva Conexión</Link>
        <Link href="/suspension-servicio" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Suspensión Temporal o Definitiva</Link>
        <Link href="/otras-solicitudes" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Otras Solicitudes</Link>
        <Link href="/buscar-tramite" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Buscar Trámite</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Galería</span>
        <Link href="/galeria" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Galería de Videos</Link>
        <Link href="/galeria/imagenes" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Galería de Fotos</Link>
      </div>

      <div class="border-t border-gray-100 pt-2 flex flex-col space-y-1">
        <Link href="/noticias" @click="mobileOpen = false" class="block px-4 py-2 text-xs font-semibold text-gray-800">Noticias</Link>
        <Link href="/procesos" @click="mobileOpen = false" class="block px-4 py-2 text-xs font-semibold text-gray-800">Procesos</Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const openDropdown = ref(null);
const mobileOpen = ref(false);
const mobileSearchOpen = ref(false);
const searchQuery = ref('');
const imageError = ref(false);

const handleImageError = () => {
  imageError.value = true;
};

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.get('/buscar', { q: searchQuery.value.trim() });
  }
};

// En la home, el nav flota transparente sobre el fondo del hero mientras el
// usuario no lo esté usando -- así la imagen tiene todo el protagonismo.
// Solo se vuelve opaco si el mouse pasa por encima del nav (hover) o si se
// hace scroll (deja de estar "atTop"). Mover el mouse sobre el resto de la
// página (la imagen) no lo revela.
const page = usePage();
const isHomePage = computed(() => page.url === '/');

const navContentRef = ref(null);
const atTop = ref(true);
const isIdle = ref(false);
const transparentActive = computed(() => isHomePage.value && atTop.value && isIdle.value);

let idleTimer = null;
let resizeObserver = null;

const startIdleTimer = (delay = 2800) => {
  clearTimeout(idleTimer);
  idleTimer = setTimeout(() => {
    isIdle.value = true;
  }, delay);
};

const onHeaderEnter = () => {
  if (!isHomePage.value) return;
  clearTimeout(idleTimer);
  isIdle.value = false;
};

const onHeaderLeave = () => {
  if (!isHomePage.value) return;
  startIdleTimer(1200);
};

const updateAtTop = () => {
  atTop.value = window.scrollY <= 10;
};

onMounted(() => {
  // Se mide en todas las páginas (no solo Home): el drawer mobile de las páginas con header
  // sticky también lee --nav-height como fallback de referencia, y Home.vue lo usa para el
  // padding-top del hero.
  if (navContentRef.value) {
    const setNavHeightVar = () => {
      document.documentElement.style.setProperty('--nav-height', navContentRef.value.offsetHeight + 'px');
    };
    setNavHeightVar();
    resizeObserver = new ResizeObserver(setNavHeightVar);
    resizeObserver.observe(navContentRef.value);
  }

  if (!isHomePage.value) return;

  window.addEventListener('scroll', updateAtTop, { passive: true });

  updateAtTop();
  startIdleTimer();
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', updateAtTop);
  clearTimeout(idleTimer);
  if (resizeObserver) resizeObserver.disconnect();
});
</script>

<style scoped>
header.nav-transparent nav .text-gray-700,
header.nav-transparent nav .text-blue-900 {
  color: #ffffff !important;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
}

header.nav-transparent .text-white,
header.nav-transparent .text-amber-400,
header.nav-transparent .text-blue-100,
header.nav-transparent .text-blue-300 {
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
}
</style>
