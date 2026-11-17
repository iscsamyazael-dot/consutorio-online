<template>
    <div>
     <!-- Fila 5: Resumen y conclusión -->
        <div class="mt-4 p-4" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px;">
          <h4 class="font-weight-bold mb-3" style="color: #9a3412;">
            <i class="fas fa-chart-bar mr-2"></i>
            Evaluación de Riesgo Total
          </h4>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Nivel de Riesgo Total</label>
              <div class="d-flex align-items-center">
                <span class="badge-riesgo"
                  :class="{
                    'badge-bajo': nivelRiesgoTotal === 'bajo',
                    'badge-medio': nivelRiesgoTotal === 'medio',
                    'badge-alto': nivelRiesgoTotal === 'alto',
                    'badge-muy-alto': nivelRiesgoTotal === 'muy_alto'
                  }">
                  {{ nivelRiesgoTotal }}
                </span>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Acciones Requeridas</label>
              <div class="d-flex align-items-center">
                <span class="badge-accion"
                  :class="{
                    'badge-ninguna': accionRequerida === 'ninguna',
                    'badge-monitoreo': accionRequerida === 'monitoreo',
                    'badge-mejora': accionRequerida === 'mejora',
                    'badge-intervencion': accionRequerida === 'intervencion'
                  }">
                  {{ accionRequerida }}
                </span>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Prioridad de Intervención</label>
              <div class="d-flex align-items-center">
                <span class="badge-prioridad"
                  :class="{
                    'badge-baja': prioridadIntervencion === 'baja',
                    'badge-media': prioridadIntervencion === 'media',
                    'badge-alta-p': prioridadIntervencion === 'alta',
                    'badge-urgente': prioridadIntervencion === 'urgente'
                  }">
                  {{ prioridadIntervencion }}
                </span>
              </div>
            </div>
          </div>
        </div>
    </div>
</template>

<script>
export default {
  name: 'RiesgoTotalErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  computed: {
    nivelRiesgoTotal() {
        const niveles = []
        const rula = this.modelValue.rula_puntuacion
        const reba = this.modelValue.reba_puntuacion
        const niosh = this.modelValue.niosh_indice_levantamiento

        if (rula) {
            if (rula <= 2) niveles.push(0)
            else if (rula <= 4) niveles.push(1)
            else if (rula <= 6) niveles.push(2)
            else niveles.push(3)
        }
        if (reba) {
            if (reba <= 1) niveles.push(0)
            else if (reba <= 3) niveles.push(1)
            else if (reba <= 7) niveles.push(2)
            else niveles.push(3)
        }

        if (niosh) {
            if (niosh < 1) niveles.push(0)
            else if (niosh <= 3) niveles.push(1)
            else niveles.push(2)
        }

        if (niveles.length === 0) return 'sin valorar'

        let nivel = Math.max(...niveles)
       const factores = this.riesgosKeys.filter(k => (this.modelValue.hallazgos || {})[k]).length
        if (factores > 0) nivel = Math.min(nivel + 1, 3)

        return ['bajo', 'medio', 'alto', 'muy_alto'][nivel]
    },
    accionRequerida() {
      const riesgo = this.nivelRiesgoTotal
      switch (riesgo) {
        case 'bajo': return 'ninguna'
        case 'medio': return 'monitoreo'
        case 'alto': return 'mejora'
        case 'muy_alto': return 'intervencion'
        case 'sin valorar': return 'pendiente'
        default: return 'ninguna'
      }
    },
    prioridadIntervencion() {
      const riesgo = this.nivelRiesgoTotal
      switch (riesgo) {
        case 'bajo': return 'baja'
        case 'medio': return 'media'
        case 'alto': return 'alta'
        case 'muy_alto': return 'urgente'
        case 'sin valorar': return 'pendiente'
        default: return 'baja'
      }
    },
    riesgosKeys() {
        return ['posturas_forzadas', 'movimientos_repetitivos', 'manipulacion_cargas', 'cargas_maximas_manipuladas', 'exposicion_calor', 'exposicion_vibraciones', 'iluminacion_insuficiente']
    },
  }
}
</script>

<style scoped>
    .badge-riesgo, .badge-accion, .badge-prioridad {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-bajo { background: #dcfce7; color: #166534; }
    .badge-medio { background: #fef3c7; color: #92400e; }
    .badge-alto { background: #ffedd5; color: #9a3412; }
    .badge-muy-alto { background: #fee2e2; color: #991b1b; }

    .badge-ninguna { background: #dcfce7; color: #166534; }
    .badge-monitoreo { background: #fef3c7; color: #92400e; }
    .badge-mejora { background: #ffedd5; color: #9a3412; }
    .badge-intervencion { background: #fee2e2; color: #991b1b; }

    .badge-baja { background: #dcfce7; color: #166534; }
    .badge-media { background: #fef3c7; color: #92400e; }
    .badge-alta-p { background: #ffedd5; color: #9a3412; }
    .badge-urgente { background: #fee2e2; color: #991b1b; }
</style>