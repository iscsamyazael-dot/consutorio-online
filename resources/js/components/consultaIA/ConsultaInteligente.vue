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
    <!-- FILA PRINCIPAL -->
    <div class="row">
        <div class="col-lg-3">
            <HistorialClinico
                :paciente-id="pacienteId"
                :ia-data="iaData"
            />
            <AlertasClinicas
                :ia-data="iaData"
            />
            <ArchivosClinicos
                ref="archivosClinicos"
                :consulta-id="consultaId"
            />
        </div>
        <div class="col-lg-6">
            <TranscripcionLive
                :paciente-id="pacienteId"
                @actualizarSintomas="actualizarSintomas"
                @actualizarIaData="actualizarIaData"
                @marcarErrorIa="marcarErrorIa"
                @actualizarConsultaId="actualizarConsultaId"
                @archivoSubido="refrescarArchivos"
            />
            <PanelIA
                :ia-data="iaData"
                :has-error="iaError"
            />
        </div>
        <div class="col-lg-3">
            <RecetaInteligente
                :sintomas="sintomasDetectados"
                :consulta-id="consultaId"
            />
            <DerivacionClinica
                :sintomas="sintomasDetectados"
            />
        </div>
    </div>
    <!-- NOTA PSOAPP A TODO EL ANCHO -->
    <div class="row psoapp-row">
        <div class="col-12 psoapp-col">
            <NotaPSOAPP
                ref="notaPsoapp"
                :consulta-id="consultaId"
                :nota-psoapp="iaData ? iaData.nota_psoapp : null"
            />
        </div>
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
import NotaPSOAPP from './NotaPSOAPP.vue'
export default {
    components: {
        TranscripcionLive,
        PanelIA,
        HistorialClinico,
        AlertasClinicas,
        ArchivosClinicos,
        DerivacionClinica,
        RecetaInteligente,
        NotaPSOAPP
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
   methods: {
    async obtenerPaciente() {
        try {
            const response = await ApiService.get(
                '/ExpedienteDetalle/' + this.pacienteId
            );
            this.paciente = response.data;
            console.log(
                'Datos paciente:',
                this.paciente
            );
            // Actualiza el encabezado del Blade
            const nombre = document.getElementById('nombrePaciente');
            const datos = document.getElementById('datosPaciente');
            if (nombre) {
                nombre.innerHTML = this.paciente.nombre;
            }
            if (datos) {
                datos.innerHTML =
                    this.paciente.edad +
                    ' años | ' +
                    this.paciente.sexo;
            }
        } catch (error) {
            console.error(
                'Error al cargar paciente:',
                error
            );
        }
    },
    async cargarListaPacientes() {
        this.buscando = true;
        try {
            const response = await ApiService.get('/pacientes');
            // Por si la respuesta viene envuelta en { data: [...] }
            this.todosPacientes = Array.isArray(response.data)
                ? response.data
                : (response.data.data || []);
        } catch (error) {
            console.error(
                'Error al cargar lista de pacientes:',
                error
            );
            this.todosPacientes = [];
        } finally {
            this.buscando = false;
        }
    },
    buscarPacientes() {
        clearTimeout(this.debounceTimer);
        if (this.busqueda.length < 2) {
            this.resultados = [];
            return;
        }
        this.debounceTimer = setTimeout(() => {
            const texto = this.busqueda.toLowerCase();
            this.resultados = this.todosPacientes.filter(p =>
                (p.nombre || '')
                    .toLowerCase()
                    .includes(texto)
            );

        }, 200);
    },
    seleccionarPaciente(paciente) {

        // Navega a la consulta inteligente
        // con el paciente seleccionado
        window.location.href =
            '/ConsultaInteligente/' + paciente.id;
    },
    actualizarSintomas(sintomas) {

        this.sintomasDetectados = sintomas;

        console.log(
            'Síntomas detectados por IA:',
            sintomas
        );
    },
    actualizarIaData(iaData) {
        // Guardamos los datos de IA.
        // NotaPSOAPP.vue recibe "iaData.nota_psoapp" por la prop
        // :nota-psoapp declarada en el template, y se reparte solo
        // gracias al watch interno del componente — ya no hace falta
        // llamar manualmente a actualizarDesdeIA() por cada campo aquí.
        this.iaData = iaData;
        // Quitamos el estado de error
        this.iaError = false;
        console.log(
            'Datos recibidos de la IA:',
            iaData
        );
    },
    marcarErrorIa() {

        this.iaError = true;

        console.error(
            'Se produjo un error en el procesamiento de IA.'
        );
    },
    actualizarConsultaId(consultaId) {

        this.consultaId = consultaId;

        console.log(
            'Consulta ID actualizado:',
            consultaId
        );
    },
    refrescarArchivos() {

        // Se dispara cuando TranscripcionLive.vue
        // termina de subir un archivo correctamente.

        if (this.$refs.archivosClinicos) {

            this.$refs.archivosClinicos.cargarArchivos();
        }
    }
}
}
</script>
<style scoped>
.psoapp-row {
    width: 100%;
    margin-left: 0;
    margin-right: 0;
}
.psoapp-col {
    width: 100%;
    max-width: 100%;
    padding-left: 0;
    padding-right: 0;
}
.psoapp-col :deep(.psoapp-card) {
    width: 100% !important;
    max-width: 100% !important;
}
</style>