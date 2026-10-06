<template>
  <div class="seccion-guia-i-evento-psicologia">
    <!-- SECTION: GUÍA I - EVENTO TRAUMÁTICO (NOM-035) -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #fd7e14 0%, #e86c00 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-exclamation-triangle mr-2"></i> GUÍA I: EVENTO TRAUMÁTICO (NOM-035-STPS-2018)
        </h5>
      </div>
      <div class="card-body">
        <!-- APLICAR GUÍA I -->
        <div class="mb-4">
          <div class="border rounded p-3 bg-light">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" v-model="form.guia_ref_i_aplicada" id="guiaI" @change="updateField('guia_ref_i_aplicada', $event.target.checked)" />
              <label class="form-check-label font-weight-bold text-dark" for="guiaI">
                <i class="fas fa-clipboard-check mr-2 text-warning"></i> Aplicar Guía de Referencia I (Eventos Traumáticos)
              </label>
            </div>
            <p class="text-sm text-muted mt-1 ml-4">Marcar para habilitar campos de evento traumático (requerido para tipo "Seguimiento Trauma")</p>
          </div>
        </div>

        <!-- CAMPOS EVENTO TRAUMÁTICO -->
        <div v-if="form.guia_ref_i_aplicada">
          <div class="row mb-4">
            <!-- HA PRESENCIADO EVENTO -->
            <div class="col-md-6 mb-3">
              <div class="border rounded p-3 bg-white">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="form.ha_presenciado_evento_traumatico" id="haPresenciado" @change="updateField('ha_presenciado_evento_traumatico', $event.target.checked)" />
                  <label class="form-check-label font-weight-bold text-dark" for="haPresenciado">
                    <i class="fas fa-eye mr-2 text-info"></i> Ha presenciado o experimentado evento traumático
                  </label>
                </div>
              </div>
            </div>

            <!-- REQUIERE CANALIZACIÓN IMSS -->
            <div class="col-md-6 mb-3">
              <div class="border rounded p-3 bg-white">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="form.requiere_canalizacion_imss" id="canalizacionIMSS" @change="updateField('requiere_canalizacion_imss', $event.target.checked)" />
                  <label class="form-check-label font-weight-bold text-danger" for="canalizacionIMSS">
                    <i class="fas fa-hospital mr-2"></i> Requiere canalización al IMSS
                  </label>
                </div>
                <p class="text-sm text-muted mt-1 ml-4">Según criterios NOM-035-STPS-2018</p>
              </div>
            </div>
          </div>

          <!-- DESCRIPCIÓN DEL EVENTO -->
          <div v-if="form.ha_presenciado_evento_traumatico" class="mb-4">
            <label class="font-weight-bold">Descripción del Evento Traumático <span class="text-danger">*</span></label>
            <textarea v-model="form.descripcion_evento_traumatico" rows="3" class="form-control" placeholder="Describa brevemente el evento traumático presenciado o experimentado..." style="height: 96px;"></textarea>
          </div>

          <!-- FECHA DEL EVENTO -->
          <div v-if="form.ha_presenciado_evento_traumatico" class="row mb-4">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold">Fecha del Evento <span class="text-danger">*</span></label>
              <input type="date" v-model="form.fecha_evento_traumatico" class="form-control" :max="fechaHoy" style="height: 38px;" required />
            </div>
          </div>

          <!-- CRITERIOS NOM-035 (ESTILO TABLAS DE REFERENCIA NUTRICIÓN) -->
          <div class="mt-4 pt-3 border-top">
            <div class="card-header pb-0 pt-0">
              <h5 class="mb-0">
                <i class="fas fa-book mr-2"></i> CRITERIOS NOM-035-STPS-2018 (GUÍA I)
              </h5>
            </div>
            <div class="card-body">
              <ul class="list-unstyled text-sm">
                <li><i class="fas fa-circle text-warning mr-1"></i> <strong>Evento traumático:</strong> Pone en riesgo la vida o integridad física propia o ajena</li>
                <li><i class="fas fa-circle text-warning mr-1"></i> <strong>Testigo de:</strong> Muerte, lesión grave, violencia sexual o amenazas de muerte</li>
                <li><i class="fas fa-circle text-warning mr-1"></i> <strong>Síntomas TEPT:</strong> Revive el evento (flashbacks, pesadillas), evita estímulos asociados, hiperactivación (irritabilidad, hipervigilancia)</li>
                <li><i class="fas fa-circle text-warning mr-1"></i> <strong>Duración:</strong> > 1 mes con deterioro funcional significativo (laboral, social, familiar)</li>
                <li><i class="fas fa-circle text-warning mr-1"></i> <strong>Excluye:</strong> Efectos fisiológicos de sustancias u otra condición médica</li>
              </ul>
            </div>
          </div>

          <!-- RESUMEN GUÍA I -->
          <div v-if="form.guia_ref_i_aplicada && (form.ha_presenciado_evento_traumatico || form.requiere_canalizacion_imss)" class="mt-4 p-3 bg-light border border-left border-warning">
            <h5 class="font-weight-bold text-warning mb-3 flex items-center gap-2">
              <i class="fas fa-file-alt mr-2"></i> RESUMEN GUÍA I
            </h5>
            <div class="row">
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Evento traumático:</span>
                  <span class="font-weight-bold">{{ form.ha_presenciado_evento_traumatico ? 'Sí' : 'No' }}</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Fecha evento:</span>
                  <span class="font-weight-bold">{{ form.fecha_evento_traumatico ? formatearFecha(form.fecha_evento_traumatico) : '—' }}</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Canalización IMSS:</span>
                  <span class="font-weight-bold text-danger">{{ form.requiere_canalizacion_imss ? 'REQUERIDA' : 'No requerida' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GuiaIEvento',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    formatearFecha(fecha) {
      if (!fecha) return 'sin fecha'
      return new Date(`${fecha}T00:00:00`).toLocaleDateString('es-MX')
    }
  }
}
</script>

<style scoped>
.seccion-guia-i-evento-psicologia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #fd7e14 0%, #e86c00 100%) !important;
  color: white !important;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #fd7e14;
  box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
}

.form-check-input {
  width: 1.1rem;
  height: 1.1rem;
  margin-top: 0.15rem;
}

.form-check-label {
  font-size: 0.9rem;
  cursor: pointer;
}

.text-muted { color: #6c757d !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-info { color: #17a2b8 !important; }
.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-warning { border-color: #ffc107 !important; }
.border-success { border-color: #28a745 !important; }

.rounded { border-radius: 0.25rem !important; }
.p-2 { padding: 0.5rem !important; }
.p-3 { padding: 1rem !important; }
.p-4 { padding: 1.5rem !important; }
.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }

.d-flex { display: flex !important; }
.flex-wrap { flex-wrap: wrap !important; }
.justify-content-between { justify-content: space-between !important; }
.align-items-center { align-items: center !important; }
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }

.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.875rem !important; }

.list-unstyled { padding-left: 0; list-style: none; }
</style>