<template>
  <div class="container-fluid content-wrapper-custom pt-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
      <div>
        <h1 class="fw-black text-dark m-0 d-flex align-items-center gap-2 tracking-tight">
          <span class="p-2 bg-primary-soft rounded-3 d-inline-flex align-items-center justify-content-center border border-primary-subtle">
            <i class="fas fa-heartbeat text-primary animate__animated animate__pulse animate__infinite"></i>
          </span>
          Centro de Consultas
        </h1>
        <p class="text-muted m-0 mt-2 fs-6">
          Plataforma médica inteligente de monitoreo clínico y flujos en tiempo real.
        </p>
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <div class="bg-white px-3 py-2 rounded-pill shadow-xs d-flex align-items-center gap-2 border border-light-subtle">
          <span class="live-dot"></span>
          <small class="fw-bold text-secondary text-uppercase tracking-wider" style="font-size: 0.75rem;">Sistema Activo</small>
        </div>
        <a href="/consultas/create" class="btn btn-primary rounded-3 px-4 py-2 shadow-sm fw-bold d-flex align-items-center gap-2 btn-hover-transform">
          <i class="fas fa-plus-circle"></i> Nueva Consulta
        </a>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-xl-3 col-sm-6 mb-3" v-for="stat in stats" :key="stat.title">
        <div :class="['modern-stat-card', stat.borderClass, 'shadow-sm']">
          <div class="card-body-custom">
            <div>
              <span class="text-muted fw-semibold text-uppercase tracking-wider small">{{ stat.title }}</span>
              <h2 class="fw-black text-dark mt-1 mb-0">{{ stat.value }}</h2>
            </div>
            <div :class="['stat-icon-box', stat.iconBgClass, stat.iconTextClass]">
              <i :class="stat.icon"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      
      <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
          <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
              <span class="badge-dot bg-primary"></span> Panel de Atención
            </h5>
          </div>
          <div class="card-body px-4 py-3">
            
            <div class="patient-hero-card p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="avatar-premium bg-primary text-white shadow-sm flex-shrink-0">
                  <i class="fas fa-user-md"></i>
                </div>
                <div class="ms-3 text-start">
                  <h5 class="fw-bold mb-0 text-dark">{{ pacienteActivo.nombre }}</h5>
                  <span class="text-muted small">Folio: <code class="text-primary fw-bold">{{ pacienteActivo.folio }}</code></span>
                </div>
              </div>
              <span :class="['badge badge-premium flex-shrink-0', pacienteActivo.estado === 'En consulta' ? 'bg-success-soft text-success border border-success-subtle' : 'bg-primary-soft text-primary border border-primary-subtle']">
                {{ pacienteActivo.estado }}
              </span>
            </div>

            <div class="mb-4">
              <table class="table table-borderless align-middle m-0 panel-details-table">
                <tbody>
                  <tr>
                    <td class="ps-0 py-2 text-muted fw-medium" style="width: 45%;">
                      <i class="fas fa-fingerprint text-secondary opacity-75 me-2"></i>Diagnóstico Prev.
                    </td>
                    <td class="pe-0 py-2 text-end text-dark fw-semibold small">
                      {{ pacienteActivo.diagnostico }}
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-0 py-2 text-muted fw-medium">
                      <i class="fas fa-calendar-check text-secondary opacity-75 me-2"></i>Fecha
                    </td>
                    <td class="pe-0 py-2 text-end text-dark fw-semibold">
                      {{ pacienteActivo.fecha }}
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-0 py-2 text-muted fw-medium">
                      <i class="fas fa-shield-virus text-secondary opacity-75 me-2"></i>Triage
                    </td>
                    <td class="pe-0 py-2 text-end">
                      <span :class="['badge badge-premium text-white fw-bold', pacienteActivo.triage === 'Grave' ? 'bg-danger' : 'bg-secondary']">
                        {{ pacienteActivo.triage }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="symptoms-alert p-3 rounded-3">
              <h6 class="fw-bold text-danger mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-notes-medical"></i> Sintomatología Reportada
              </h6>
              <p class="text-dark m-0 small lh-base">
                {{ pacienteActivo.sintomas }}
              </p>
            </div>
          </div>
          
          <div class="card-footer bg-light border-top-0 d-flex gap-2 justify-content-stretch px-4 py-3">
            <button @click="abrirDetallesModal(pacienteActivo)" class="btn btn-light border flex-grow-1 btn-hover-transform" title="Ver Detalles">
              <i class="fas fa-eye text-info"></i> Detalle
            </button>
            <a :href="'/consultas/' + pacienteActivo.id + '/edit'" class="btn btn-light border flex-grow-1 btn-hover-transform" title="Editar Registro">
              <i class="fas fa-pen text-warning"></i> Editar
            </a>
            <button @click="abrirExpedienteDirecto" class="btn btn-primary flex-grow-2 btn-hover-transform fw-bold" title="Historial Médico">
              <i class="fas fa-file-medical me-1"></i> Expediente
            </button>
          </div>
        </div>
      </div>

      <div class="col-xl-8 mb-4">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
          <div class="card-header bg-white d-flex justify-content-between align-items-center py-4 px-4 border-bottom-0">
            <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
              <span class="badge-dot bg-secondary"></span> Lista de Espera del Día
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold">
              {{ listaPacientes.length }} Pacientes
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table premium-table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-muted fw-semibold text-uppercase tracking-wider small">Paciente / Diagnóstico Preliminar</th>
                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Folio</th>
                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Estado</th>
                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Urgencia</th>
                    <th class="text-end px-4 py-3 text-muted fw-semibold text-uppercase tracking-wider small">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="paciente in listaPacientes" :key="paciente.id" class="premium-row">
                    <td class="px-4 py-3">
                      <div class="d-flex align-items-center">
                        <div :class="['mini-avatar-premium text-white flex-shrink-0', paciente.triage === 'Grave' ? 'bg-primary' : 'bg-success']">
                          <i class="fas fa-user-md"></i>
                        </div>
                        <div class="ms-3 text-start">
                          <div class="fw-bold text-dark mb-0">{{ paciente.nombre }}</div>
                          <div class="text-muted small lh-sm">{{ paciente.diagnostico }}</div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ paciente.folio }}</span></td>
                    <td>
                      <span :class="['badge badge-premium border', paciente.estado === 'En consulta' ? 'bg-success-soft text-success border-success-subtle' : 'bg-primary-soft text-primary border-primary-subtle']">
                        {{ paciente.estado }}
                      </span>
                    </td>
                    <td>
                      <span :class="['badge badge-premium text-white', paciente.triage === 'Grave' ? 'bg-danger' : 'bg-secondary']">
                        {{ paciente.triage }}
                      </span>
                    </td>
                    <td class="text-end px-4 py-3">
                      <div class="d-flex justify-content-end gap-1">
                        <button @click="seleccionarPaciente(paciente)" class="btn-icon-premium text-primary" title="Atender ahora"><i class="fas fa-play"></i></button>
                        <button @click="abrirDetallesModal(paciente)" class="btn-icon-premium text-info" title="Ver Ficha"><i class="fas fa-eye"></i></button>
                        <button @click="eliminarPaciente(paciente.id)" class="btn-icon-premium text-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="modal fade" id="verPacienteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header border-0 bg-dark py-4 px-4 d-flex align-items-center justify-content-between">
            <h5 class="modal-title fw-bold text-white m-0 d-flex align-items-center gap-2">
              <i class="fas fa-shield-alt text-primary"></i> Ficha Clínica Digital
            </h5>
            <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4 bg-white">
            <div class="text-center mb-4">
              <div class="avatar-premium bg-primary text-white shadow mx-auto mb-2" style="width: 65px; height: 65px;">
                <i class="fas fa-user-md" style="font-size: 1.5rem !important;"></i>
              </div>
              <h4 class="fw-black text-dark mb-1">{{ modalData.nombre }}</h4>
              <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1.5">ID REGISTRO: {{ modalData.folio }}</span>
            </div>
            
            <div class="row g-3 bg-light p-3 rounded-3 border border-light-subtle">
              <div class="col-6">
                <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem">Estado Actual</small>
                <span class="badge bg-success-soft text-success border border-success-subtle mt-1 d-inline-block">{{ modalData.estado }}</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem">Prioridad / Triage</small>
                <span :class="['badge text-white fw-bold mt-1 d-inline-block', modalData.triage === 'Grave' ? 'bg-danger' : 'bg-secondary']">{{ modalData.triage }}</span>
              </div>
              <div class="col-12"><hr class="my-1 opacity-25"></div>
              <div class="col-12">
                <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem"><i class="fas fa-clock me-1"></i> Fecha de Atención</small>
                <span class="text-dark fw-bold d-block mt-1">{{ modalData.fecha }}</span>
              </div>
              <div class="col-12">
                <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem"><i class="fas fa-comment-medical me-1"></i> Síntomas y Notas de Admisión</small>
                <p class="text-secondary m-0 small mt-1 lh-base">{{ modalData.sintomas }}</p>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3 px-4">
            <button type="button" class="btn btn-light border px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" @click="conmutarAExpediente" class="btn btn-primary px-4 rounded-3 fw-bold d-flex align-items-center gap-2 btn-hover-transform">
              <i class="fas fa-folder-open"></i> Abrir Expediente Completo
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="expedientePacienteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <form @submit.prevent="guardarExpediente" enctype="multipart/form-data">
            <div class="modal-header border-0 bg-primary py-4 px-4 d-flex align-items-center justify-content-between">
              <h5 class="modal-title fw-bold text-white m-0 d-flex align-items-center gap-2">
                <i class="fas fa-file-medical"></i> Apertura de Expediente Clínico
              </h5>
              <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
              <div class="d-flex align-items-center mb-4 p-3 rounded-3 bg-light border">
                <div class="mini-avatar-premium bg-primary text-white flex-shrink-0">
                  <i class="fas fa-id-card-alt"></i>
                </div>
                <div class="ms-3">
                  <h5 class="fw-black text-dark mb-0">{{ modalData.nombre }}</h5>
                  <span class="text-muted small">Folio Asociado: <code class="text-primary fw-bold">{{ modalData.folio }}</code></span>
                </div>
              </div>

              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="fas fa-heartbeat text-danger"></i> 1. Signos Vitales y Somatometría
              </h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label text-secondary fw-semibold small">Peso (kg)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-weight"></i></span>
                    <input type="number" step="0.1" v-model="formExpediente.peso" class="form-control border-start-0" placeholder="0.0" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label text-secondary fw-semibold small">Talla / Estatura (cm)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-ruler-vertical"></i></span>
                    <input type="number" v-model="formExpediente.talla" class="form-control border-start-0" placeholder="0" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label text-secondary fw-semibold small">Presión Arterial (mm Hg)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-heart"></i></span>
                    <input type="text" v-model="formExpediente.presion" class="form-control border-start-0" placeholder="120/80" required>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label text-secondary fw-semibold small">Diagnóstico / Observaciones Clínicas Iniciales</label>
                  <textarea v-model="formExpediente.observaciones" class="form-control" rows="3" placeholder="Escriba las conclusions o anotaciones físicas del paciente..." required></textarea>
                </div>
              </div>

              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="fas fa-x-ray text-primary"></i> 2. Carga de Radiografías / Estudios Visuales
              </h6>
              <div class="upload-drag-zone p-4 rounded-3 text-center border-dashed position-relative mb-2">
                <input type="file" @change="manejarArchivos" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" multiple accept="image/*">
                <div class="py-2">
                  <i class="fas fa-cloud-upload-alt text-primary display-4 mb-2 opacity-75"></i>
                  <p class="fw-bold text-dark mb-1 small">Arrastra aquí tus archivos o haz clic para buscar</p>
                  <p class="text-muted m-0 extra-small">Formatos permitidos: JPG, PNG. Puedes subir varias imágenes a la vez.</p>
                </div>
              </div>
              
              <div v-if="previews.length" class="d-flex flex-wrap gap-2 mt-2">
                <div v-for="(src, idx) in previews" :key="idx" class="position-relative">
                  <img :src="src" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" />
                </div>
              </div>

            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-end p-3 px-4 gap-2">
              <button type="button" class="btn btn-light border px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success px-4 rounded-3 fw-bold d-flex align-items-center gap-2 btn-hover-transform">
                <i class="fas fa-save"></i> Guardar e Inicializar Expediente
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Instancias de modales de Bootstrap
let bModalDetalles = null;
let bModalExpediente = null;

// Contadores superiores reactivos
const stats = ref([
  { title: 'Consultas Hoy', value: 24, icon: 'fas fa-stethoscope', borderClass: 'border-start-blue', iconBgClass: 'bg-blue-soft', iconTextClass: 'text-blue' },
  { title: 'Pacientes Activos', value: 12, icon: 'fas fa-user-injured', borderClass: 'border-start-green', iconBgClass: 'bg-green-soft', iconTextClass: 'text-green' },
  { title: 'Pendientes', value: 2, icon: 'fas fa-clock', borderClass: 'border-start-orange', iconBgClass: 'bg-orange-soft', iconTextClass: 'text-orange' },
  { title: 'Urgencias', value: 3, icon: 'fas fa-ambulance', borderClass: 'border-start-red', iconBgClass: 'bg-red-soft', iconTextClass: 'text-red' }
]);

// Listado de Pacientes (Equivalente a tu BD)
const listaPacientes = ref([
  { id: 1, nombre: 'Juan Pérez', folio: 'FOL-001', estado: 'En consulta', triage: 'Grave', fecha: '22 Mayo 2026', sintomas: 'Dolor abdominal agudo focalizado en fosa ilíaca derecha, náuseas, mareos esporádicos y fiebre ligera cuantificada en 38.2°C.' },
  { id: 2, nombre: 'María López', folio: 'FOL-002', estado: 'Esperando', triage: 'Normal', fecha: '25 Mayo 2026', sintomas: 'Tos seca persistente desde hace 4 días, disnea leve al caminar rápido, temperatura corporal controlada en 37.8°C. Sin alergias declaradas.' }
]);

// Paciente cargado en el panel lateral izquierdo (Inicia con Juan Pérez)
const pacienteActivo = ref({ ...listaPacientes.value[0] });

// Estado para los Modales
const modalData = ref({ nombre: '', folio: '', estado: '', triage: '', fecha: '', sintomas: '' });
const formExpediente = ref({ peso: '', talla: '', presion: '', observaciones: '', archivos: [] });
const previews = ref([]);

onMounted(() => {
  // Inicializamos los modales usando Vanilla JS nativo de Bootstrap 5
  bModalDetalles = new bootstrap.Modal(document.getElementById('verPacienteModal'));
  bModalExpediente = new bootstrap.Modal(document.getElementById('expedientePacienteModal'));
});

// Lógica de acciones
const seleccionarPaciente = (paciente) => {
  pacienteActivo.value = { ...paciente };
};

const abrirDetallesModal = (paciente) => {
  modalData.value = { ...paciente };
  bModalDetalles.show();
};

const abrirExpedienteDirecto = () => {
  modalData.value = { ...pacienteActivo.value };
  previews.value = [];
  bModalExpediente.show();
};

const conmutarAExpediente = () => {
  bModalDetalles.hide();
  previews.value = [];
  bModalExpediente.show();
};

const manejarArchivos = (event) => {
  const files = event.target.files;
  formExpediente.value.archivos = files;
  previews.value = [];
  
  for (let i = 0; i < files.length; i++) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previews.value.push(e.target.result);
    };
    reader.readAsDataURL(files[i]);
  }
};

const guardarExpediente = () => {
  console.log('Enviando expediente mediante Axios:', formExpediente.value);
  // Aquí irá tu llamada axios.post('/api/expedientes', ...)
  bModalExpediente.hide();
  alert('¡Expediente guardado con éxito para ' + modalData.value.nombre + '!');
};

const eliminarPaciente = (id) => {
  if (confirm('¿Seguro que deseas eliminar este paciente de la lista de espera?')) {
    listaPacientes.value = listaPacientes.value.filter(p => p.id !== id);
  }
};
</script>

<style scoped>
    /* Traemos exactamente tus estilos premium encapsulados en el componente */
    .fw-black { font-weight: 800 !important; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.05em; }
    .flex-grow-2 { flex-grow: 2 !important; }
    .cursor-pointer { cursor: pointer; }
    .extra-small { font-size: 0.75rem; }

    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-blue-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .text-blue { color: #2563eb !important; }
    .bg-green-soft { background-color: rgba(34, 197, 94, 0.1) !important; }
    .text-green { color: #16a34a !important; }
    .bg-orange-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    .text-orange { color: #d97706 !important; }
    .bg-red-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .text-red { color: #dc2626 !important; }

    .modern-stat-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modern-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.04) !important;
    }
    .card-body-custom {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .border-start-blue { border-left: 4px solid #3b82f6; }
    .border-start-green { border-left: 4px solid #22c55e; }
    .border-start-orange { border-left: 4px solid #f59e0b; }
    .border-start-red { border-left: 4px solid #ef4444; }

    .badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .patient-hero-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
    }

    .avatar-premium {
        width: 42px !important;
        height: 42px !important;
        border-radius: 10px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .avatar-premium i {
        font-size: 1.1rem !important;
    }

    .mini-avatar-premium {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .badge-premium {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        text-align: center;
    }
    
    .panel-details-table td {
        font-size: 0.9rem;
    }

    .symptoms-alert {
        background-color: rgba(220, 53, 69, 0.03);
        border: 1px dashed rgba(220, 53, 69, 0.25);
    }

    .premium-table th {
        font-size: 0.75rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .premium-row {
        transition: background-color 0.2s ease;
    }
    .premium-row:hover {
        background-color: #f8fafc !important;
    }
    
    .btn-icon-premium {
        background: transparent;
        border: none;
        padding: 0.4rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-icon-premium:hover {
        background: #f1f5f9;
        transform: scale(1.15);
    }

    .btn-hover-transform {
        transition: all 0.2s ease;
    }
    .btn-hover-transform:hover {
        transform: scale(1.02);
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulseDot 2s infinite;
    }

    .upload-drag-zone {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
        transition: all 0.2s ease;
    }
    .upload-drag-zone:hover {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.02);
    }

    @keyframes pulseDot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
</style>