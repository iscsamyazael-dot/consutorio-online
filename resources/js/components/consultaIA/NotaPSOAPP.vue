<template>
  <div class="psoapp-card">
    <!-- HEADER -->
    <div class="card-header">
      <div class="header-left">
        <h2>📋 Nota PSOAPP</h2>
        <p>Presentación · Subjetivo · Objetivo · Análisis · Plan · Pronóstico</p>
      </div>
      <div class="mode-toggle">
        <button
          type="button"
          :class="['mode-btn', 'ia', { active: modo === 'ia' }]"
          @click="setModo('ia')"
        >🎙️ IA (voz)</button>
        <button
          type="button"
          :class="['mode-btn', 'manual', { active: modo === 'manual' }]"
          @click="setModo('manual')"
        >✍️ Manual</button>
      </div>
    </div>

    <!-- BARRA "ESCUCHANDO" -->
    <div v-if="modo === 'ia'" class="listening-bar">
      <span class="dot"></span> IA escuchando la consulta y llenando la nota por puntos...
    </div>

    <!-- PROGRESO -->
    <div class="progress-row">
      <div
        v-for="s in secciones"
        :key="'p-' + s.key"
        class="progress-seg"
        :class="{ done: estado[s.key].completado }"
      ></div>
    </div>

    <!-- ACORDEÓN -->
    <div class="accordion">
      <div
        v-for="(s, i) in secciones"
        :key="s.key"
        class="item"
        :class="{ open: abierto === s.key }"
      >
        <div class="item-head" @click="toggleItem(s.key)">
          <div class="letter">{{ s.letra }}</div>
          <div class="item-title">
            <b>{{ s.titulo }}</b>
            <span>{{ s.sub }}</span>
          </div>
          <span
            class="status-pill"
            :class="estado[s.key].completado ? 'done' : (modo === 'ia' ? 'ia' : 'pending')"
          >
            {{ estado[s.key].completado ? 'Completado' : (modo === 'ia' ? 'Esperando IA' : 'Pendiente') }}
          </span>
          <span class="chevron">▾</span>
        </div>

        <div class="item-body" v-show="abierto === s.key">
          <textarea
            :value="estado[s.key].texto"
            :placeholder="s.placeholder"
            :readonly="modo === 'ia'"
            @blur="onManualEdit(s.key, $event.target.value)"
          ></textarea>
          <div class="hint">
            {{ modo === 'ia'
              ? 'La IA llenará este punto conforme avance la consulta.'
              : 'Edición manual — se guarda al salir del campo.' }}
          </div>
        </div>
      </div>
    </div>

    <!-- ACCIONES -->
    <div class="actions">
      <div class="actions-row">
        <button class="btn receta" :disabled="descargando" @click="descargar('receta')">
          {{ descargando ? '⏳ Generando...' : '🖨️ Descargar receta (PDF)' }}
        </button>
        <button class="btn diagnostico" :disabled="descargando" @click="descargar('diagnostico')">
          {{ descargando ? '⏳ Generando...' : '📄 Descargar diagnóstico (PDF)' }}
        </button>
      </div>
      <select
        class="save-select"
        v-model="opcionGuardar"
        :disabled="guardando"
        @change="guardar(opcionGuardar)"
      >
        <option value="" disabled>{{ guardando ? 'Guardando...' : 'Guardar nota como...' }}</option>
        <option value="borrador">Borrador</option>
        <option value="final">Nota final (firmada)</option>
      </select>
    </div>

    <!-- TOAST -->
    <transition name="fade">
      <div v-if="toastMsg" class="toast">{{ toastMsg }}</div>
    </transition>
  </div>
</template>

<script>
// Mapeo entre las llaves que devuelve el backend (IAClinicaService::consultarIA)
// dentro de "nota_psoapp" y las llaves internas del acordeón de este componente.
// Backend -> Frontend
const MAPA_BACKEND_A_FRONTEND = {
  presentacion: 'P1',
  subjetivo: 'S',
  objetivo: 'O',
  analisis: 'A',
  plan: 'P2',
  pronostico: 'P3'
};

export default {
  name: 'NotaPSOAPP',
  props: {
    // Id de la consulta activa, para guardar/descargar
    consultaId: { type: [String, Number], default: null },
    // Objeto "nota_psoapp" tal cual lo devuelve el backend, ej:
    // ia_data.nota_psoapp = { presentacion, subjetivo, objetivo, analisis, plan, pronostico }
    // Pásalo desde el padre cada vez que llegue una respuesta nueva de la IA.
    notaPsoapp: { type: Object, default: null }
  },
  data() {
    return {
      modo: 'ia', // 'ia' | 'manual'
      abierto: 'P1',
      opcionGuardar: '',
      toastMsg: '',
      toastTimer: null,
      guardando: false,
      descargando: false,
      secciones: [
        {
          key: 'P1', letra: 'P', titulo: 'Presentación',
          sub: 'Ficha de identificación y motivo de consulta',
          placeholder: 'Nombre/expediente, edad, sexo, ocupación, motivo de consulta, antecedentes relevantes...'
        },
        {
          key: 'S', letra: 'S', titulo: 'Subjetivo',
          sub: 'Anamnesis / padecimiento actual referido por el paciente',
          placeholder: 'Inicio, localización, intensidad, tipo de dolor, agravantes/aliviantes, síntomas asociados...'
        },
        {
          key: 'O', letra: 'O', titulo: 'Objetivo',
          sub: 'Exploración física y estudios',
          placeholder: 'Signos vitales, exploración física por regiones, laboratorios/imagen...'
        },
        {
          key: 'A', letra: 'A', titulo: 'Análisis',
          sub: 'Apreciación clínico-diagnóstica y razonamiento',
          placeholder: 'Diagnóstico principal y diferenciales, razonamiento clínico, escalas de riesgo...'
        },
        {
          key: 'P2', letra: 'P', titulo: 'Plan',
          sub: 'Diagnóstico, terapéutico y educativo',
          placeholder: 'Estudios a solicitar, tratamiento (dosis/vía/frecuencia), indicaciones al paciente...'
        },
        {
          key: 'P3', letra: 'P', titulo: 'Pronóstico',
          sub: 'Evolución esperada para la vida y la función',
          placeholder: 'Pronóstico para la vida, para la función y factores determinantes...'
        }
      ],
      estado: {
        P1: { texto: '', completado: false },
        S:  { texto: '', completado: false },
        O:  { texto: '', completado: false },
        A:  { texto: '', completado: false },
        P2: { texto: '', completado: false },
        P3: { texto: '', completado: false }
      }
    };
  },
  watch: {
    // Cada vez que el padre pase un nuevo nota_psoapp (por ejemplo, tras
    // recibir la respuesta de /consultas/ia o /consultas/subir-archivo),
    // lo repartimos automáticamente a las secciones del acordeón.
    notaPsoapp: {
      immediate: true,
      handler(nuevoValor) {
        if (nuevoValor) {
          this.cargarDesdeIA(nuevoValor);
        }
      }
    }
  },
  methods: {
    toggleItem(key) {
      this.abierto = this.abierto === key ? null : key;
    },
    setModo(m) {
      this.modo = m;
    },
    onManualEdit(key, valor) {
      this.estado[key].texto = valor;
      this.estado[key].completado = valor.trim().length > 0;
      this.mostrarToast('Guardado ✓');
      // Aquí puedes emitir el cambio al padre o llamar a tu API de autosave:
      this.$emit('psoapp-actualizado', { key, valor, consultaId: this.consultaId });
    },
    // Llama este método desde tu servicio de IA / websocket de transcripción
    // cada vez que detectes que un fragmento de la consulta pertenece a un punto.
    // Ejemplo: this.$refs.notaPsoapp.actualizarDesdeIA('S', 'Refiere dolor lumbar...')
    actualizarDesdeIA(key, texto) {
      if (!this.estado[key]) {
        return
      }
      if (!texto || !texto.trim()) {
        return
      }
      const textoNuevo = texto.trim()
      const textoActual = this.estado[key].texto.trim()
      // Si no existe información previa,
      // simplemente agregamos el nuevo contenido
      if (!textoActual) {
        this.estado[key].texto = textoNuevo
      } else {
        // Evitar duplicar exactamente el mismo contenido
        if (!textoActual.includes(textoNuevo)) {
          this.estado[key].texto =
            textoActual + '\n\n' + textoNuevo
        }
      }
      this.estado[key].completado =
        this.estado[key].texto.trim().length > 0
    },
    // Recibe el objeto "nota_psoapp" completo tal como lo devuelve el
    // backend (IAClinicaService::consultarIA / analizarTranscripcion) y
    // reparte cada campo a la sección correspondiente del acordeón usando
    // MAPA_BACKEND_A_FRONTEND. Reutiliza actualizarDesdeIA() para no
    // duplicar contenido si el mismo texto ya estaba cargado.
    //
    // Ejemplo de uso desde el padre:
    //   this.$refs.notaPsoapp.cargarDesdeIA(iaData.nota_psoapp)
    // o simplemente pasando la prop:
    //   <NotaPSOAPP :nota-psoapp="iaData.nota_psoapp" />
    cargarDesdeIA(notaPsoappBackend) {
      if (!notaPsoappBackend || typeof notaPsoappBackend !== 'object') {
        return;
      }

      Object.entries(MAPA_BACKEND_A_FRONTEND).forEach(([claveBackend, claveFrontend]) => {
        const texto = notaPsoappBackend[claveBackend];

        if (!texto) {
          return;
        }

        // "No disponible" / "Sin datos suficientes..." son respuestas
        // válidas del backend cuando no hay info, pero no aportan nada
        // nuevo al acordeón si ya hay contenido cargado; aun así las
        // dejamos pasar la primera vez para que el usuario vea que la
        // IA sí revisó esa sección.
        this.actualizarDesdeIA(claveFrontend, texto);
      });
    },

    // Descarga el PDF (receta o diagnóstico) llamando a
    // ConsultaIAController::generarPdf, ruta 'consultaIA.generarPdf'
    // (GET consultaIA/{consultaId}/pdf/{tipo}).
    //
    // No usamos axios aquí porque la respuesta es un archivo binario, no
    // JSON: es más simple y robusto dejar que el navegador maneje la
    // descarga directamente, ya que la ruta va protegida por el
    // middleware 'auth' y el navegador ya manda la cookie de sesión al
    // ser una petición same-origin.
    async descargar(tipo) {
      if (!this.consultaId) {
        this.mostrarToast('No hay una consulta activa para generar el PDF');
        return;
      }
      if (this.descargando) return;

      this.descargando = true;
      this.mostrarToast(tipo === 'receta' ? 'Generando receta en PDF...' : 'Generando diagnóstico en PDF...');

      try {
        const url = `/consultaIA/${this.consultaId}/pdf/${tipo}`;

        // Truco para descargar sin salir de la página actual:
        // un <a> temporal con download apuntando a la ruta protegida.
        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.target = '_blank'; // por si el navegador decide abrirlo en vez de descargarlo
        document.body.appendChild(enlace);
        enlace.click();
        document.body.removeChild(enlace);

        this.$emit('psoapp-descargar', { tipo, consultaId: this.consultaId });
      } catch (error) {
        console.error('Error al descargar el PDF:', error);
        this.mostrarToast('No se pudo generar el PDF');
      } finally {
        this.descargando = false;
      }
    },

    // Guarda la nota PSOAPP (borrador o final) llamando a
    // ConsultaIAController::guardarPsoapp, ruta 'consultaIA.guardarPsoapp'
    // (POST consultaIA/{consultaId}/psoapp).
    async guardar(valor) {
      if (!valor) return;

      if (!this.consultaId) {
        this.mostrarToast('No hay una consulta activa para guardar la nota');
        this.opcionGuardar = '';
        return;
      }
      if (this.guardando) return;

      this.guardando = true;

      try {
        const respuesta = await window.axios.post(
          `/consultaIA/${this.consultaId}/psoapp`,
          {
            estado: valor,
            contenido: this.estado
          }
        );

        if (respuesta.data && respuesta.data.success) {
          this.mostrarToast(
            valor === 'borrador'
              ? 'Guardado como borrador'
              : 'Nota final guardada en el expediente'
          );
          this.$emit('psoapp-guardar', {
            estado: valor,
            contenido: this.estado,
            consultaId: this.consultaId,
            notaId: respuesta.data.nota_id
          });
        } else {
          this.mostrarToast('No se pudo guardar la nota PSOAPP');
        }
      } catch (error) {
        console.error('Error al guardar la nota PSOAPP:', error);
        const mensaje = error?.response?.data?.error || 'No se pudo guardar la nota PSOAPP';
        this.mostrarToast(mensaje);
      } finally {
        this.guardando = false;
        // Reseteamos el select para que se pueda volver a elegir la misma
        // opción después (si no, @change no dispara dos veces seguidas
        // con el mismo valor).
        this.opcionGuardar = '';
      }
    },

    mostrarToast(msg) {
      this.toastMsg = msg;
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toastMsg = ''; }, 2200);
    }
  }
};
</script>

<style scoped>
.psoapp-card {
  --bg: #f3f5f9;
  --card: #ffffff;
  --border: #e5e9f0;
  --blue: #2f6feb;
  --blue-dark: #1d4ed8;
  --green: #16a34a;
  --amber-bg: #fff8e6;
  --amber-border: #f2d382;
  --text: #1f2937;
  --text-light: #6b7280;

  width: 100% !important;
  max-width: none !important;
  min-width: 0 !important;

  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(16,24,40,.04);
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  color: var(--text);
  position: relative;
  box-sizing: border-box;
}
.card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 18px; border-bottom: 1px solid var(--border);
}
.card-header h2 { margin: 0; font-size: 15px; font-weight: 700; color: var(--blue-dark); }
.card-header p { margin: 2px 0 0; font-size: 12px; color: var(--text-light); }
.header-left { display: flex; flex-direction: column; }

.mode-toggle {
  display: flex; align-items: center; gap: 2px; background: #f2f4f8;
  border: 1px solid var(--border); border-radius: 999px; padding: 3px;
}
.mode-btn {
  border: none; background: transparent; padding: 6px 12px; border-radius: 999px;
  cursor: pointer; color: var(--text-light); font-size: 12px; font-weight: 600; transition: .15s;
}
.mode-btn.ia.active { background: var(--blue); color: #fff; }
.mode-btn.manual.active { background: var(--text); color: #fff; }

.listening-bar {
  display: flex; align-items: center; gap: 8px; padding: 10px 18px;
  background: #eef7ff; border-bottom: 1px solid var(--border);
  font-size: 12px; color: var(--blue-dark); font-weight: 600;
}
.dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: pulse 1.2s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

.progress-row { display: flex; gap: 6px; padding: 14px 18px 0; }
.progress-seg { height: 5px; flex: 1; border-radius: 4px; background: #e5e9f0; }
.progress-seg.done { background: var(--green); }

.accordion { padding: 10px 10px 16px; }
.item { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; overflow: hidden; }
.item-head { display: flex; align-items: center; gap: 12px; padding: 12px 14px; cursor: pointer; background: #fafbfd; }
.letter {
  width: 30px; height: 30px; border-radius: 8px; background: var(--blue); color: #fff;
  display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;
}
.item-title { flex: 1; }
.item-title b { font-size: 13.5px; display: block; }
.item-title span { font-size: 11.5px; color: var(--text-light); }
.status-pill { font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }
.status-pill.pending { background: #f1f2f4; color: var(--text-light); }
.status-pill.ia { background: var(--amber-bg); color: #92700f; border: 1px solid var(--amber-border); }
.status-pill.done { background: #e8f6ee; color: #146c43; border: 1px solid #bfe8d2; }
.chevron { font-size: 12px; color: var(--text-light); margin-left: 4px; transition: .2s; }
.item.open .chevron { transform: rotate(180deg); }

.item-body { padding: 0 14px 14px 56px; }
textarea {
  width: 100%; min-height: 74px; resize: vertical; border: 1px solid var(--border); border-radius: 8px;
  padding: 10px; font-size: 13px; font-family: inherit; color: var(--text); background: #fff;
}
textarea:focus { outline: 2px solid #cbdcfd; border-color: var(--blue); }
.hint { font-size: 11px; color: var(--text-light); margin-top: 5px; }

.actions { display: flex; flex-direction: column; gap: 10px; padding: 16px 18px; border-top: 1px solid var(--border); background: #fafbfd; }
.actions-row { display: flex; gap: 10px; }
.btn { flex: 1; border: none; border-radius: 8px; padding: 11px 12px; font-size: 13px; font-weight: 700; cursor: pointer; }
.btn.receta { background: var(--blue); color: #fff; }
.btn.diagnostico { background: #fff; color: var(--blue-dark); border: 1px solid var(--blue); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.save-select {
  width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px;
  font-size: 13px; font-weight: 600; color: var(--text); background: #fff;
}
.save-select:disabled { opacity: 0.6; cursor: not-allowed; }

.toast {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
  background: #111827; color: #fff; padding: 8px 16px; border-radius: 8px;
  font-size: 12.5px; font-weight: 600;
}
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>