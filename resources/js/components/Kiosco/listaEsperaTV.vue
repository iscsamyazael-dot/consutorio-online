<template>
  <div class="tv">
    <!-- ░░ ESTADO: dispositivo sin emparejar ░░ -->
    <div v-if="estado === 'emparejar'" class="tv__emparejar">
      <p class="icono-grande" aria-hidden="true">⚠</p>
      <h2>Esta pantalla no está emparejada</h2>
      <p class="texto-apoyo">
        Escribe el código de emparejamiento generado desde el panel administrativo
        (Configuración del sistema → Dispositivos → Agregar nuevo dispositivo).
      </p>

      <input
        ref="inputEmparejamientoRef"
        v-model="codigoEmparejamiento"
        type="text"
        inputmode="numeric"
        autocomplete="one-time-code"
        class="input-grande"
        placeholder="Ej. 649903"
        @keyup.enter="emparejarDispositivo"
      />

      <p v-if="mensajeError" class="texto-apoyo texto-apoyo--error">{{ mensajeError }}</p>

      <button
        class="boton boton--primario"
        :disabled="!codigoEmparejamiento.trim() || emparejando"
        @click="emparejarDispositivo"
      >
        {{ emparejando ? 'Verificando…' : 'Emparejar esta pantalla' }}
      </button>
    </div>

    <!-- ░░ ESTADO: pantalla activa ░░ -->
    <template v-else>
      <header class="tv__header">
        <div class="tv__marca">
          <div class="tv__logo-mark" aria-hidden="true"></div>
          <h1>Sala de espera</h1>
        </div>
        <div class="tv__reloj">{{ horaActual }}</div>
      </header>

      <main class="tv__lienzo">
        
        <!-- ░░ SECCIÓN 1: PACIENTES LLAMANDO (DESTACADO) ░░ -->
        <transition-group name="banner" tag="div" class="tv__llamados" v-if="pacientesLlamando.length > 0">
          <div v-for="r in pacientesLlamando" :key="r.numero_turno" class="banner-llamado">
            
            <div class="banner-llamado__turno">
              <span>{{ r.numero_turno }}</span>
            </div>
            
            <div class="banner-llamado__info">
              <span class="banner-llamado__etiqueta">¡PASE A CONSULTA!</span>
              <h2 class="banner-llamado__nombre">{{ r.nombre_completo || r.nombre_corto || '—' }}</h2>
            </div>
            
            <div class="banner-llamado__destino">
              <span class="banner-llamado__destino-icono" aria-hidden="true">🚪</span>
              <div class="banner-llamado__destino-texto">
                <span class="destino-label">Diríjase a</span>
                <span class="destino-valor">{{ r.consultorio || 'Consultorio' }}</span>
              </div>
            </div>

          </div>
        </transition-group>

        <!-- ░░ SECCIÓN 2: LISTA DE ESPERA GENERAL ░░ -->
        <div class="tv__lista-contenedor" v-if="pacientesEnEspera.length > 0">
          <!-- Encabezados de columna -->
          <div class="fila fila--head">
            <span class="col col--turno">Turno</span>
            <span class="col col--nombre">Paciente</span>
            <span class="col col--consultorio">Consultorio</span>
            <span class="col col--estado">Estado</span>
          </div>

          <!-- Lista -->
          <transition-group name="fila" tag="div" class="tv__lista">
            <div
              v-for="r in pacientesEnEspera"
              :key="r.numero_turno"
              class="fila"
            >
              <span class="col col--turno">
                <span class="turno-badge">{{ r.numero_turno }}</span>
              </span>
              <span class="col col--nombre">{{ r.nombre_completo || r.nombre_corto || '—' }}</span>
              <span class="col col--consultorio">
                {{ r.consultorio || 'Por asignar' }}
              </span>
              <span class="col col--estado">
                <span class="chip" :class="chipClase(r.estado)">{{ r.estado }}</span>
              </span>
            </div>
          </transition-group>
        </div>

        <!-- ░░ ESTADO VACÍO ░░ -->
        <div v-if="registros.length === 0" class="tv__vacio">
          <p class="icono-grande" aria-hidden="true">🕊</p>
          <p class="texto-apoyo">No hay pacientes en espera en este momento.</p>
        </div>
      </main>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import KioscoApiService from '../../services/KioscoApiService.js'

const TOKEN_KEY = 'kiosco_device_token'
const INTERVALO_POLLING_MS = 5000
const INTERVALO_RELOJ_MS = 1000

const estado = ref('pantalla')
const registros = ref([])
const horaActual = ref('')

const codigoEmparejamiento = ref('')
const inputEmparejamientoRef = ref(null)
const emparejando = ref(false)
const mensajeError = ref('')

let temporizadorPolling = null
let temporizadorReloj = null

const PRIORIDAD_ESTADO = { 'En espera': 1, 'En consulta': 2 }

// 1. Filtramos los que están "Llamando" para mostrarlos gigantes
const pacientesLlamando = computed(() => {
  return registros.value
    .filter(r => r.estado === 'Llamando')
    .sort((a, b) => a.numero_turno - b.numero_turno)
})

// 2. Filtramos el resto para la lista normal
const pacientesEnEspera = computed(() => {
  return registros.value
    .filter(r => r.estado !== 'Llamando')
    .sort((a, b) => {
      const prioridadA = PRIORIDAD_ESTADO[a.estado] ?? 99
      const prioridadB = PRIORIDAD_ESTADO[b.estado] ?? 99
      if (prioridadA !== prioridadB) return prioridadA - prioridadB
      return a.numero_turno - b.numero_turno
    })
})

function chipClase(valorEstado) {
  switch (valorEstado) {
    case 'En espera': return 'chip--blue'
    case 'En consulta':  return 'chip--gray'
    default:           return 'chip--gray'
  }
}

function actualizarReloj() {
  horaActual.value = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
}

async function cargarPantalla() {
  try {
    const { data } = await KioscoApiService.get('/api/kiosco/lista-espera-pantalla')
    // 🔍 DEPURACIÓN: Imprimimos en consola lo que responde el servidor en cada polling
    console.log('📡 [Polling TV] Datos recibidos del servidor:', data)
    // Mapeamos los estados del backend para que el usuario vea "En consulta"
    const registrosMapeados = Array.isArray(data) ? data.map(item => {
      let estadoUX = item.estado
      // Si el backend manda "En proceso", la TV lo muestra como "En consulta"
      if (item.estado === 'En espera') {
        estadoUX = 'En consulta'
      }
      return {
        ...item,
        estado: estadoUX
      }
    }) : []
    registros.value = registrosMapeados
    // 🔍 DEPURACIÓN: Imprimimos el estado actual mapeado en memoria
    registros.value.forEach(r => {
      console.log(`👤 Paciente: ${r.nombre_completo} | Turno: ${r.numero_turno} | Estado en TV: "${r.estado}"`)
    })
  } catch (error) {
    console.error('Error al cargar la pantalla de sala de espera:', error)
    if (error.response?.status === 401 || error.response?.status === 403) {
      requerirNuevoEmparejamiento()
    }
  }
}

function iniciarPolling() {
  detenerPolling()
  cargarPantalla()
  temporizadorPolling = setInterval(cargarPantalla, INTERVALO_POLLING_MS)
}

function detenerPolling() {
  clearInterval(temporizadorPolling)
  temporizadorPolling = null
}

async function emparejarDispositivo() {
  const codigo = codigoEmparejamiento.value.trim()
  if (!codigo) return

  mensajeError.value = ''
  emparejando.value = true
  try {
    const { data } = await KioscoApiService.post('/api/kiosco/dispositivos/emparejar', {
      codigo,
      tipo: 'tv',
    })

    localStorage.setItem(TOKEN_KEY, data.token)
    codigoEmparejamiento.value = ''
    estado.value = 'pantalla'
    await nextTick()
    iniciarPolling()
  } catch (error) {
    console.error('Error al emparejar la pantalla:', error)
    mensajeError.value = error.response?.data?.message
      || 'Código inválido o expirado. Verifica e intenta de nuevo.'
  } finally {
    emparejando.value = false
  }
}

function requerirNuevoEmparejamiento() {
  localStorage.removeItem(TOKEN_KEY)
  detenerPolling()
  registros.value = []
  codigoEmparejamiento.value = ''
  estado.value = 'emparejar'
  nextTick(() => inputEmparejamientoRef.value?.focus())
}

onMounted(() => {
  actualizarReloj()
  temporizadorReloj = setInterval(actualizarReloj, INTERVALO_RELOJ_MS)

  if (!localStorage.getItem(TOKEN_KEY)) {
    estado.value = 'emparejar'
    nextTick(() => inputEmparejamientoRef.value?.focus())
    return
  }

  estado.value = 'pantalla'
  iniciarPolling()
})

onBeforeUnmount(() => {
  detenerPolling()
  clearInterval(temporizadorReloj)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.tv {
  --color-fondo: #F7F9F5;
  --color-marca: #2D6A4F;
  --color-marca-oscuro: #1E4A37;
  --color-texto: #1B2B23;
  --color-muted: #6B8577;
  --color-acento: #E8871E;
  --color-error: #C1443C;
  --color-borde: #DCE6DE;

  --fuente-titular: 'Quicksand', sans-serif;
  --fuente-cuerpo: 'Inter', sans-serif;

  min-height: 100vh;
  width: 100%;
  background: var(--color-fondo);
  color: var(--color-texto);
  font-family: var(--fuente-cuerpo);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ──────────────────────────────────────────
   Pantalla de emparejamiento
   ────────────────────────────────────────── */
.tv__emparejar { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; max-width: 560px; margin: 0 auto; padding: 40px; text-align: center; }
.tv__emparejar h2 { font-family: var(--fuente-titular); font-weight: 600; font-size: 1.9rem; margin: 0; }
.icono-grande { font-size: 3rem; margin: 0; color: var(--color-error); }
.texto-apoyo { color: var(--color-muted); font-size: 1.1rem; line-height: 1.5; margin: 0; }
.texto-apoyo--error { color: var(--color-error); }
.input-grande { width: 100%; font-size: 1.3rem; padding: 18px 20px; border-radius: 14px; border: 2px solid var(--color-borde); font-family: var(--fuente-cuerpo); color: var(--color-texto); text-align: center; }
.input-grande:focus { outline: 3px solid var(--color-acento); outline-offset: 1px; border-color: var(--color-acento); }
.boton { font-family: var(--fuente-cuerpo); font-weight: 600; font-size: 1.15rem; padding: 18px 40px; border-radius: 16px; border: none; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease; }
.boton:active { transform: scale(0.97); }
.boton:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.boton--primario { background: var(--color-marca); color: #fff; }
.boton--primario:not(:disabled):hover { background: var(--color-marca-oscuro); }

/* ──────────────────────────────────────────
   Encabezado
   ────────────────────────────────────────── */
.tv__header { display: flex; align-items: center; justify-content: space-between; padding: 32px 56px; flex-shrink: 0; }
.tv__marca { display: flex; align-items: center; gap: 18px; }
.tv__logo-mark { width: 18px; height: 18px; border-radius: 50%; background: var(--color-marca); flex-shrink: 0; }
.tv__marca h1 { font-family: var(--fuente-titular); font-weight: 600; font-size: 2.1rem; color: var(--color-marca-oscuro); margin: 0; }
.tv__reloj { font-family: 'IBM Plex Mono', var(--fuente-cuerpo), monospace; font-size: 2rem; font-weight: 600; color: var(--color-muted); font-variant-numeric: tabular-nums; }

/* ──────────────────────────────────────────
   Lienzo Principal
   ────────────────────────────────────────── */
.tv__lienzo { flex: 1; padding: 0 56px 48px; overflow-y: auto; display: flex; flex-direction: column; gap: 40px; }

/* ░░ Banners de Llamado (Jerarquía Principal) ░░ */
.tv__llamados { display: flex; flex-direction: column; gap: 20px; }

.banner-llamado {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 32px;
  background: linear-gradient(135deg, #FFEDD5 0%, #FCEBD5 100%);
  border: 4px solid var(--color-acento);
  border-radius: 24px;
  padding: 32px 40px;
  box-shadow: 0 20px 40px rgba(232, 135, 30, 0.15);
  animation: pulso-banner 2s infinite alternate;
}

@keyframes pulso-banner {
  0% { box-shadow: 0 10px 25px rgba(232, 135, 30, 0.1); border-color: rgba(232, 135, 30, 0.5); }
  100% { box-shadow: 0 20px 50px rgba(232, 135, 30, 0.35); border-color: var(--color-acento); }
}

.banner-llamado__turno {
  background: var(--color-acento);
  color: #fff;
  font-family: 'IBM Plex Mono', var(--fuente-cuerpo), monospace;
  font-size: 4rem;
  font-weight: 700;
  padding: 16px 32px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.banner-llamado__info { display: flex; flex-direction: column; gap: 4px; }
.banner-llamado__etiqueta { font-size: 1.2rem; font-weight: 700; color: #B45309; text-transform: uppercase; letter-spacing: 0.1em; }
.banner-llamado__nombre { font-size: 2.8rem; font-weight: 700; color: #78350F; margin: 0; line-height: 1.1; }

.banner-llamado__destino {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fff;
  padding: 20px 32px;
  border-radius: 16px;
}
.banner-llamado__destino-icono { font-size: 3rem; }
.banner-llamado__destino-texto { display: flex; flex-direction: column; }
.destino-label { font-size: 1rem; color: var(--color-muted); font-weight: 600; text-transform: uppercase; }
.destino-valor { font-size: 2rem; font-weight: 700; color: var(--color-marca-oscuro); }

/* ░░ Tabla General de Espera ░░ */
.tv__lista-contenedor { display: flex; flex-direction: column; gap: 8px; }

.fila {
  display: grid;
  grid-template-columns: 140px 1fr 220px 200px;
  align-items: center;
  gap: 24px;
  padding: 22px 28px;
  border-radius: 18px;
  margin-bottom: 14px;
  background: #fff;
  border: 2px solid var(--color-borde);
}

.fila--head { background: transparent; border: none; padding: 0 28px 12px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.85rem; font-weight: 700; color: var(--color-muted); }
.col--turno { display: flex; }
.turno-badge { font-family: 'IBM Plex Mono', var(--fuente-cuerpo), monospace; font-weight: 700; font-size: 1.6rem; color: var(--color-marca-oscuro); background: #EAF3EE; border-radius: 12px; padding: 6px 16px; }
.col--nombre { font-size: 1.5rem; font-weight: 600; color: var(--color-texto); }
.col--consultorio { font-size: 1.15rem; color: var(--color-muted); }

.chip { display: inline-block; font-size: 1rem; font-weight: 700; padding: 8px 18px; border-radius: 999px; letter-spacing: 0.02em; }
.chip--blue  { background: #dbeafe; color: #1e40af; }
.chip--gray  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

.tv__vacio { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 80px 20px; text-align: center; }

/* Animaciones de entrada/salida de Vue Transition */
.banner-enter-active, .banner-leave-active, .fila-enter-active, .fila-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.banner-enter-from, .banner-leave-to, .fila-enter-from, .fila-leave-to { opacity: 0; transform: translateY(-20px) scale(0.98); }
.fila-move { transition: transform 0.4s ease; }

@media (prefers-reduced-motion: reduce) {
  .banner-llamado { animation: none; }
  .banner-enter-active, .banner-leave-active, .fila-enter-active, .fila-leave-active, .fila-move { transition: none; }
}
</style>