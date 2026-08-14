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
                :citas="citasFiltradas"
                @cita-actualizada="obtenerCitas"
                @citas-visibles-cambiadas="onCitasVisiblesCambiadas"
            ></calendario>
        </div>
        <div style="flex: 1;">
            <resumen-medico :citas="citasVisibles" :pendientes="citasPendientes"></resumen-medico>
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
            createUrl: 'AgendarCitas',
            citasPendientes: [],
            filtroMedicoId: '',
            filtroEspecialidadId: '',
            // NUEVO: conjunto de citas "activo" para el resumen.
            // - Si el calendario está en vista mes/semana -> todas las citasFiltradas.
            // - Si el usuario entró al detalle de un día -> solo las citas de ese día
            //   (puede ser un array vacío, en cuyo caso el resumen debe mostrar ceros).
            citasVisibles: []
        }
    },
    computed: {
        // Lista única de médicos a partir de las citas ya cargadas.
        // IMPORTANTE: un médico puede aparecer en citas de varias especialidades,
        // así que guardamos TODAS las especialidades que le hemos visto atender
        // (especialidadIds), no solo la primera que se encuentra al recorrer el array.
        medicosDisponibles() {
            const mapa = new Map()

            this.citas.forEach(c => {
                if (!c.medico) return

                const id = c.medico.id ?? c.medico.nombre
                const espId = c.especialidad
                    ? (c.especialidad.id ?? c.especialidad.nombre)
                    : ''

                if (!mapa.has(id)) {
                    mapa.set(id, {
                        id,
                        nombre: c.medico.nombre,
                        especialidadIds: new Set(espId ? [espId] : [])
                    })
                } else if (espId) {
                    mapa.get(id).especialidadIds.add(espId)
                }
            })

            // Convertimos cada Set a Array para que sea más fácil de consumir
            // en el componente hijo (agendamedica.vue)
            return Array.from(mapa.values()).map(m => ({
                ...m,
                especialidadIds: Array.from(m.especialidadIds)
            }))
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
        },
        // Citas filtradas por médico/especialidad seleccionados en agenda-medica
        // Esta sigue siendo la fuente de la verdad que recibe el calendario.
        citasFiltradas() {
            return this.citas.filter(c => {
                const medicoOk = !this.filtroMedicoId ||
                    (c.medico && (c.medico.id ?? c.medico.nombre) === this.filtroMedicoId)
                const especialidadOk = !this.filtroEspecialidadId ||
                    (c.especialidad && (c.especialidad.id ?? c.especialidad.nombre) === this.filtroEspecialidadId)
                return medicoOk && especialidadOk
            })
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
        obtenerCitas() {
            axios.get('/api/citas')
                .then(res => {
                    this.citas = res.data
                })
                .catch(err => console.error(err))
        },
        onFiltroCambiado(filtro) {
            this.filtroMedicoId = filtro.medicoId
            this.filtroEspecialidadId = filtro.especialidadId
        },
        // NUEVO: recibe desde Calendario.vue el conjunto de citas "activo" según
        // si el usuario está en vista mes/semana o dentro del detalle de un día.
        onCitasVisiblesCambiadas(citas) {
            this.citasVisibles = citas
        }
    }
}
</script>