<template>
  <header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    
    <!-- Top Header Bar -->
    <div class="bg-blue-900 border-b border-blue-950 py-2 px-4 sm:px-6 lg:px-8 text-xs text-white">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="flex items-center space-x-4">
          <span class="flex items-center space-x-1 text-amber-400 font-bold">
            <span>Emergencias 24/7:</span>
            <span class="text-white font-mono">176 - 46214500</span>
          </span>
          <span class="hidden md:inline text-blue-300">•</span>
          <span class="hidden md:inline text-blue-100">Atención al Cliente: (591-4) 64-51200</span>
        </div>

        <div class="flex items-center space-x-3">
          <!-- Header Search Box -->
          <div class="relative">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      
      <!-- Brand Logo -->
      <Link href="/" class="flex items-center space-x-3 group">
        <img
          src="/img/cessa_logo.jpg"
          alt="CESSA Logo"
          class="h-16 w-auto object-contain"
          @error="handleImageError"
        />
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
              <Link href="/contenido/consejos-de-seguridad" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Consejos de Seguridad
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

        <!-- Dropdown 3: Servicios -->
        <div class="relative group" @mouseenter="openDropdown = 'servicios'" @mouseleave="openDropdown = null">
          <button
            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-blue-900 hover:bg-gray-50 flex items-center space-x-1 transition-all"
            :class="{ 'text-blue-900 font-bold border-b-2 border-amber-500': ['/consulta-deuda', '/calculadora', '/nueva-conexion', '/suspension-servicio', '/otras-solicitudes', '/buscar-tramite'].includes($page.url) }"
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
              <Link href="/contenido/generacion-distribuida" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Generación Distribuida
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
              <Link href="/galeria/trabajadores" class="block px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 hover:text-blue-900 hover:bg-blue-50 border-l-2 border-transparent hover:border-amber-500 transition-all">
                Galería de Trabajadores
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

    <!-- Mobile Drawer -->
    <div v-if="mobileOpen" class="lg:hidden bg-white border-b border-gray-200 px-4 py-4 space-y-3">
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
        <Link href="/contenido/consejos-de-seguridad" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Consejos de Seguridad</Link>
        <Link href="/contenido/personal-autorizado-cessa" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Personal Autorizado</Link>
        <Link href="/contenido/tramites-derechos-y-requisitos" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Trámites, Derechos y Requisitos</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Servicios</span>
        <Link href="/consulta-deuda" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Consulta de Deuda</Link>
        <Link href="/calculadora" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Calculadora de Consumo</Link>
        <Link href="/nueva-conexion" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Nueva Conexión</Link>
        <Link href="/suspension-servicio" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Suspensión Temporal o Definitiva</Link>
        <Link href="/otras-solicitudes" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Otras Solicitudes</Link>
        <Link href="/buscar-tramite" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Buscar Trámite</Link>
        <Link href="/contenido/generacion-distribuida" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Generación Distribuida</Link>
      </div>

      <div class="space-y-1 border-t border-gray-100 pt-2">
        <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block px-3 py-1">Galería</span>
        <Link href="/galeria" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Galería de Videos</Link>
        <Link href="/galeria/imagenes" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Galería de Fotos</Link>
        <Link href="/galeria/trabajadores" @click="mobileOpen = false" class="block px-4 py-2 rounded-lg text-xs text-gray-700 hover:bg-blue-50">Galería de Trabajadores</Link>
      </div>

      <div class="border-t border-gray-100 pt-2 flex flex-col space-y-1">
        <Link href="/noticias" @click="mobileOpen = false" class="block px-4 py-2 text-xs font-semibold text-gray-800">Noticias</Link>
        <Link href="/procesos" @click="mobileOpen = false" class="block px-4 py-2 text-xs font-semibold text-gray-800">Procesos</Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const openDropdown = ref(null);
const mobileOpen = ref(false);
const searchQuery = ref('');
const imageError = ref(false);

const handleImageError = () => {
  imageError.value = true;
};

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.get('/buscar-tramite', { nro_solicitud: searchQuery.value.trim() });
  }
};
</script>
