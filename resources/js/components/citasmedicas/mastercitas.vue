<template>
    <agenda-medica
        :create-url="createUrl"
        :medicos="medicosDisponibles"
        :especialidades="especialidadesDisponibles"
        @filtro-cambiado="onFiltroCambiado"
    ></agenda-medica>
    <dradmin></dradmin>

    <div style="display: flex; gap: 16px; align-items: flex-start;">
        <div style="flex: 2;">
            <calendario
                :medico-id="filtroMedicoId"
                :especialidad-id="filtroEspecialidadId"
                :citas="citas"
                @cita-actualizada="obtenerCitas"
            ></calendario>
        </div>
        <div style="flex: 1;">
            <resumen-medico :citas="citas" :pendientes="citasPendientes"></resumen-medico>
        </div>
    </div>
</template>
<script> 
import AgendaMedica from './agendamedica.vue';
import DrAdmin from './dradmin.vue';
import Calendario from './calendario.vue';  
import ResumenMedico from './Resumenmedico.vue';
import axios from 'axios';

    export default {
        components: {
            AgendaMedica,
            DrAdmin,
            Calendario,
            ResumenMedico
        },
        data() {
            return {
                citas: [],
                createUrl: '/citas/create',
                citasPendientes: [],
                filtroMedicoId: '',
                filtroEspecialidadId: ''
            }
        },
        computed: {
            // Lista única de médicos a partir de las citas ya cargadas
            medicosDisponibles() {
                const mapa = new Map()
                this.citas.forEach(c => {
                    if (!c.medico) return
                    const id = c.medico.id ?? c.medico.nombre
                    if (!mapa.has(id)) {
                        mapa.set(id, {
                            id,
                            nombre: c.medico.nombre,
                            especialidadId: c.especialidad ? (c.especialidad.id ?? c.especialidad.nombre) : ''
                        })
                    }
                })
                return Array.from(mapa.values())
            },
            // Lista única de especialidades a partir de las citas ya cargadas
            especialidadesDisponibles() {
                const mapa = new Map()
                this.citas.forEach(c => {
                    if (!c.especialidad) return
                    const id = c.especialidad.id ?? c.especialidad.nombre
                    if (!mapa.has(id)) {
                        mapa.set(id, { id, nombre: c.especialidad.nombre })
                    }
                })
                return Array.from(mapa.values())
            }
        },
        watch: {
            citas(nuevo) {
                nuevo.forEach(c => {
                console.log('ESTADO:', c.estado)
                })
            }
        },

        mounted() {
            axios.get('/api/citas')
            .then(res => {
                this.citas = res.data
            })
            .catch(err => console.error(err))
        },
        methods: {
            obtenerCitas (){
                axios.get ('/api/citas')
                        .then(res =>{
                            this.citas = res.data
                        })
                        .catch(err => console.error(err))
            },
            onFiltroCambiado(filtro) {
                this.filtroMedicoId = filtro.medicoId
                this.filtroEspecialidadId = filtro.especialidadId
            }
        }
    }
</script>