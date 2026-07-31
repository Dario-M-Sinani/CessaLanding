<template>
  <AppLayout>
    <div class="py-16 bg-white min-h-screen">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <span class="px-4 py-1.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-full text-xs font-bold uppercase tracking-wider">
            Consumidor
          </span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-950 tracking-tight">Consejos de Seguridad</h1>
          <p class="text-gray-600 text-base max-w-2xl mx-auto">
            La electricidad nos trae enormes beneficios, pero un mal uso puede ser peligroso.
            Estos consejos básicos te ayudan a vos y a tu familia a usarla sin riesgos.
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(tip, index) in tips"
            :key="tip.title"
            class="group p-6 bg-gray-50 border border-gray-200 hover:border-blue-900 rounded-2xl shadow-sm hover:shadow-md transition-all space-y-4"
          >
            <div class="flex items-center gap-3">
              <span
                class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center font-extrabold text-sm"
                :class="index % 2 === 0 ? 'bg-blue-900 text-white' : 'bg-amber-400 text-blue-950'"
              >
                {{ String(index + 1).padStart(2, '0') }}
              </span>
              <svg class="w-5 h-5 text-amber-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path d="M11.983 1.907a.75.75 0 00-1.292-.657l-8.5 9.5A.75.75 0 002.75 12h6.572l-1.305 6.093a.75.75 0 001.292.657l8.5-9.5A.75.75 0 0017.25 8h-6.572l1.305-6.093z" />
              </svg>
            </div>
            <h3 class="text-sm font-extrabold text-blue-950 uppercase tracking-wide leading-snug">{{ tip.title }}</h3>
            <p class="text-xs text-gray-600 leading-relaxed">{{ tip.body }}</p>
          </div>
        </div>

        <div class="bg-blue-950 rounded-2xl p-8 sm:p-10 flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">
          <svg class="w-12 h-12 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
          </svg>
          <div class="space-y-1.5">
            <h3 class="text-lg font-extrabold text-white">¿Tenés una emergencia eléctrica?</h3>
            <p class="text-blue-100 text-xs leading-relaxed">
              Cables caídos, chispas, olor a quemado o cualquier situación de riesgo con la red eléctrica: llamá de inmediato
              a nuestra línea gratuita de emergencias 24/7.
            </p>
          </div>
          <a href="tel:176" class="sm:ml-auto shrink-0 px-6 py-3 bg-amber-400 hover:bg-amber-300 text-blue-950 font-extrabold rounded-xl text-sm transition-colors whitespace-nowrap">
            Llamar al 176
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

const tips = [
  {
    title: 'En la casa',
    body: 'Enseñá a los niños a no jugar con cables ni tomacorrientes, e instalá protectores en los enchufes. Nunca dejes que un bebé se lleve un cable a la boca, y guardá planchas, tostadoras, ventiladores y calefactores lejos de su alcance.',
  },
  {
    title: 'Manejo de cables y enchufes',
    body: 'Revisá tus cables: si están pelados o dañados, cambialos. Mantenelos lejos del calor, aceite y agua, y nunca los pases debajo de alfombras. Para desconectar un aparato, tirá siempre del cuerpo del enchufe, nunca del cable ni de su parte metálica.',
  },
  {
    title: 'Cambiar un artefacto eléctrico',
    body: 'Antes de cambiar un foco o una lámpara, apagá el interruptor y evitá tocar la parte metálica. Prestá atención a señales de alerta como luces que parpadean, disyuntores que saltan seguido o electrodomésticos que tardan en calentar.',
  },
  {
    title: 'Recarga de enchufes',
    body: 'Evitá los "ladrones" de corriente y no uses extensiones para conexiones permanentes: sobrecargar una línea puede recalentarla y provocar un cortocircuito o un incendio. Nunca uses un aparato eléctrico con las manos mojadas.',
  },
  {
    title: 'En el tránsito',
    body: 'Si tu auto choca contra un poste y hay cables encima, no salgas: podés cerrar el circuito y sufrir una descarga. Si tenés que salir sí o sí, saltá con los dos pies juntos, lo más lejos posible del vehículo, y llamá enseguida a CESSA.',
  },
  {
    title: 'En la construcción',
    body: 'No armes andamios cerca de líneas eléctricas ni transportes fierros, escaleras u otros objetos metálicos cerca de los cables. El contacto puede provocar cortocircuitos, explosiones o descargas a tierra.',
  },
];
</script>
