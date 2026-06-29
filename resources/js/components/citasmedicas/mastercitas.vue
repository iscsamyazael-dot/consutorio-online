
<template>
    <agenda-medica></agenda-medica>
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
                citas:[]
                // Aquí puedes agregar datos específicos para MasterCitas si es necesario
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
            // Aquí puedes agregar métodos específicos para MasterCitas si es necesario
        }
    }
</script>