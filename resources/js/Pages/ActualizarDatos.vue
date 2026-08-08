<template>
  <AppLayout>
    <div class="py-12 bg-white min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header / Marketing -->
        <div class="text-center space-y-3">
          <span class="px-4 py-1.5 bg-amber-50 border border-amber-300 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider">
            Menos Papel, Más Rapidez
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-950">Actualiza tus Datos de Contacto</h1>
          <p class="text-gray-600 text-sm max-w-2xl mx-auto">
            Estamos dejando atrás el papel impreso: muy pronto tu aviso de cobranza llegará
            directo a tu <span class="font-semibold text-gray-800">correo electrónico</span>, y
            próximamente también por <span class="font-semibold text-gray-800">WhatsApp</span>.
            CESSA ya cuenta con un servicio de notificaciones por SMS a través de Tigo, así que
            confirmar tu correo y celular es el primer paso para que no se te pase ninguna
            factura.
          </p>
        </div>

        <!-- Incentive Banner -->
        <div class="bg-blue-900 text-white rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <svg class="w-9 h-9 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0015.5 15h-.75a2.5 2.5 0 100-5h-4.5a1.5 1.5 0 010-3H14V5.5h-2.5V4h-1.5v1.55c-1.978.243-3.5 1.928-3.5 3.95a3.5 3.5 0 003.5 3.5h1.25a1 1 0 010 2H6.5V17h2.5v1.5h1.5v-1.55c.088-.01.174-.021.25-.033z" /></svg>
            <div class="text-xs sm:text-sm">
              <span class="font-bold block">Beneficios exclusivos para quienes actualicen sus datos</span>
              <span class="text-blue-100">Estamos preparando sorpresas e incentivos para los abonados que se sumen primero a la facturación digital. ¡Sé de los primeros!</span>
            </div>
          </div>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-2 sm:gap-4">
          <div
            v-for="(label, idx) in ['Verificar Cuenta', 'Datos de Contacto', 'Confirmar Códigos']"
            :key="idx"
            class="flex items-center gap-2 sm:gap-4"
          >
            <div class="flex items-center gap-2">
              <span
                class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                :class="paso > idx + 1
                  ? 'bg-emerald-500 text-white'
                  : paso === idx + 1
                    ? 'bg-amber-500 text-blue-950'
                    : 'bg-gray-200 text-gray-500'"
              >
                <svg v-if="paso > idx + 1" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                <span v-else>{{ idx + 1 }}</span>
              </span>
              <span class="text-[11px] sm:text-xs font-semibold hidden sm:inline" :class="paso === idx + 1 ? 'text-blue-950' : 'text-gray-500'">{{ label }}</span>
            </div>
            <div v-if="idx < 2" class="w-6 sm:w-10 h-0.5 bg-gray-200"></div>
          </div>
        </div>

        <!-- Generic Error Alert -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
          {{ error }}
        </div>

        <!-- STEP 1: Verificar cuenta -->
        <div v-if="paso === 1" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          <form @submit.prevent="verificarCuenta" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Número de Abonado</label>
              <input
                v-model="verificacion.nro_cliente"
                type="text"
                inputmode="numeric"
                placeholder="Ejemplo: 123456"
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono"
                required
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">N° de Cuenta</label>
              <input
                :value="verificacion.nro_cuenta"
                @input="onNroCuentaInput"
                type="text"
                inputmode="numeric"
                placeholder="Ejemplo: 00-000-00000"
                maxlength="12"
                required
                class="w-full sm:w-72 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono"
              />
              <p v-if="formatError" class="text-[11px] text-red-600 mt-1.5">{{ formatError }}</p>
              <p v-else class="text-[11px] text-gray-500 mt-1.5">
                Formato Zona-Manzano-Correlativo, tal como aparece en la parte superior de tu factura.
              </p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
            >
              {{ loading ? 'Verificando...' : 'Verificar' }}
            </button>
          </form>
        </div>

        <!-- STEP 2: Datos de contacto -->
        <div v-if="paso === 2" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          <div class="pb-4 border-b border-gray-200">
            <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Cuenta Verificada</span>
            <h2 class="text-xl font-black text-gray-900 mt-1">{{ nombreAbonado || 'Abonado' }}</h2>
          </div>

          <form @submit.prevent="enviarCodigos" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
              <input
                v-model="contacto.email"
                type="email"
                placeholder="tucorreo@ejemplo.com"
                required
                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm"
              />
              <p class="text-[11px] text-gray-500 mt-1.5">Aquí llegará tu aviso de cobranza a partir de ahora.</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">N° de Celular</label>
              <input
                :value="contacto.phone"
                @input="onPhoneInput"
                type="text"
                inputmode="numeric"
                placeholder="Ejemplo: 71234567"
                maxlength="15"
                required
                class="w-full sm:w-64 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono"
              />
              <p class="text-[11px] text-gray-500 mt-1.5">Recibirás notificaciones por SMS a través de nuestro servicio con Tigo.</p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
            >
              {{ loading ? 'Enviando códigos...' : 'Enviar Códigos de Verificación' }}
            </button>
          </form>
        </div>

        <!-- STEP 3: Confirmar códigos -->
        <div v-if="paso === 3" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
          <div class="pb-4 border-b border-gray-200">
            <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Último Paso</span>
            <h2 class="text-xl font-black text-gray-900 mt-1">Confirma los códigos que enviamos</h2>
            <p class="text-xs text-gray-600 mt-1">
              Enviamos un código a <span class="font-semibold text-gray-800">{{ contacto.email }}</span>
              y otro por SMS al <span class="font-semibold text-gray-800">{{ contacto.phone }}</span>. Ambos vencen en 10 minutos.
            </p>
          </div>

          <form @submit.prevent="confirmarCodigos" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Código de Correo</label>
                <input
                  v-model="codigos.codigo_email"
                  type="text"
                  inputmode="numeric"
                  maxlength="6"
                  placeholder="123456"
                  required
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono tracking-[0.3em] text-center"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Código de SMS</label>
                <input
                  v-model="codigos.codigo_sms"
                  type="text"
                  inputmode="numeric"
                  maxlength="6"
                  placeholder="123456"
                  required
                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-900 text-sm font-mono tracking-[0.3em] text-center"
                />
              </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
              <button
                type="submit"
                :disabled="loading"
                class="w-full sm:w-auto px-8 py-3 bg-amber-500 hover:bg-amber-400 text-blue-950 font-extrabold rounded-xl transition-all shadow-md text-sm disabled:opacity-50"
              >
                {{ loading ? 'Confirmando...' : 'Confirmar' }}
              </button>

              <button
                type="button"
                @click="enviarCodigos"
                :disabled="loading"
                class="w-full sm:w-auto px-4 py-3 sm:py-2 text-blue-900 font-bold text-xs uppercase tracking-wider hover:underline disabled:opacity-50"
              >
                Reenviar códigos
              </button>
            </div>
          </form>
        </div>

        <!-- STEP 4: Éxito -->
        <div v-if="paso === 4" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 sm:p-10 text-center space-y-4">
          <svg class="w-14 h-14 text-emerald-500 mx-auto" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
          <h2 class="text-2xl font-black text-emerald-900">¡Datos actualizados!</h2>
          <p class="text-sm text-emerald-800 max-w-md mx-auto">
            Tu correo y celular quedaron confirmados. A partir de ahora tu aviso de cobranza
            llegará a tu correo electrónico -- ¡gracias por ayudarnos a reducir el papel impreso!
          </p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

const paso = ref(1);
const loading = ref(false);
const error = ref('');
const formatError = ref('');
const nombreAbonado = ref('');

const verificacion = reactive({ nro_cliente: '', nro_cuenta: '' });
const contacto = reactive({ email: '', phone: '' });
const codigos = reactive({ codigo_email: '', codigo_sms: '' });

const getCookie = (name) => {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
};

const postJson = async (url, body) => {
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
    },
    body: JSON.stringify(body),
  });

  return response.json();
};

const onNroCuentaInput = (e) => {
  const digits = e.target.value.replace(/\D/g, '').slice(0, 10);
  let formatted = digits;
  if (digits.length > 5) {
    formatted = `${digits.slice(0, 2)}-${digits.slice(2, 5)}-${digits.slice(5)}`;
  } else if (digits.length > 2) {
    formatted = `${digits.slice(0, 2)}-${digits.slice(2)}`;
  }
  verificacion.nro_cuenta = formatted;
  e.target.value = formatted;
};

const onPhoneInput = (e) => {
  const digits = e.target.value.replace(/\D/g, '').slice(0, 15);
  contacto.phone = digits;
  e.target.value = digits;
};

const verificarCuenta = async () => {
  error.value = '';
  formatError.value = '';

  const partes = verificacion.nro_cuenta.split('-').map((p) => p.trim()).filter(Boolean);
  if (partes.length !== 3) {
    formatError.value = 'El N° de Cuenta debe tener el formato Zona-Manzano-Correlativo, ej. 9-35-4000.';
    return;
  }
  const [zona, manzano, correlativo] = partes;

  loading.value = true;
  try {
    const json = await postJson('/api/actualizar-datos/verificar', {
      nro_cliente: verificacion.nro_cliente,
      zona,
      manzano,
      correlativo,
    });

    if (json.success) {
      nombreAbonado.value = json.nombre || '';
      paso.value = 2;
    } else {
      error.value = json.message || 'No se pudo verificar la cuenta.';
    }
  } catch (e) {
    error.value = 'No se pudo conectar con el servidor. Intenta más tarde.';
  } finally {
    loading.value = false;
  }
};

const enviarCodigos = async () => {
  error.value = '';
  loading.value = true;

  try {
    const json = await postJson('/api/actualizar-datos/enviar-codigos', {
      email: contacto.email,
      phone: contacto.phone,
    });

    if (json.success) {
      paso.value = 3;
    } else {
      error.value = json.message || 'No se pudieron enviar los códigos.';
    }
  } catch (e) {
    error.value = 'No se pudo conectar con el servidor. Intenta más tarde.';
  } finally {
    loading.value = false;
  }
};

const confirmarCodigos = async () => {
  error.value = '';
  loading.value = true;

  try {
    const json = await postJson('/api/actualizar-datos/confirmar-codigos', {
      codigo_email: codigos.codigo_email,
      codigo_sms: codigos.codigo_sms,
    });

    if (json.success) {
      paso.value = 4;
    } else {
      error.value = json.message || 'Los códigos no son válidos.';
    }
  } catch (e) {
    error.value = 'No se pudo conectar con el servidor. Intenta más tarde.';
  } finally {
    loading.value = false;
  }
};
</script>
