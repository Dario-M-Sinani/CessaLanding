<template>
  <div class="min-h-screen bg-white text-gray-800 flex flex-col font-sans selection:bg-amber-400 selection:text-blue-950">
    <!-- Navbar Component -->
    <Navbar />

    <!-- Main Content Slot -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Footer (Charcoal Background, matching cessa.com.bo footer color #272727) -->
    <footer class="bg-[#272727] border-t-4 border-amber-400 text-white">

      <!-- Contact Strip -->
      <div class="border-b border-neutral-700/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-center">
          <span class="font-bold text-white">¿Tienes más preguntas?</span>
          <a href="mailto:cessa@cessa.com.bo" class="flex items-center gap-1.5 text-neutral-300 hover:text-amber-400 transition-colors">
            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" /><path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" /></svg>
            cessa@cessa.com.bo
          </a>
          <span class="flex items-center gap-1.5 text-neutral-300">
            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
            Calle Ayacucho Nº 254
          </span>
          <a href="tel:176" class="flex items-center gap-1.5 text-neutral-300 hover:text-amber-400 transition-colors">
            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd" /></svg>
            176 - 46214500
          </a>
          <a href="https://wa.me/59172876668" target="_blank" class="flex items-center gap-1.5 text-neutral-300 hover:text-amber-400 transition-colors">
            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2c-4.418 0-8 3.134-8 7 0 2.09 1.048 3.976 2.723 5.264-.16.982-.457 1.803-.928 2.516a.75.75 0 00.712 1.146 8.5 8.5 0 004.293-1.375A9.72 9.72 0 0010 17c4.418 0 8-3.134 8-7s-3.582-7-8-7z" clip-rule="evenodd" /></svg>
            72876668
          </a>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-4 gap-10">

        <!-- Col 1: Brand Info -->
        <div class="space-y-4">
          <img src="/img/Logo_CESSA_240x240.png" alt="CESSA Logo" class="h-14 w-auto bg-white p-1.5 rounded-lg" />
          <p class="text-xs font-bold text-amber-400 uppercase tracking-wide">La Energía del Desarrollo</p>
          <p class="text-xs text-neutral-400 leading-relaxed">
            Compañía Eléctrica Sucre S.A. — Distribución y comercialización de energía eléctrica en el Departamento de Chuquisaca.
          </p>
          <div class="flex items-center space-x-2.5 pt-1">
            <a href="https://www.facebook.com/cessasucre" target="_blank" aria-label="Facebook" class="w-8 h-8 rounded-full bg-neutral-800 hover:bg-amber-400 flex items-center justify-center text-neutral-300 hover:text-neutral-900 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94z"/></svg>
            </a>
            <a href="https://twitter.com/CessaSucre" target="_blank" aria-label="Twitter / X" class="w-8 h-8 rounded-full bg-neutral-800 hover:bg-amber-400 flex items-center justify-center text-neutral-300 hover:text-neutral-900 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.6 8.7L23 22h-6.9l-5.4-6.6L4.6 22H1.5l8.1-9.3L1 2h7l4.9 6z"/></svg>
            </a>
            <a href="https://www.instagram.com/cessasucre.bo/" target="_blank" aria-label="Instagram" class="w-8 h-8 rounded-full bg-neutral-800 hover:bg-amber-400 flex items-center justify-center text-neutral-300 hover:text-neutral-900 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.65.25 1.21.6 1.75 1.14.54.54.89 1.1 1.14 1.75.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.14 1.75 4.9 4.9 0 0 1-1.75 1.14c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.75-1.14 4.9 4.9 0 0 1-1.14-1.75c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.25-.65.6-1.21 1.14-1.75a4.9 4.9 0 0 1 1.75-1.14c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2zm0 3.15a6.85 6.85 0 1 0 0 13.7 6.85 6.85 0 0 0 0-13.7zm0 11.3a4.45 4.45 0 1 1 0-8.9 4.45 4.45 0 0 1 0 8.9zm7.12-11.57a1.6 1.6 0 1 1-3.2 0 1.6 1.6 0 0 1 3.2 0z"/></svg>
            </a>
            <a href="https://www.youtube.com/channel/UCgs6_sEf0wx9AMlYnVICgmA" target="_blank" aria-label="YouTube" class="w-8 h-8 rounded-full bg-neutral-800 hover:bg-amber-400 flex items-center justify-center text-neutral-300 hover:text-neutral-900 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.38.55A3.02 3.02 0 0 0 .5 6.19 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14c1.88.55 9.38.55 9.38.55s7.5 0 9.38-.55a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.81zM9.6 15.6V8.4l6.27 3.6-6.27 3.6z"/></svg>
            </a>
          </div>

          <!-- App Móvil -->
          <div class="space-y-2 pt-1">
            <p class="text-xs font-bold text-amber-400 uppercase tracking-wide">Descargá Nuestra App</p>
            <div class="flex flex-col space-y-2">
              <a
                href="https://apps.apple.com/bo/app/cessa/id6453522570"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2.5 px-3.5 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 transition-colors"
              >
                <svg class="w-5 h-5 flex-shrink-0 text-white" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14.94 5.19A4.38 4.38 0 0 0 16 2 4.44 4.44 0 0 0 13 3.52a4.17 4.17 0 0 0-1 3.09 3.69 3.69 0 0 0 2.94-1.42zm2.52 7.44a4.51 4.51 0 0 1 2.16-3.81 4.66 4.66 0 0 0-3.66-2c-1.56-.16-3 .91-3.83.91s-2-.89-3.3-.87a4.92 4.92 0 0 0-4.14 2.53C2.93 12.45 4.24 17 6 19.47c.8 1.21 1.8 2.58 3.12 2.53s1.75-.82 3.28-.82 2 .82 3.3.79 2.22-1.24 3.06-2.45a11 11 0 0 0 1.38-2.85 4.41 4.41 0 0 1-2.68-4.04z" />
                </svg>
                <span class="leading-tight">
                  <span class="block text-[9px] text-neutral-400">Disponible en</span>
                  <span class="block text-xs font-bold text-white">App Store</span>
                </span>
              </a>
              <a
                href="https://play.google.com/store/apps/details?id=bo.systems.itgroup.appcessausuarios"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2.5 px-3.5 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 transition-colors"
              >
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                  <path d="M3.6 2.3c-.4.3-.6.8-.6 1.4v16.6c0 .6.2 1.1.6 1.4l.1.1L13 12v-.2L3.7 2.2z" fill="#00d2ff" />
                  <path d="M16.1 15.1 13 12v-.2l3.1-3.1 6.9 3.9c1 .6 1 1.5 0 2.1z" fill="#ffcf00" />
                  <path d="M16.1 15.1 13 12 3.7 21.4c.4.4 1 .4 1.7.1z" fill="#ff3a44" />
                  <path d="M16.1 8.7 5.4 2.5c-.7-.3-1.3-.3-1.7.1L13 12z" fill="#00e876" />
                </svg>
                <span class="leading-tight">
                  <span class="block text-[9px] text-neutral-400">Disponible en</span>
                  <span class="block text-xs font-bold text-white">Google Play</span>
                </span>
              </a>
            </div>
          </div>
        </div>

        <!-- Col 2: La Compañía -->
        <div>
          <h4 class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-4">La Compañía</h4>
          <ul class="space-y-2 text-xs text-neutral-300">
            <li><Link href="/la-compania/quienes-somos" class="hover:text-amber-400 transition-colors">Quiénes Somos</Link></li>
            <li><Link href="/informacion/faqs" class="hover:text-amber-400 transition-colors">Preguntas Frecuentes</Link></li>
            <li><Link href="/la-compania/mision-vision" class="hover:text-amber-400 transition-colors">Misión y Visión</Link></li>
            <li><Link href="/la-compania/estructura" class="hover:text-amber-400 transition-colors">Estructura Organizacional</Link></li>
            <li><Link href="/la-compania/rrhh" class="hover:text-amber-400 transition-colors">Recursos Humanos</Link></li>
            <li><Link href="/la-compania/contacto" class="hover:text-amber-400 transition-colors">Contáctenos</Link></li>
            <li><Link href="/contenido/tramites-derechos-y-requisitos" class="hover:text-amber-400 transition-colors">Trámites, Derechos y Requisitos</Link></li>
            <li><Link href="/personal/autorizado" class="hover:text-amber-400 transition-colors">Personal Autorizado</Link></li>
          </ul>
        </div>

        <!-- Col 3: Trámites e Información -->
        <div>
          <h4 class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-4">Trámites e Información</h4>
          <ul class="space-y-2 text-xs text-neutral-300">
            <li><Link href="/informacion/cortes-programados" class="hover:text-amber-400 transition-colors">Cortes Programados</Link></li>
            <li><Link href="/calculadora" class="hover:text-amber-400 transition-colors">Cálculo de Consumo</Link></li>
            <li><Link href="/procesos" class="hover:text-amber-400 transition-colors">Licitaciones y Convocatorias</Link></li>
            <li><Link href="/informacion/documentos" class="hover:text-amber-400 transition-colors">Documentos Institucionales</Link></li>
            <li>
              <a href="https://recaudodigital.sintesis.com.bo/suitepagos-ui/company/CESSA" target="_blank" class="hover:text-amber-400 transition-colors">
                Pago de Factura Digital (QR)
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 4: Emergency Hotline Pill -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-2">Emergencias 24/7</h4>
          <div class="p-5 bg-black/30 border border-neutral-700 rounded-2xl text-center space-y-1 shadow-lg">
            <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400">Línea Gratuita de Atención</span>
            <span class="block text-2xl font-black text-white tracking-tight font-mono">176 - 46214500</span>
            <span class="text-[11px] text-neutral-400 block">Reclamos técnicos e imprevistos</span>
          </div>
        </div>

      </div>

      <!-- Regulatory / Partner Badges -->
      <div class="border-t border-neutral-800 bg-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-2 sm:grid-cols-4 gap-6 items-center">
          <a href="https://www.aetn.gob.bo/web/" target="_blank" class="flex flex-col items-center text-center gap-2">
            <img src="/img/footer/aetn.jpg" alt="AETN" class="h-12 w-auto rounded bg-white p-1" />
            <span class="text-[9px] text-neutral-500 leading-tight">Fiscalizada y regulada por la AETN</span>
          </a>
          <a href="https://www.felcn.gob.bo/" target="_blank" class="flex flex-col items-center text-center gap-2">
            <img src="/img/footer/felcn.png" alt="FELCN" class="h-12 w-auto" />
            <span class="text-[9px] text-neutral-500 leading-tight">Denuncia línea gratuita 800101344</span>
          </a>
          <a href="https://www.cndc.bo/home/index.php" target="_blank" class="flex flex-col items-center text-center gap-2">
            <img src="/img/footer/cndc.jpg" alt="CNDC" class="h-8 w-auto rounded bg-white p-1" />
            <span class="text-[9px] text-neutral-500 leading-tight">Comité Nacional de Despacho de Carga</span>
          </a>
          <a href="https://m.certipedia.com/?id=9000003691" target="_blank" class="flex items-center justify-center">
            <img src="/img/footer/iso9001.jpg" alt="ISO 9001:2015" class="h-10 w-auto rounded bg-white p-1" />
          </a>
        </div>
      </div>

      <!-- Copyright Bottom Bar -->
      <div class="border-t border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-neutral-500">
          <div class="flex flex-col sm:flex-row items-center gap-1.5 sm:gap-3 text-center sm:text-left">
            <span>Copyright &copy; {{ new Date().getFullYear() }} CESSA.</span>
            <span class="hidden sm:inline text-neutral-700">•</span>
            <span class="inline-flex items-center gap-1.5 text-neutral-400">
              <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
              Desarrollado por <span class="text-neutral-300 font-semibold">DTIC CESSA</span>
            </span>
          </div>

          <a
            href="https://recaudodigital.sintesis.com.bo/suitepagos-ui/company/CESSA"
            target="_blank"
            class="flex items-center gap-1.5 text-neutral-600 hover:text-neutral-400 transition-colors"
            title="Pago de factura digital mediante QR (Síntesis)"
          >
            <img src="/img/footer/pago-qr.png" alt="Síntesis" class="h-4 w-auto opacity-70" />
            <span class="text-[10px]">Pago QR vía Síntesis</span>
          </a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Navbar from '../Components/Navbar.vue';
</script>
