<template>
  <div class="psoapp-card">
    <!-- HEADER -->
    <div class="card-header">
      <div class="header-left">
        <div class="eyebrow">
          <span v-for="l in letras" :key="l" class="eyebrow-letter">{{ l }}</span>
        </div>
        <h2>Nota PSOAPP</h2>
        <p>Presentación · Subjetivo · Objetivo · Análisis · Plan · Pronóstico</p>
      </div>
      <div class="mode-toggle">
        <button
          type="button"
          :class="['mode-btn', 'ia', { active: modo === 'ia' }]"
          @click="setModo('ia')"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
          IA (voz)
        </button>
        <button
          type="button"
          :class="['mode-btn', 'manual', { active: modo === 'manual' }]"
          @click="setModo('manual')"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
          Manual
        </button>
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
        :title="s.titulo"
      ></div>
    </div>

    <!-- ACORDEÓN -->
    <div class="accordion">
      <div
        v-for="(s, i) in secciones"
        :key="s.key"
        class="item"
        :class="{ open: abierto === s.key, done: estado[s.key].completado, last: i === secciones.length - 1 }"
      >
        <div class="item-head" @click="toggleItem(s.key)">
          <div class="rail">
            <div class="letter">
              <svg v-if="estado[s.key].completado" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <span v-else>{{ s.letra }}</span>
            </div>
            <div v-if="i !== secciones.length - 1" class="rail-line"></div>
          </div>
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
          <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
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
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          {{ descargando ? 'Generando...' : 'Descargar receta (PDF)' }}
        </button>
        <button class="btn diagnostico" :disabled="descargando" @click="descargar('diagnostico')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          {{ descargando ? 'Generando...' : 'Descargar diagnóstico (PDF)' }}
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
      recetaDescargada: false,
      diagnosticoDescargado: false,
      letras: ['P', 'S', 'O', 'A', 'P', 'P'],
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
    // Llama a este método antes de permitir que el usuario salga de la
    // vista (cambie de paciente, navegue a otra sección, etc.). Si falta
    // descargar la receta y/o el diagnóstico, muestra una alerta y solo
    // ejecuta callbackNavegacion() si el usuario confirma salir de todos
    // modos. Si ya descargó ambos, ejecuta el callback directo.
    validarSalida(callbackNavegacion) {
      const faltaReceta = !this.recetaDescargada;
      const faltaDiagnostico = !this.diagnosticoDescargado;

      if (!faltaReceta && !faltaDiagnostico) {
        if (typeof callbackNavegacion === 'function') {
          callbackNavegacion();
        }
        return;
      }

      let texto;
      if (faltaReceta && faltaDiagnostico) {
        texto = 'No has descargado la receta ni el diagnóstico de esta consulta. Si sales ahora, podrías perderlos.';
      } else if (faltaReceta) {
        texto = 'No has descargado la receta médica de esta consulta. Si sales ahora, podrías perderla.';
      } else {
        texto = 'No has descargado el diagnóstico de esta consulta. Si sales ahora, podrías perderlo.';
      }

      // FIX: antes era `Swal.fire(...)` sin prefijo, lo que lanzaba un
      // ReferenceError silencioso si SweetAlert2 solo está registrado
      // como window.Swal (como en el resto del proyecto) y por eso el
      // modal nunca resolvía su promesa ni ejecutaba la navegación.
      window.Swal.fire({
        title: '¿Estás seguro de salir?',
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir de todos modos',
        cancelButtonText: 'Cancelar, quedarme',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          // El usuario decidió salir bajo su propio riesgo
          if (typeof callbackNavegacion === 'function') {
            callbackNavegacion();
          }
        }
      });
    },
    // Consulta rápida (sin disparar el modal) de si falta descargar algo.
    // La usa el padre para decidir si necesita interceptar la navegación
    // o dejar pasar el clic sin más.
    tienePendientes() {
      return !this.recetaDescargada || !this.diagnosticoDescargado;
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

        if (tipo === 'receta') {
          this.recetaDescargada = true;
        } else if (tipo === 'diagnostico') {
          this.diagnosticoDescargado = true;
        }
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
  /* Paleta alineada a la identidad ya usada en receta/diagnóstico (Ultra Farmacia) */
  --bg: #f4f7f8;
  --card: #ffffff;
  --border: #e3e9ea;
  --teal: #0B7285;
  --teal-dark: #075463;
  --teal-tint: #eaf7f8;
  --amber: #d97706;
  --amber-bg: #fffbeb;
  --amber-border: #fde68a;
  --green: #146c43;
  --green-bg: #e8f6ee;
  --green-border: #bfe6cf;
  --text: #1f2937;
  --text-light: #6b7280;

  width: 100% !important;
  max-width: none !important;
  min-width: 0 !important;

  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  color: var(--text);
  position: relative;
  box-sizing: border-box;
}

/* ---------- Header ---------- */
.card-header {
  display: flex; align-items: flex-end; justify-content: space-between;
  padding: 18px 20px 16px; border-bottom: 1px solid var(--border);
  background: linear-gradient(180deg, var(--teal-tint) 0%, #ffffff 100%);
}
.header-left { display: flex; flex-direction: column; }
.eyebrow { display: flex; gap: 3px; margin-bottom: 8px; }
.eyebrow-letter {
  width: 18px; height: 18px; border-radius: 4px;
  background: var(--teal); color: #fff;
  font-size: 10px; font-weight: 800; letter-spacing: .2px;
  display: flex; align-items: center; justify-content: center;
}
.eyebrow-letter:nth-child(2) { background: var(--teal-dark); }
.eyebrow-letter:nth-child(3) { background: var(--teal); }
.eyebrow-letter:nth-child(4) { background: var(--teal-dark); }
.eyebrow-letter:nth-child(5) { background: var(--teal); }
.eyebrow-letter:nth-child(6) { background: var(--teal-dark); }
.card-header h2 { margin: 0; font-size: 17px; font-weight: 800; color: #111827; letter-spacing: -.2px; }
.card-header p { margin: 3px 0 0; font-size: 12px; color: var(--text-light); }

.mode-toggle {
  display: flex; align-items: center; gap: 2px; background: #ffffff;
  border: 1px solid var(--border); border-radius: 999px; padding: 3px;
  box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
}
.mode-btn {
  display: flex; align-items: center; gap: 6px;
  border: none; background: transparent; padding: 7px 14px; border-radius: 999px;
  cursor: pointer; color: var(--text-light); font-size: 12.5px; font-weight: 700;
  transition: background .15s, color .15s;
}
.mode-btn:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
.mode-btn.ia.active { background: var(--teal); color: #fff; }
.mode-btn.manual.active { background: var(--text); color: #fff; }

/* ---------- Barra de escucha ---------- */
.listening-bar {
  display: flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--teal-tint); border-bottom: 1px solid var(--border);
  font-size: 12.5px; color: var(--teal-dark); font-weight: 700;
}
.dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: pulse 1.2s infinite; flex-shrink: 0; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
@media (prefers-reduced-motion: reduce) {
  .dot { animation: none; }
}

/* ---------- Progreso ---------- */
.progress-row { display: flex; gap: 6px; padding: 16px 20px 0; }
.progress-seg { height: 5px; flex: 1; border-radius: 4px; background: #e5e9f0; transition: background .2s; }
.progress-seg.done { background: var(--teal); }

/* ---------- Acordeón con línea de secuencia ---------- */
.accordion { padding: 14px 14px 18px; }
.item { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; overflow: hidden; background: #fff; transition: border-color .15s; }
.item.open { border-color: #cfe4e8; }
.item-head { display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; cursor: pointer; background: #fafbfd; }
.item.open .item-head { background: #fff; }

.rail { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.letter {
  width: 30px; height: 30px; border-radius: 8px; background: var(--teal); color: #fff;
  display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px;
  flex-shrink: 0; transition: background .2s;
}
.item.done .letter { background: var(--green); }
.rail-line { width: 2px; flex: 1; min-height: 14px; background: var(--border); margin-top: 4px; }
.item.done .rail-line { background: var(--green-border); }

.item-title { flex: 1; padding-top: 3px; }
.item-title b { font-size: 13.5px; display: block; color: #111827; }
.item-title span { font-size: 11.5px; color: var(--text-light); }

.status-pill { font-size: 10.5px; font-weight: 700; padding: 4px 9px; border-radius: 999px; white-space: nowrap; margin-top: 3px; }
.status-pill.pending { background: #f1f2f4; color: var(--text-light); }
.status-pill.ia { background: var(--amber-bg); color: #92700f; border: 1px solid var(--amber-border); }
.status-pill.done { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-border); }

.chevron { color: var(--text-light); margin-left: 2px; margin-top: 6px; transition: transform .2s; flex-shrink: 0; }
.item.open .chevron { transform: rotate(180deg); color: var(--teal); }

.item-body { padding: 0 14px 14px 56px; }
textarea {
  width: 100%; min-height: 74px; resize: vertical; border: 1px solid var(--border); border-radius: 8px;
  padding: 10px; font-size: 13px; font-family: inherit; color: var(--text); background: #fff;
  transition: border-color .15s, box-shadow .15s;
}
textarea:focus-visible { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(11, 114, 133, .12); }
.hint { font-size: 11px; color: var(--text-light); margin-top: 5px; }

/* ---------- Acciones ---------- */
.actions { display: flex; flex-direction: column; gap: 10px; padding: 16px 20px; border-top: 1px solid var(--border); background: #fafbfd; }
.actions-row { display: flex; gap: 10px; }
.btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px; border: none; border-radius: 8px; padding: 11px 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background .15s, border-color .15s; }
.btn.receta { background: var(--teal); color: #fff; }
.btn.receta:hover:not(:disabled) { background: var(--teal-dark); }
.btn.diagnostico { background: #fff; color: var(--teal-dark); border: 1px solid var(--teal); }
.btn.diagnostico:hover:not(:disabled) { background: var(--teal-tint); }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn:focus-visible { outline: 2px solid var(--teal-dark); outline-offset: 2px; }

.save-select {
  width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px;
  font-size: 13px; font-weight: 600; color: var(--text); background: #fff;
}
.save-select:disabled { opacity: .6; cursor: not-allowed; }
.save-select:focus-visible { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(11, 114, 133, .12); }

.toast {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
  background: #111827; color: #fff; padding: 8px 16px; border-radius: 8px;
  font-size: 12.5px; font-weight: 600; box-shadow: 0 4px 12px rgba(0, 0, 0, .18);
}
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>