<template>
  <div class="kiosco">
    <!-- Encabezado fijo -->
    <header class="kiosco__header">
      <div class="kiosco__logo-mark" aria-hidden="true"></div>
      <h1 class="kiosco__titulo">Registro de llegada</h1>
    </header>

    <main class="kiosco__lienzo">

      <!-- ░░ ESTADO: dispositivo sin emparejar → AHORA MUESTRA FORMULARIO ░░ -->
      <section v-if="estado === 'emparejar'" class="pantalla pantalla--formulario">
        <div class="pantalla--centrada" style="margin-bottom: 8px;">
          <p class="icono-grande" aria-hidden="true">⚠</p>
          <h2>Este dispositivo no está emparejado</h2>
          <p class="texto-apoyo">
            Escribe el código de emparejamiento generado desde el panel administrativo
            (Configuración del sistema → Dispositivos → Agregar nuevo dispositivo).
          </p>
        </div>

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

        <p v-if="mensajeError" class="texto-apoyo" style="color: var(--color-error); text-align:center;">
          {{ mensajeError }}
        </p>

        <button
          class="boton boton--primario boton--ancho"
          style="align-self:center;"
          :disabled="!codigoEmparejamiento.trim() || emparejando"
          @click="emparejarDispositivo"
        >
          {{ emparejando ? 'Verificando…' : 'Emparejar dispositivo' }}
        </button>
      </section>

      <!-- ░░ ESTADO: inicio — escáner activo + accesos manuales ░░ -->
      <section v-else-if="estado === 'inicio'" class="pantalla">
        <div class="escaner">
          <video ref="videoRef" class="escaner__video" playsinline></video>
          <div class="escaner__marco" aria-hidden="true">
            <span class="escaner__esquina escaner__esquina--tl"></span>
            <span class="escaner__esquina escaner__esquina--tr"></span>
            <span class="escaner__esquina escaner__esquina--bl"></span>
            <span class="escaner__esquina escaner__esquina--br"></span>
          </div>
          <p class="escaner__ayuda">Coloca tu código QR frente a la cámara</p>
        </div>

        <div class="divisor">
          <span>o</span>
        </div>

        <button class="boton boton--secundario boton--ancho" @click="abrirEntradaManual">
          Escribir mi folio o nombre
        </button>
      </section>

      <!-- ░░ ESTADO: entrada manual (folio o nombre) ░░ -->
      <section v-else-if="estado === 'manual'" class="pantalla pantalla--formulario">
        <button class="boton-volver" @click="volverAInicio" aria-label="Volver">‹ Volver</button>
        <h2>Escribe tu folio o tu nombre completo</h2>
        <input
          ref="inputManualRef"
          v-model="codigoManual"
          type="text"
          class="input-grande"
          placeholder="Ej. CIT-2026-0045 o María López García"
          @keyup.enter="buscar(codigoManual)"
        />
        <button
          class="boton boton--primario boton--ancho"
          :disabled="!codigoManual.trim() || cargando"
          @click="buscar(codigoManual)"
        >
          {{ cargando ? 'Buscando…' : 'Buscar' }}
        </button>
      </section>

      <!-- ░░ ESTADO: varias coincidencias por nombre ░░ -->
      <section v-else-if="estado === 'coincidencias'" class="pantalla pantalla--formulario">
        <button class="boton-volver" @click="volverAInicio" aria-label="Volver">‹ Volver</button>
        <h2>Selecciona tu nombre</h2>
        <ul class="lista-coincidencias">
          <li v-for="p in coincidencias" :key="p.id">
            <button class="fila-paciente" @click="elegirPaciente(p)">
              {{ p.nombre }}
            </button>
          </li>
        </ul>
        <p v-if="coincidencias.length === 0" class="texto-apoyo">
          No encontramos coincidencias. Intenta de nuevo o pide ayuda en recepción.
        </p>
      </section>

      <!-- ░░ ESTADO: confirmar datos ░░ -->
      <section v-else-if="estado === 'confirmar'" class="pantalla pantalla--centrada">
        <p class="etiqueta-ojo">Confirma tus datos</p>
        <h2 class="nombre-confirmar">{{ pacienteSeleccionado?.nombre }}</h2>
        <div v-if="citaSeleccionada" class="detalle-cita">
          <p><strong>Médico:</strong> {{ citaSeleccionada.medico?.nombre || 'Por asignar' }}</p>
          <p><strong>Hora de cita:</strong> {{ citaSeleccionada.hora || '—' }}</p>
        </div>
        <p v-else class="texto-apoyo">Te registraremos sin cita previa (consulta de paso).</p>

        <button class="boton boton--primario boton--ancho" :disabled="cargando" @click="confirmarRegistro">
          {{ cargando ? 'Registrando…' : 'Confirmar registro' }}
        </button>
        <button class="boton boton--texto" @click="volverAInicio">No soy yo, volver</button>
      </section>

      <!-- ░░ ESTADO: éxito — boleto de turno ░░ -->
      <section v-else-if="estado === 'exito'" class="pantalla pantalla--centrada">
        <div class="boleto">
          <p class="boleto__etiqueta">Tu turno</p>
          <p class="boleto__numero">{{ registroCreado?.numero_turno }}</p>
          <div class="boleto__perforado" aria-hidden="true"></div>
          <p class="boleto__folio">Folio {{ registroCreado?.folio }}</p>
          <p class="boleto__nota">
            {{ yaExistia ? 'Ya estabas registrado, aquí está tu turno.' : 'Toma asiento, te llamaremos en pantalla.' }}
          </p>
        </div>
        <p class="texto-apoyo cuenta-regresiva">Volviendo al inicio en {{ segundosRestantes }}s…</p>
      </section>

      <!-- ░░ ESTADO: no encontrado / error ░░ -->
      <section v-else-if="estado === 'no_encontrado'" class="pantalla pantalla--centrada">
        <p class="icono-grande" aria-hidden="true">✕</p>
        <h2>No encontramos tu registro</h2>
        <p class="texto-apoyo">{{ mensajeError }}</p>
        <button class="boton boton--primario boton--ancho" @click="volverAInicio">Intentar de nuevo</button>
      </section>

    </main>
  </div>
</template>

<script setup>
    import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
    import QrScanner from 'qr-scanner'
    import KioscoApiService from '../../services/KioscoApiService.js'

    // ──────────────────────────────────────────
    // Config: llamadas a /api/kiosco/* vía KioscoApiService,
    // que ya mete el Bearer del dispositivo emparejado en
    // cada request (ver KioscoApiService.js) — por eso aquí
    // ya no se arma el header a mano ni se usa fetch.
    // ──────────────────────────────────────────
    const TOKEN_KEY = 'kiosco_device_token'

    // ──────────────────────────────────────────
    // Estado de la pantalla
    // ──────────────────────────────────────────
    const estado = ref('inicio') // emparejar | inicio | manual | coincidencias | confirmar | exito | no_encontrado
    const cargando = ref(false)
    const mensajeError = ref('')

    // ---- Emparejamiento ----
    const codigoEmparejamiento = ref('')
    const inputEmparejamientoRef = ref(null)
    const emparejando = ref(false)

    const codigoManual = ref('')
    const inputManualRef = ref(null)
    const coincidencias = ref([])
    const pacienteSeleccionado = ref(null)
    const citaSeleccionada = ref(null)
    const registroCreado = ref(null)
    const yaExistia = ref(false)
    const segundosRestantes = ref(8)

    const videoRef = ref(null)
    let qrScanner = null
    let temporizadorReinicio = null

    // ──────────────────────────────────────────
    // Emparejamiento del dispositivo
    // ──────────────────────────────────────────
    async function emparejarDispositivo() {
      const codigo = codigoEmparejamiento.value.trim()
      if (!codigo) return

      mensajeError.value = ''
      emparejando.value = true
      try {
        // Ajusta esta ruta al endpoint real de tu backend para canjear
        // el código de emparejamiento por el token del dispositivo.
        const { data } = await KioscoApiService.post('/api/kiosco/dispositivos/emparejar', { codigo })

        localStorage.setItem(TOKEN_KEY, data.token)
        codigoEmparejamiento.value = ''
        estado.value = 'inicio'
        await nextTick()
        iniciarEscaner()
      } catch (error) {
        console.error('Error al emparejar el dispositivo:', error)
        mensajeError.value = error.response?.data?.message
          || 'Código inválido o expirado. Verifica e intenta de nuevo.'
      } finally {
        emparejando.value = false
      }
    }

    // ──────────────────────────────────────────
    // Escáner de cámara
    // ──────────────────────────────────────────
    async function iniciarEscaner() {
    await nextTick()
    if (!videoRef.value) return

    qrScanner = new QrScanner(
        videoRef.value,
        (resultado) => {
        const texto = resultado?.data?.trim()
        if (texto) {
            pausarEscaner()
            buscar(texto)
        }
        },
        { highlightScanRegion: false, highlightCodeOutline: false, maxScansPerSecond: 5 }
    )

    try {
        await qrScanner.start()
    } catch (e) {
        // Sin cámara disponible: el kiosco sigue funcionando con entrada manual
        console.warn('No se pudo iniciar la cámara, se usará solo entrada manual', e)
    }
    }

    function pausarEscaner() {
    qrScanner?.stop()
    }

    function reanudarEscanerSiAplica() {
    if (estado.value === 'inicio') {
        qrScanner?.start().catch(() => {})
    }
    }

    // ──────────────────────────────────────────
    // Navegación entre estados
    // ──────────────────────────────────────────
    function abrirEntradaManual() {
    pausarEscaner()
    codigoManual.value = ''
    estado.value = 'manual'
    nextTick(() => inputManualRef.value?.focus())
    }

    function volverAInicio() {
    codigoManual.value = ''
    coincidencias.value = []
    pacienteSeleccionado.value = null
    citaSeleccionada.value = null
    mensajeError.value = ''
    estado.value = 'inicio'
    nextTick(reanudarEscanerSiAplica)
    }

    // Cuando el token se pierde/expira a mitad de operación (401/403),
    // regresamos a la pantalla de emparejamiento en vez de a un callejón
    // sin salida.
    function requerirNuevoEmparejamiento() {
      localStorage.removeItem(TOKEN_KEY)
      pausarEscaner()
      codigoEmparejamiento.value = ''
      estado.value = 'emparejar'
      nextTick(() => inputEmparejamientoRef.value?.focus())
    }

    // ──────────────────────────────────────────
    // Búsqueda (QR o manual — el backend detecta
    // automáticamente si es folio de cita CIT-,
    // folio de paciente PAC-, o nombre)
    // ──────────────────────────────────────────
    async function buscar(codigo) {
    const valor = (codigo || '').trim()
    if (!valor) return

    cargando.value = true
    try {
        const { data } = await KioscoApiService.post('/api/kiosco/lista-espera/buscar-paciente', { codigo: valor })

        if (!data.encontrado) {
        mensajeError.value = data.motivo === 'cita_no_encontrada'
            ? 'No encontramos una cita con ese folio para hoy.'
            : data.motivo === 'paciente_no_encontrado'
            ? 'No encontramos un paciente con ese folio.'
            : 'No encontramos coincidencias con ese nombre.'
        estado.value = 'no_encontrado'
        return
        }

        if (data.tipo === 'nombre') {
        coincidencias.value = data.coincidencias || []
        estado.value = 'coincidencias'
        return
        }

        // tipo 'cita' o 'paciente'
        pacienteSeleccionado.value = data.paciente
        citaSeleccionada.value = data.cita || null
        estado.value = 'confirmar'
    } catch (error) {
        console.error('Error al buscar en el kiosco:', error)
        if (error.response?.status === 401 || error.response?.status === 403) {
        requerirNuevoEmparejamiento()
        return
        }
        mensajeError.value = error.response?.data?.message || 'No pudimos completar la búsqueda. Intenta de nuevo.'
        estado.value = 'no_encontrado'
    } finally {
        cargando.value = false
    }
    }

    function elegirPaciente(paciente) {
    pacienteSeleccionado.value = paciente
    citaSeleccionada.value = null // búsqueda por nombre no trae cita asociada
    estado.value = 'confirmar'
    }

    // ──────────────────────────────────────────
    // Confirmar registro en lista_espera
    // ──────────────────────────────────────────
    async function confirmarRegistro() {
    cargando.value = true
    try {
        const { data } = await KioscoApiService.post('/api/kiosco/lista-espera/registrar-desde-kiosco', {
        paciente_id: pacienteSeleccionado.value.id,
        cita_id: citaSeleccionada.value?.id ?? null,
        })

        registroCreado.value = data.registro
        yaExistia.value = !!data.ya_existia
        estado.value = 'exito'
        iniciarCuentaRegresiva()
    } catch (error) {
        console.error('Error al registrar desde el kiosco:', error)
        if (error.response?.status === 401 || error.response?.status === 403) {
        requerirNuevoEmparejamiento()
        return
        }
        mensajeError.value = error.response?.data?.message || 'No pudimos completar tu registro. Intenta de nuevo o pide ayuda en recepción.'
        estado.value = 'no_encontrado'
    } finally {
        cargando.value = false
    }
    }

    function iniciarCuentaRegresiva() {
    segundosRestantes.value = 8
    clearInterval(temporizadorReinicio)
    temporizadorReinicio = setInterval(() => {
        segundosRestantes.value -= 1
        if (segundosRestantes.value <= 0) {
        clearInterval(temporizadorReinicio)
        volverAInicio()
        }
    }, 1000)
    }

    // ──────────────────────────────────────────
    onMounted(() => {
    if (!localStorage.getItem(TOKEN_KEY)) {
        estado.value = 'emparejar'
        nextTick(() => inputEmparejamientoRef.value?.focus())
        return
    }
    iniciarEscaner()
    })

    onBeforeUnmount(() => {
    qrScanner?.destroy()
    clearInterval(temporizadorReinicio)
    })
</script>

<style scoped>
/* ──────────────────────────────────────────
   Tokens
   ────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Inter:wght@400;500;600&display=swap');

.kiosco {
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
   Encabezado
   ────────────────────────────────────────── */
.kiosco__header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 28px 40px;
}

.kiosco__logo-mark {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: var(--color-marca);
  flex-shrink: 0;
}

.kiosco__titulo {
  font-family: var(--fuente-titular);
  font-weight: 600;
  font-size: 1.6rem;
  color: var(--color-marca-oscuro);
  margin: 0;
}

/* ──────────────────────────────────────────
   Lienzo principal
   ────────────────────────────────────────── */
.kiosco__lienzo {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 40px 60px;
}

.pantalla {
  width: 100%;
  max-width: 640px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
}

.pantalla--centrada {
  text-align: center;
}

.pantalla--formulario {
  position: relative;
  align-items: stretch;
  gap: 20px;
}

.pantalla h2 {
  font-family: var(--fuente-titular);
  font-weight: 600;
  font-size: 1.9rem;
  margin: 0;
  color: var(--color-texto);
}

.texto-apoyo {
  color: var(--color-muted);
  font-size: 1.1rem;
  line-height: 1.5;
  margin: 0;
}

.icono-grande {
  font-size: 3rem;
  color: var(--color-error);
  margin: 0;
}

/* ──────────────────────────────────────────
   Escáner
   ────────────────────────────────────────── */
.escaner {
  position: relative;
  width: 100%;
  max-width: 420px;
  aspect-ratio: 1;
  border-radius: 24px;
  overflow: hidden;
  background: var(--color-marca-oscuro);
}

.escaner__video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.escaner__marco {
  position: absolute;
  inset: 32px;
  pointer-events: none;
}

.escaner__esquina {
  position: absolute;
  width: 36px;
  height: 36px;
  border: 4px solid var(--color-acento);
}
.escaner__esquina--tl { top: 0; left: 0; border-right: none; border-bottom: none; border-radius: 8px 0 0 0; }
.escaner__esquina--tr { top: 0; right: 0; border-left: none; border-bottom: none; border-radius: 0 8px 0 0; }
.escaner__esquina--bl { bottom: 0; left: 0; border-right: none; border-top: none; border-radius: 0 0 0 8px; }
.escaner__esquina--br { bottom: 0; right: 0; border-left: none; border-top: none; border-radius: 0 0 8px 0; }

.escaner__ayuda {
  position: absolute;
  bottom: 16px;
  left: 0;
  right: 0;
  text-align: center;
  color: #fff;
  font-size: 0.95rem;
  margin: 0;
}

.divisor {
  display: flex;
  align-items: center;
  gap: 16px;
  width: 100%;
  max-width: 420px;
  color: var(--color-muted);
}
.divisor::before, .divisor::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--color-borde);
}

/* ──────────────────────────────────────────
   Botones
   ────────────────────────────────────────── */
.boton {
  font-family: var(--fuente-cuerpo);
  font-weight: 600;
  font-size: 1.15rem;
  padding: 20px 32px;
  border-radius: 16px;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, background 0.15s ease;
}
.boton:active { transform: scale(0.97); }
.boton:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.boton--ancho { width: 100%; max-width: 420px; }

.boton--primario {
  background: var(--color-marca);
  color: #fff;
}
.boton--primario:not(:disabled):hover { background: var(--color-marca-oscuro); }

.boton--secundario {
  background: #fff;
  color: var(--color-marca-oscuro);
  border: 2px solid var(--color-borde);
}

.boton--texto {
  background: none;
  color: var(--color-muted);
  font-weight: 500;
  padding: 8px;
}

.boton-volver {
  align-self: flex-start;
  background: none;
  border: none;
  color: var(--color-muted);
  font-size: 1rem;
  cursor: pointer;
  padding: 4px 0;
}

/* ──────────────────────────────────────────
   Entrada manual / coincidencias
   ────────────────────────────────────────── */
.input-grande {
  width: 100%;
  font-size: 1.3rem;
  padding: 18px 20px;
  border-radius: 14px;
  border: 2px solid var(--color-borde);
  font-family: var(--fuente-cuerpo);
  color: var(--color-texto);
}
.input-grande:focus {
  outline: 3px solid var(--color-acento);
  outline-offset: 1px;
  border-color: var(--color-acento);
}

.lista-coincidencias {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 50vh;
  overflow-y: auto;
}

.fila-paciente {
  width: 100%;
  text-align: left;
  background: #fff;
  border: 2px solid var(--color-borde);
  border-radius: 14px;
  padding: 18px 20px;
  font-size: 1.15rem;
  color: var(--color-texto);
  cursor: pointer;
}
.fila-paciente:hover { border-color: var(--color-marca); }

/* ──────────────────────────────────────────
   Confirmación
   ────────────────────────────────────────── */
.etiqueta-ojo {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.85rem;
  color: var(--color-muted);
  font-weight: 600;
  margin: 0;
}

.nombre-confirmar {
  font-family: var(--fuente-titular);
  font-size: 2.2rem;
  color: var(--color-marca-oscuro);
  margin: 0;
}

.detalle-cita {
  background: #fff;
  border: 2px solid var(--color-borde);
  border-radius: 14px;
  padding: 18px 24px;
  width: 100%;
  max-width: 420px;
}
.detalle-cita p { margin: 6px 0; font-size: 1.05rem; }

/* ──────────────────────────────────────────
   Boleto (firma visual)
   ────────────────────────────────────────── */
.boleto {
  background: #fff;
  border-radius: 20px;
  padding: 40px 32px 32px;
  width: 100%;
  max-width: 380px;
  box-shadow: 0 12px 32px rgba(45, 106, 79, 0.16);
  position: relative;
}

.boleto__etiqueta {
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 0.85rem;
  color: var(--color-muted);
  font-weight: 600;
  margin: 0 0 8px;
}

.boleto__numero {
  font-family: var(--fuente-titular);
  font-weight: 700;
  font-size: 5.5rem;
  line-height: 1;
  color: var(--color-acento);
  margin: 0 0 24px;
  font-variant-numeric: tabular-nums;
}

.boleto__perforado {
  height: 0;
  border-top: 2px dashed var(--color-borde);
  position: relative;
  margin-bottom: 20px;
}
.boleto__perforado::before, .boleto__perforado::after {
  content: '';
  position: absolute;
  top: -11px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--color-fondo);
}
.boleto__perforado::before { left: -32px; }
.boleto__perforado::after { right: -32px; }

.boleto__folio {
  font-family: var(--fuente-cuerpo);
  font-weight: 600;
  font-size: 1.05rem;
  color: var(--color-texto);
  margin: 0 0 8px;
}

.boleto__nota {
  color: var(--color-muted);
  font-size: 0.95rem;
  margin: 0;
}

.cuenta-regresiva {
  margin-top: 8px;
  font-size: 0.95rem;
}

/* ──────────────────────────────────────────
   Accesibilidad
   ────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .boton { transition: none; }
}
</style>