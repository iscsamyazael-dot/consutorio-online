<template>
  <div class="relative" ref="wrapper">
    <button
      @click="abierto = !abierto"
      class="relative inline-flex items-center justify-center p-2 rounded-full hover:bg-teal-50 focus:outline-none transition-colors duration-200"
    >
      <!-- Icono campana -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 transition-colors duration-200"
        :class="[store.noLeidas > 0 ? 'campana-animada' : 'text-gray-400']"
        :style="store.noLeidas > 0 ? { color: '#0B7285' } : {}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <span
        v-if="store.noLeidas > 0"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full badge-pulso"
      >
        {{ store.noLeidas }}
      </span>
    </button>

    <!-- Panel desplegable -->
    <Transition name="panel">
      <div
        v-if="abierto"
        class="absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto bg-white rounded-xl shadow-xl ring-1 ring-black/5 z-50 origin-top-right"
      >
        <!-- Header -->
        <div
          class="px-4 py-3 rounded-t-xl flex items-center justify-between sticky top-0 z-10"
          style="background: linear-gradient(90deg, #0B7285, #0d8a9e);"
        >
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
            <span class="font-semibold text-white text-sm">Alertas de medicamentos</span>
          </div>
          <span
            v-if="store.alertas.length"
            class="text-xs font-bold text-white/90 bg-white/20 px-2 py-0.5 rounded-full"
          >
            {{ store.alertas.length }}
          </span>
        </div>

        <!-- Cargando -->
        <div v-if="store.cargando" class="px-4 py-6 text-sm text-gray-500 flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4" style="color:#0B7285" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Cargando...
        </div>

        <!-- Error -->
        <div v-else-if="store.error" class="px-4 py-3 text-sm text-red-600">
          {{ store.error }}
        </div>

        <!-- Vacío -->
        <div v-else-if="store.alertas.length === 0" class="px-4 py-8 text-sm text-gray-400 flex flex-col items-center gap-2">
          <svg class="h-9 w-9 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
          </svg>
          Sin alertas por ahora
        </div>

        <!-- Lista -->
        <TransitionGroup v-else name="lista" tag="ul" class="divide-y divide-gray-100">
          <li
            v-for="(alerta, i) in store.alertas"
            :key="alerta.id"
            @click="store.marcarLeida(alerta.id)"
            class="relative px-4 py-3 pl-3 text-sm cursor-pointer flex items-start gap-3 border-l-4 transition-colors duration-150 hover:bg-gray-50"
            :class="alerta.read ? 'bg-white' : 'bg-teal-50/40'"
            :style="{ borderLeftColor: colorBorde(alerta.tipo), transitionDelay: (i * 30) + 'ms' }"
          >
            <span class="mt-0.5 shrink-0" :style="{ color: colorBorde(alerta.tipo) }">
              <component :is="iconoTipo(alerta.tipo)" class="w-5 h-5" />
            </span>
            <div class="flex-1 min-w-0">
              <p :class="alerta.read ? 'text-gray-700' : 'text-gray-900 font-semibold'" class="truncate">
                {{ alerta.nombre }}
              </p>
              <p class="text-xs mt-0.5" :style="{ color: colorBorde(alerta.tipo) }">
                {{ alerta.mensaje }}
              </p>
            </div>
            <span v-if="!alerta.read" class="mt-1.5 w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
          </li>
        </TransitionGroup>

        <!-- Footer -->
        <div
          v-if="!store.cargando && store.alertas.length > 0"
          class="px-4 py-2 text-center border-t border-gray-100 sticky bottom-0 bg-white"
        >
          <a
            href="/Medicamentos"
            class="text-xs font-semibold hover:underline"
            style="color:#0B7285"
          >
            Ver inventario completo
          </a>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, h } from 'vue'
import { useNotificacionesStore } from '../stores/notificaciones'

const store = useNotificacionesStore()
const abierto = ref(false)
const wrapper = ref(null)

function colorBorde(tipo) {
  switch (tipo) {
    case 'sin_stock': return '#dc2626'
    case 'stock_critico': return '#f97316'
    case 'caducado': return '#dc2626'
    case 'por_caducar': return '#eab308'
    default: return '#9ca3af'
  }
}

// Iconos simples como componentes de render function (sin depender de librerías externas)
const IconTimes = () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M6 18L18 6M6 6l12 12' })
])
const IconWarning = () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.99 4.5h.008v.008h-.008v-.008z' })
])
const IconClock = () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z' })
])
const IconBan = () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M18.36 6.64a9 9 0 11-12.73 0m12.73 0A9 9 0 006 6.64m12.36 0L5.64 18.36' })
])
const IconInfo = () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
])

function iconoTipo(tipo) {
  switch (tipo) {
    case 'sin_stock': return IconTimes
    case 'stock_critico': return IconWarning
    case 'caducado': return IconBan
    case 'por_caducar': return IconClock
    default: return IconInfo
  }
}

function cerrarSiClickFuera(e) {
  if (wrapper.value && !wrapper.value.contains(e.target)) {
    abierto.value = false
  }
}

onMounted(() => {
  store.fetchAlerts()
  document.addEventListener('click', cerrarSiClickFuera)
})

onUnmounted(() => {
  document.removeEventListener('click', cerrarSiClickFuera)
})
</script>

<style scoped>
.campana-animada {
  animation: swing 1.8s ease-in-out infinite;
  transform-origin: top center;
}
@keyframes swing {
  0%, 100% { transform: rotate(0deg); }
  10% { transform: rotate(12deg); }
  20% { transform: rotate(-10deg); }
  30% { transform: rotate(8deg); }
  40% { transform: rotate(-6deg); }
  50% { transform: rotate(3deg); }
  60%, 100% { transform: rotate(0deg); }
}

.badge-pulso {
  animation: pulso 2s ease-in-out infinite;
}
@keyframes pulso {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
  50% { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); }
}

.panel-enter-active,
.panel-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.panel-enter-from,
.panel-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-4px);
}

.lista-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.lista-enter-from {
  opacity: 0;
  transform: translateX(-8px);
}
</style>