<template>
  <div class="col-lg-9">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

      <!-- HEADER TABS -->
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <ul class="nav nav-tabs custom-tabs border-0">

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'consultas' }"
              @click="tabActiva = 'consultas'"
              type="button">
              <i class="fas fa-stethoscope"></i> Consultas
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'recetas' }"
              @click="tabActiva = 'recetas'"
              type="button">
              <i class="fas fa-prescription"></i> Recetas
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'archivos' }"
              @click="tabActiva = 'archivos'"
              type="button">
              <i class="fas fa-file-medical"></i> Archivos
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'notas' }"
              @click="tabActiva = 'notas'"
              type="button">
              <i class="fas fa-notes-medical"></i> Notas
            </button>
          </li>

        </ul>
      </div>

      <!-- CONTENIDO TABS -->
      <div class="card-body p-4">

        <!-- CONSULTAS -->
        <div v-if="tabActiva === 'consultas'">
          <div class="section-title">
            <h5>Historial de consultas</h5>
            <small>Registro cronológico de atención médica</small>
          </div>

          <div v-if="infoConsultas.length === 0" class="alert alert-info text-center">
            <i class="fas fa-folder-open mr-2"></i>
            No se encuentran consultas registradas para este paciente.
          </div>

          <div
            v-else
            class="timeline-card"
            v-for="consulta in infoConsultas"
            :key="consulta.id">
            <div class="timeline-dot" :class="colorPunto(consulta.tipo_consulta)"></div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between flex-wrap gap-2">
                <h6 class="fw-bold mb-0">{{ formatearFecha(consulta.fecha) }}</h6>
                <span
                  class="badge rounded-pill px-3 py-2"
                  :class="colorBadge(consulta.tipo_consulta)">
                  {{ consulta.tipo_consulta }}
                </span>
              </div>

              <template v-if="consulta.motivo">
                <p class="mt-2 mb-1"><strong>Motivo:</strong> {{ consulta.motivo }}</p>
                <p class="text-muted small mb-3" v-if="consulta.diagnostico">
                  Diagnóstico: {{ consulta.diagnostico }}
                </p>
                <a :href="`/HistorialConsulta/${consulta.id}`" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                  Ver consulta completa
                </a>
              </template>

              <p class="mt-2 mb-0 text-muted" v-else>
                {{ consulta.descripcion }}
              </p>
            </div>
          </div>
        </div>

        <!-- RECETAS -->
        <div v-if="tabActiva === 'recetas'">
          <div class="section-title">
            <h5>Historial de recetas</h5>
            <small>Tratamientos indicados al paciente</small>
          </div>

          <div class="record-card">
            <div>
              <h6 class="fw-bold mb-1">10 Mayo 2026</h6>
              <p class="mb-0 text-muted">Amoxicilina 500mg, cada 8 horas por 7 días.</p>
            </div>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
              Ver receta
            </button>
          </div>

          <div class="record-card">
            <div>
              <h6 class="fw-bold mb-1">02 Abril 2026</h6>
              <p class="mb-0 text-muted">Ibuprofeno 400mg, cada 12 horas por 3 días.</p>
            </div>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
              Ver receta
            </button>
          </div>
        </div>

        <!-- ARCHIVOS -->
        <div v-if="tabActiva === 'archivos'">
          <div class="section-title">
            <h5>Archivos clínicos</h5>
            <small>Estudios, análisis e imágenes médicas</small>
          </div>

          <div class="row">
            <div v-if="infoArchivos.length === 0" class="col-12">
              <div class="alert alert-info text-center">
                  <i class="fas fa-folder-open mr-2"></i>
                   No se encuentran archivos clínicos para este paciente.
              </div>            
            </div>
            <div 
              class="col-md-4" 
              v-else 
              v-for="documentos in infoArchivos" 
              :key="documentos?.id">
              <div class="file-card">
                <i :class="obtenerIcono(documentos?.archivo_url)"></i>
                  <h6>{{ documentos.tipo_archivo }}</h6>
                  <small class="text-muted">{{ documentos.fecha_subida }}</small>
                <button 
                    class="btn btn-sm btn-outline-primary rounded-pill mt-3"
                    v-if="documentos.archivo_url"
                    @click="verArchivo(documentos)">
                  Ver archivo
                </button>
                <button 
                    v-else
                    class="btn btn-sm btn-outline-primary rounded-pill mt-3"
                    disabled>
                  Sin Archivo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- NOTAS -->
        <div v-if="tabActiva === 'notas'">
          <div class="section-title">
            <h5>Notas médicas</h5>
            <small>Observaciones generales del paciente</small>
          </div>

          <div class="note-card">
            <small class="text-muted">15 Mayo 2026</small>
            <p class="mb-0 mt-2">
              Paciente reporta mejoría notable. Continuar tratamiento actual.
            </p>
          </div>

          <div class="note-card">
            <small class="text-muted">03 Abril 2026</small>
            <p class="mb-0 mt-2">
              Se recomienda mantener hidratación y vigilancia de síntomas.
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>
  
  <!--Modal para mostrar los archivos a traves de un modal-->
  <div class="modal fade" id="modalArchivo">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista De Los Arcchivos Clínicos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <img
                    v-if="archivoSeleccionado && esImagen(archivoSeleccionado)"
                    :src="archivoSeleccionado"
                    class="img-fluid">
                <iframe
                    v-else
                    :src="archivoSeleccionado"
                    width="100%"
                    height="700"
                    frameborder="0"
                ></iframe>
            </div>
        </div>
    </div>
</div>
<!--Aqui termina el modal para ver los archivos-->
</template>

<style scoped>
body {
  background: #f4f6f9;
}

.rounded-4 {
  border-radius: 24px !important;
}

.custom-tabs .nav-link {
  border: none;
  color: #6c757d;
  font-weight: 700;
  padding: 14px 18px;
  border-radius: 16px 16px 0 0;
  cursor: pointer;
  background: transparent;
}

.custom-tabs .nav-link.active {
  background: #f4f8ff;
  color: #0d6efd;
}

.section-title {
  margin-bottom: 20px;
}

.section-title h5 {
  font-weight: 800;
  margin-bottom: 2px;
}

.section-title small {
  color: #6c757d;
}

.timeline-card,
.record-card,
.note-card {
  background: #fff;
  border: 1px solid #edf0f4;
  border-radius: 18px;
  padding: 18px;
  margin-bottom: 16px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
}

.timeline-card {
  display: flex;
  gap: 16px;
}

.timeline-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  margin-top: 5px;
  flex-shrink: 0;
}

.record-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.file-card {
  border: 1px solid #edf0f4;
  border-radius: 20px;
  padding: 22px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.file-card i {
  font-size: 42px;
  margin-bottom: 14px;
}

.file-card h6 {
  font-weight: 700;
  word-break: break-word;
}

@media (max-width: 768px) {
  .record-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .custom-tabs .nav-link {
    padding: 10px 12px;
    font-size: 13px;
  }
}
</style>
<script>
    import ApiService from '../../services/ApiService.js'
    export default {
        name: 'ExpedienteTabs',
        data() {
            return {  
              tabActiva: 'consultas',
              infoArchivos: [],
              infoConsultas: [],
              archivoSeleccionado:''
            }
        },
        mounted() {
            console.log('PROP PacienteId:', this.pacienteId);
        },
        methods: {
          async obtenerConsultas(){
              try {
                  const response = await ApiService.get('/historialClinico?paciente_id=' + this.pacienteId)

                  const consultas = response.data.consultas || []

                  // Mapeamos la estructura real del backend a lo que pinta la tarjeta:
                  // - diagnóstico viene de la primera evaluación de IA de esa consulta
                  // - "descripcion" (tarjeta sin motivo) usa la recomendación de la IA si no hay motivo
                  this.infoConsultas = consultas.map(consulta => {
                    const evaluacion = (consulta.evaluaciones && consulta.evaluaciones[0]) || null

                    return {
                      id: consulta.id,
                      fecha: consulta.created_at,
                      tipo_consulta: consulta.estado,
                      motivo: consulta.motivo_consulta,
                      diagnostico: evaluacion ? evaluacion.diagnostico_probable : null,
                      descripcion: evaluacion ? evaluacion.recomendacion : null
                    }
                  })

                  console.log('Historial clínico cargado:', this.infoConsultas)
              } catch(error){
                  console.error("Error al obtener el historial clínico:", error)
              }
          },
          //Formatea la fecha que llega en created_at (ej: 2026-05-10T14:32:00.000000Z)//
          formatearFecha(fecha){
            if(!fecha) return ''
            const f = new Date(fecha)
            return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
          },
          //Colores del punto de la línea de tiempo según el estado real de la consulta//
          // ⚠️ Ajusta estos valores si tu columna `estado` usa otros textos
          colorPunto(estado){
            switch((estado || '').toLowerCase()){
              case 'finalizada':
              case 'completada':
                return 'bg-success'
              case 'en_proceso':
                return 'bg-warning'
              case 'urgencia':
                return 'bg-danger'
              default:
                return 'bg-secondary'
            }
          },
          //Colores del badge según el estado real de la consulta//
          colorBadge(estado){
            switch((estado || '').toLowerCase()){
              case 'finalizada':
              case 'completada':
                return 'bg-success'
              case 'en_proceso':
                return 'bg-warning text-dark'
              case 'urgencia':
                return 'bg-danger'
              default:
                return 'bg-secondary'
            }
          },
          //Función para obtener los archivos de un paciente mediante su ID//
            async obtenerArchivos(){
                try {
                    const response = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
                    this.infoArchivos = response.data.archivos || []
                    console.log('Archivos cargados:',this.infoArchivos)
                }catch(error){
                        console.error("Error al obtener Archivos:", error)
                }
            },
          //Función para determinar que tipo de archivo se mostrara mediante un modal//
            verArchivo(documentos){
              this.archivoSeleccionado = '/' + documentos.archivo_url
              $('#modalArchivo').modal('show')
            },
            esImagen(ruta){
                return /\.(jpg|jpeg|png|gif|webp)$/i.test(ruta)
            },
          //Aquítermina la función para determinar que tipo de archivo se mostrara mediante un modal//
          //Función para determinar la extención de archvo por ejemplo (pdf,docx,jpg etc)
             obtenerIcono(ruta){
              console.log('Ruta recibida:', ruta);
              if(!ruta){
                  return 'fas fa-file-alt text-secondary';
              }
              const extension = ruta.split('.').pop().toLowerCase();
              switch(extension){
                  case 'pdf':
                      return 'fas fa-file-pdf text-danger';
                  case 'doc':
                  case 'docx':
                      return 'fas fa-file-word text-primary';
                  case 'xls':
                  case 'xlsx':
                      return 'fas fa-file-excel text-success';
                  case 'ppt':
                  case 'pptx':
                      return 'fas fa-file-powerpoint text-warning';
                  case 'jpg':
                  case 'jpeg':
                  case 'png':
                  case 'gif':
                  case 'webp':
                      return 'fas fa-file-image text-info';
                  default:
                      return 'fas fa-file-alt text-secondary';
                }
             }
          //Aquí termina la función para determinar la extención del archivo ///
        },
        props:{
            //Esto guarda la el id que se trajo mediante la ruta parametrizada que el master hereda a los componentes hijos
            pacienteId:{
                type: [Number, String],
                required: true
            }
        },
        watch:{
            pacienteId:{
                immediate:true,
                handler(id){
                    if(id){
                        this.obtenerArchivos();
                        this.obtenerConsultas();
                    }
                }
            }
        }
    }
</script>