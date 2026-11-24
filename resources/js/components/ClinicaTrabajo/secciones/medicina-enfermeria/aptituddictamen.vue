<template>
  <div class="aptitud-dictamen">

    <!-- ===================== 4.1 DIAGNÓSTICO ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-file-medical mr-2"></i>4.1 Diagnóstico clínico</span>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-8">
            <label>Diagnóstico principal <span class="text-danger">*</span></label>
            <span v-if="esSugerido('diagnostico')" class="badge badge-warning ml-2">Sugerido por IA</span>
            <textarea
              v-model.trim="local.diagnostico"
              class="form-control"
              rows="2"
              placeholder="Ej: Sin hallazgos relevantes, hipertensión controlada…"
            ></textarea>
          </div>
          <div class="form-group col-md-4 position-relative">
            <label>Código CIE-11 (opcional)</label>
            <span v-if="esSugerido('cie11_termino')" class="badge badge-warning ml-2">Sugerido por IA</span>
            <div class="position-relative">
              <input
                v-model="local.cie11_termino"
                type="text"
                class="form-control pr-4"
                placeholder="Escribe o busca en CIE-11..."
                @keyup.enter="buscarIcd11Inmediato(local.cie11_termino)"
              >
              <i class="fas fa-search cie11-lupa" title="Buscar de nuevo" @click="buscarIcd11Inmediato(local.cie11_termino)"></i>
            </div>
            <ul v-if="resultadosIcd.length" class="cie11-dropdown">
              <li v-for="r in resultadosIcd" :key="r.codigo" @click="seleccionarIcd(r)">
                <strong>{{ r.codigo }}</strong> — {{ r.titulo }}
              </li>
            </ul>
            <small v-if="buscandoIcd" class="text-muted">Buscando en CIE-11...</small>
            <small v-if="local.cie11_codigo" class="badge badge-light text-dark mt-1 d-inline-block text-wrap">
              {{ local.cie11_codigo }} — {{ local.cie11_titulo }}
            </small>
          </div>
        </div>
        <div class="form-group mb-0">
          <label>Hallazgos relevantes para la aptitud</label>
          <span v-if="esSugerido('hallazgos_relevantes_aptitud	')" class="badge badge-warning ml-2">Sugerido por IA</span>
          <textarea
            v-model="local.hallazgos_relevantes_aptitud"
            class="form-control"
            rows="3"
            placeholder="Hallazgos clínicos que impactan la aptitud laboral…"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- ===================== 4.2 APTITUD LABORAL ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-user-check mr-2"></i>4.2 Aptitud laboral</span>
        <span v-if="esSugerido('aptitud')" class="badge badge-warning ml-2">Sugerido por IA</span>
        <span class="contador">Obligatorio</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Seleccione una sola opción. Es obligatoria para guardar la ficha.</p>

        <div class="aptitud-grid" role="radiogroup" aria-label="Aptitud laboral">
          <label
            v-for="op in opcionesAptitud"
            :key="op.value"
            class="aptitud-opcion"
            :class="[op.clase, { activa: local.aptitud === op.value }]"
          >
            <input v-model="local.aptitud" type="radio" name="aptitud" :value="op.value">
            <i :class="op.icono" class="aptitud-icono" aria-hidden="true"></i>
            <span class="aptitud-texto">
              <strong>{{ op.titulo }}</strong>
              <small>{{ op.descripcion }}</small>
            </span>
          </label>
        </div>

        <p v-if="!local.aptitud" class="aptitud-pendiente">
          <i class="fas fa-info-circle mr-1"></i>Aún no se ha seleccionado la aptitud.
        </p>

        <!-- Restricciones: solo si es apto con restricciones -->
        <div v-if="local.aptitud === 'apto_con_restricciones'" class="form-group mt-3 mb-0">
          <label>Restricciones y limitaciones </label>
          <span v-if="esSugerido('restricciones')" class="badge badge-warning ml-2">Sugerido por IA</span>
          <textarea
            v-model="local.restricciones"
            class="form-control"
            rows="3"
            placeholder="Limitaciones, prohibiciones y condiciones para desempeñar el puesto…"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- ===================== 4.3 RECOMENDACIONES Y SEGUIMIENTO ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-calendar-check mr-2"></i>4.3 Recomendaciones y seguimiento</span>
        <span v-if="esSugerido('CAMPO')" class="badge badge-warning ml-2">Sugerido por IA</span>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Recomendaciones médicas</label>
            <span v-if="esSugerido('recomendaciones')" class="badge badge-warning ml-2">Sugerido por IA</span>
            <textarea v-model="local.recomendaciones" class="form-control" rows="3" placeholder="Para el trabajador y/o la empresa…"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Derivaciones a especialistas</label>
            <span v-if="esSugerido('derivaciones_especialista')" class="badge badge-warning ml-2">Sugerido por IA</span>
            <textarea v-model="local.derivaciones_especialista" class="form-control" rows="3" placeholder="Especialidad y motivo…"></textarea>
          </div>
        </div>
        <div class="form-group">
          <label>Plan de intervención</label>
          <span v-if="esSugerido('plan_intervencion')" class="badge badge-warning ml-2">Sugerido por IA</span>
          <textarea v-model="local.plan_intervencion" class="form-control" rows="3" placeholder="Acciones concretas, responsables y plazos…"></textarea>
        </div>
        <h6 class="grupo-titulo">Próximo seguimiento</h6>
        <span v-if="esSugerido('proximo_seguimiento')" class="badge badge-warning ml-2">Sugerido por IA</span>
        <div class="opciones-grid compacto">
          <label
            v-for="op in opcionesSeguimiento"
            :key="op.value"
            class="opcion"
            :class="{ activa: local.proximo_seguimiento === op.value }"
          >
            <input v-model="local.proximo_seguimiento" type="radio" name="proximo_seguimiento" :value="op.value">
            <span>{{ op.label }}</span>
          </label>
        </div>
      </div>
    </div>

    <!-- ===================== 4.4 DICTAMEN FINAL ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-file-signature mr-2"></i>4.4 Dictamen final</span>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Conclusión médica </label>
          <span v-if="esSugerido('dictamen_final')" class="badge badge-warning ml-2">Sugerido por IA</span>
          <textarea
            v-model="local.dictamen_final"
            class="form-control"
            rows="4"
            placeholder="Conclusión formal: diagnóstico, aptitud y recomendaciones…"
          ></textarea>
          <small class="text-muted">Este texto forma parte del registro médico oficial.</small>
        </div>
        <div class="form-group mb-0">
          <label>Observaciones adicionales</label>
          <span v-if="esSugerido('observaciones')" class="badge badge-warning ml-2">Sugerido por IA</span>
          <textarea v-model="local.observaciones" class="form-control" rows="2"></textarea>
        </div>
      </div>
    </div>

    <div v-if="sugeridos.length" class="card ficha-card">
      <div class="card-body">
        <div class="form-check">
          <input id="confirmo-dictamen" v-model="local.dictamen_confirmado" type="checkbox" class="form-check-input">
          <label for="confirmo-dictamen" class="form-check-label">Confirmo que revisé el dictamen sugerido por la IA y es correcto</label>
        </div>
      </div>
    </div>

    <!-- RESUMEN -->
    <div v-if="aptitudActual" class="resumen" :class="aptitudActual.clase">
      <i :class="aptitudActual.icono" aria-hidden="true"></i>
      <div>
        <strong>Aptitud registrada: {{ aptitudActual.titulo }}</strong>
        <span class="d-block">{{ aptitudActual.resumen }}</span>
      </div>
    </div>

  </div>
</template>

<script>
import modeloSincronizado from '../../mixins/modeloSincronizado'
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

const OPCIONES_APTITUD = [
  {
    value: 'apto',
    titulo: 'Apto',
    descripcion: 'Para el desempeño del puesto actual',
    resumen: 'El trabajador está apto para desempeñar el puesto.',
    icono: 'fas fa-check-circle',
    clase: 'estado-apto',
  },
  {
    value: 'apto_con_restricciones',
    titulo: 'Apto con restricciones',
    descripcion: 'Requiere limitaciones específicas',
    resumen: 'El trabajador está apto con restricciones. Revisar limitaciones.',
    icono: 'fas fa-exclamation-circle',
    clase: 'estado-restricciones',
  },
  {
    value: 'no_apto',
    titulo: 'No apto',
    descripcion: 'No puede desempeñar el puesto actual',
    resumen: 'El trabajador no está apto para desempeñar el puesto.',
    icono: 'fas fa-times-circle',
    clase: 'estado-no-apto',
  },
]

const OPCIONES_SEGUIMIENTO = [
  { value: '3m', label: '3 meses' },
  { value: '6m', label: '6 meses' },
  { value: '12m', label: '12 meses (anual)' },
  { value: 'extraordinaria', label: 'Extraordinaria (urgente)' },
]

const crearVacio = () => ({
  diagnostico: '',
  cie11_termino: '',
  cie11_codigo: '',
  cie11_titulo: '',
  hallazgos_relevantes_aptitud: '',
  aptitud: '',
  restricciones: '',
  recomendaciones: '',
  plan_intervencion: '',
  proximo_seguimiento: '',
  derivaciones_especialista: '',
  dictamen_final: '',
  observaciones: '',
  dictamen_confirmado: false,
})

export default {
  name: 'AptitudDictamen',
  props: {
    sugeridos: { type: Array, default: () => [] },
  },
  mixins: [modeloSincronizado(crearVacio)],
  data() {
    return {
      opcionesAptitud: OPCIONES_APTITUD,
      opcionesSeguimiento: OPCIONES_SEGUIMIENTO,
      resultadosIcd: [],
      buscandoIcd: false,
      debounceCie11: null,
      ultimaBusqueda: '',
    }
  },

  computed: {
    aptitudActual() {
      return OPCIONES_APTITUD.find(o => o.value === this.local.aptitud) || null
    },
  },
  
  watch: {
    // Se dispara cuando la IA llena cie11_termino o cuando el médico escribe en el buscador
    'local.cie11_termino'(texto) {
      clearTimeout(this.debounceCie11)
      const t = (texto || '').trim()
      if (t.length < 3) {
        this.resultadosIcd = []
        return
      }
      if (t === this.ultimaBusqueda) return
      this.debounceCie11 = setTimeout(() => this.buscarIcd11Inmediato(t), 400)
    },
  },

  methods: {
    async buscarIcd11Inmediato(texto) {
      const t = (texto || '').trim()
      if (t.length < 3) return
      this.ultimaBusqueda = t
      this.buscandoIcd = true
      try {
        const { data } = await clinicaTrabajo.get('/icd11/buscar', { params: { texto: t } })
        // Descarta respuestas de búsquedas anteriores que llegan tarde
        if (t !== (this.local.cie11_termino || '').trim()) return
        this.resultadosIcd = data.resultados || []
      } catch (error) {
        console.error('Error al buscar en CIE-11:', error)
        this.resultadosIcd = []
      } finally {
        this.buscandoIcd = false
      }
    },

    seleccionarIcd(resultado) {
      this.local.cie11_codigo = resultado.codigo
      this.local.cie11_titulo = resultado.titulo
      this.resultadosIcd = []
    },
    
    esSugerido(campo) {
      return this.sugeridos.includes(campo)
    },

  },

}
</script>

<style scoped src="../../estilos/ficha-ocupacional.css"></style>

<style scoped>
/* ---------- Opciones de aptitud ---------- */
.aptitud-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: .75rem;
}

.aptitud-opcion {
  --estado: #6B7280;
  --estado-fondo: #F9FAFB;
  display: flex;
  align-items: center;
  gap: .75rem;
  margin: 0;
  padding: .9rem 1rem;
  border: 1.5px solid #D1D5DB;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
  user-select: none;
}
.aptitud-opcion.estado-apto { --estado: #15803D; --estado-fondo: #F0FDF4; }
.aptitud-opcion.estado-restricciones { --estado: #B45309; --estado-fondo: #FFFBEB; }
.aptitud-opcion.estado-no-apto { --estado: #B91C1C; --estado-fondo: #FEF2F2; }

.aptitud-opcion:hover {
  border-color: var(--estado);
}
.aptitud-opcion.activa {
  border-color: var(--estado);
  background: var(--estado-fondo);
  box-shadow: inset 4px 0 0 var(--estado);
}
.aptitud-opcion:focus-within {
  outline: 2px solid rgba(0, 123, 255, .4);
  outline-offset: 1px;
}

.aptitud-opcion input {
  width: 16px;
  height: 16px;
  margin: 0;
  flex-shrink: 0;
  accent-color: var(--estado);
  cursor: pointer;
}

.aptitud-icono {
  font-size: 1.4rem;
  color: #D1D5DB;
  flex-shrink: 0;
}
.aptitud-opcion.activa .aptitud-icono {
  color: var(--estado);
}

.aptitud-texto strong {
  display: block;
  font-size: .9rem;
  color: #1F2937;
}
.aptitud-opcion.activa .aptitud-texto strong {
  color: var(--estado);
}
.aptitud-texto small {
  color: #6B7280;
  font-size: .78rem;
}

.aptitud-pendiente {
  margin: .75rem 0 0;
  font-size: .8rem;
  color: #6B7280;
}

.campo-pendiente {
  border-color: #F59E0B;
  background: #FFFBEB;
}

/* ---------- Resumen ---------- */
.resumen {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  padding: .9rem 1rem;
  border-radius: 6px;
  border: 1px solid;
  font-size: .875rem;
}
.resumen i {
  font-size: 1.3rem;
  margin-top: 2px;
}
.resumen.estado-apto { background: #F0FDF4; border-color: #86EFAC; color: #15803D; }
.resumen.estado-restricciones { background: #FFFBEB; border-color: #FCD34D; color: #B45309; }
.resumen.estado-no-apto { background: #FEF2F2; border-color: #FCA5A5; color: #B91C1C; }

/* Los textarea crecen con su contenido */
textarea.form-control {
  height: auto;
  min-height: 70px;
  field-sizing: content;
}

.cie11-lupa {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  cursor: pointer;
}
.cie11-dropdown {
  position: absolute;
  z-index: 10;
  background: #fff;
  border: 1px solid #ced4da;
  border-radius: 6px;
  width: 100%;
  max-height: 220px;
  overflow-y: auto;
  list-style: none;
  padding: 4px 0;
  margin: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
.cie11-dropdown li {
  padding: 6px 10px;
  font-size: 13px;
  cursor: pointer;
}
.cie11-dropdown li:hover {
  background: #f1f3f5;
}

</style>