<template>
    <agenda-medica :create-url="createUrl"></agenda-medica>
    <dradmin></dradmin>

    <div style="display: flex; gap: 16px; align-items: flex-start;">
        <div style="flex: 2;">
            <calendario></calendario>
        </div>
        <div style="flex: 1;">
            <resumen-medico :citas="citas" :pendientes="citasPendientes"></resumen-medico>
        </div>
    </div>
</template>
<script> 
//importa los componenetes hijos que se usan en este template
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
                createUrl: '/citas/create',//Url a la que debe apuntar el voton Nueva cita  dentro de agenda medica 
                citasPendientes: [] // antes no existía y se usaba en el template, ver nota abajo
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
            }
        }
    }
</script>