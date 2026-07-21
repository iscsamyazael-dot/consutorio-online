<template>
    <!-- Sin paciente seleccionado: mostramos buscador -->
    <div v-if="!hasPaciente" class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">
                    <i class="fas fa-user-md mr-2"></i>
                    Selecciona un paciente para iniciar la consulta
                </h5>

                <div class="form-group">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar paciente por nombre..."
                        v-model="busqueda"
                        @input="buscarPacientes">
                </div>

                <div v-if="buscando" class="text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Buscando...
                </div>

                <ul v-else-if="resultados.length" class="list-group">
                    <li
                        v-for="p in resultados"
                        :key="p.id"
                        class="list-group-item list-group-item-action"
                        style="cursor:pointer"
                        @click="seleccionarPaciente(p)">
                        <strong>{{ p.nombre }}</strong>
                        <span class="text-muted" v-if="p.edad || p.sexo">
                            — {{ p.edad }} años | {{ p.sexo }}
                        </span>
                    </li>
                </ul>

                <div v-else-if="busqueda.length >= 2 && !buscando" class="text-muted">
                    No se encontraron pacientes con ese nombre.
                </div>
            </div>
        </div>
    </div>

    <!-- Con paciente seleccionado: consulta normal -->
    <template v-else>
        <div class="col-lg-3">
            <HistorialClinico 
                :consulta-id="consultaId" 
                :ia-data="iaData">
            </HistorialClinico>
            <AlertasClinicas 
                :ia-data="iaData">
            </AlertasClinicas>
            <ArchivosClinicos 
                ref="archivosClinicos"
                :consulta-id="consultaId">
            </ArchivosClinicos>
        </div>
        <div class="col-lg-6">
            <TranscripcionLive
                :paciente-id="pacienteId"
                @actualizarSintomas="actualizarSintomas"
                @actualizarIaData="actualizarIaData"
                @marcarErrorIa="marcarErrorIa"
                @actualizarConsultaId="actualizarConsultaId"
                @archivoSubido="refrescarArchivos">
            </TranscripcionLive>
            <PanelIA 
                :ia-data="iaData" 
                :has-error="iaError">
            </PanelIA>
        </div>
        <div class="col-lg-3">
            <RecetaInteligente 
                :sintomas="sintomasDetectados">
            </RecetaInteligente>
            <DerivacionClinica 
                :sintomas="sintomasDetectados">
            </DerivacionClinica>
        </div>
    </template>
</template>
<script>
import ApiService from '../../services/ApiService.js'
import TranscripcionLive from './TranscripcionLive.vue'
import PanelIA from './PanelIA.vue'
import HistorialClinico from './HistorialClinico.vue'
import AlertasClinicas from './AlertasClinicas.vue'
import ArchivosClinicos from './ArchivosClinicos.vue'
import DerivacionClinica from './DerivacionClinica.vue'
import RecetaInteligente from './RecetaInteligente.vue'
export default {
    components: {
        TranscripcionLive,
        PanelIA,
        HistorialClinico,
        AlertasClinicas,
        ArchivosClinicos,
        DerivacionClinica,
        RecetaInteligente
    },
    props:{
        pacienteId:{
            type:[String,Number],
            required:false,
            default:''
        }
    },
    data(){
        return{
            sintomasDetectados: [],
            iaData: null,
            iaError:false,
            consultaId:null,
            paciente:{},
            busqueda:'',
            resultados:[],
            buscando:false,
            debounceTimer:null,
            todosPacientes:[]
        }
    },
    computed:{
        hasPaciente(){
            return this.pacienteId !== null &&
                   this.pacienteId !== undefined &&
                   this.pacienteId !== '';
        }
    },
    mounted(){
        console.log(
            "Paciente recibido:",
            this.pacienteId
        );
        if(this.hasPaciente){
            this.obtenerPaciente();
        }else{
            this.cargarListaPacientes();
        }
    },
    methods:{
        async obtenerPaciente(){
            try{
                const response = await ApiService.get(
                    '/ExpedienteDetalle/' + this.pacienteId
                );
                this.paciente = response.data;
                console.log(
                    "Datos paciente:",
                    this.paciente
                );
                // Actualiza el encabezado del Blade
                const nombre = document.getElementById('nombrePaciente');
                const datos = document.getElementById('datosPaciente');
                if(nombre){
                    nombre.innerHTML =
                    this.paciente.nombre;
                }
                if(datos){
                    datos.innerHTML =
                    this.paciente.edad +
                    " años | " +
                    this.paciente.sexo;
                }
            }catch(error){
                console.error(
                    "Error al cargar paciente:",
                    error
                );
            }
        },
        async cargarListaPacientes(){
            this.buscando = true;
            try{
                const response = await ApiService.get('/pacientes');
                // Por si la respuesta viene envuelta en { data: [...] }
                this.todosPacientes = Array.isArray(response.data)
                    ? response.data
                    : (response.data.data || []);
            }catch(error){
                console.error(
                    "Error al cargar lista de pacientes:",
                    error
                );
                this.todosPacientes = [];
            }finally{
                this.buscando = false;
            }
        },
        buscarPacientes(){
            clearTimeout(this.debounceTimer);

            if(this.busqueda.length < 2){
                this.resultados = [];
                return;
            }

            this.debounceTimer = setTimeout(() => {
                const texto = this.busqueda.toLowerCase();
                this.resultados = this.todosPacientes.filter(p =>
                    (p.nombre || '').toLowerCase().includes(texto)
                );
            }, 200);
        },
        seleccionarPaciente(paciente){
            // Navega a la consulta inteligente ya con el paciente elegido
            window.location.href = '/ConsultaInteligente/' + paciente.id;
        },
        actualizarSintomas(sintomas){
            this.sintomasDetectados = sintomas
        },
        actualizarIaData(iaData){
            this.iaData = iaData
            this.iaError = false
        },
        marcarErrorIa(){
            this.iaError = true
        },
        actualizarConsultaId(consultaId){
            this.consultaId = consultaId
        },
        refrescarArchivos(){
            // Se dispara cuando TranscripcionLive.vue termina de subir
            // un archivo con éxito, para que la lista de ArchivosClinicos.vue
            // se actualice sin necesidad de recargar la página.
            if (this.$refs.archivosClinicos) {
                this.$refs.archivosClinicos.cargarArchivos()
            }
        }
    }
}
</script>