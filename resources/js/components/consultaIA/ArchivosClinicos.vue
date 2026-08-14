<template>
    <!-- ARCHIVOS -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-medical"></i>
                Archivos Clínicos
            </h3>
            <div class="card-tools">
                <span v-if="cargando" class="spinner-border spinner-border-sm text-info"></span>
            </div>
        </div>
        <div class="card-body">
            <div
                v-if="!cargando && archivos.length === 0"
                class="text-muted"
                style="font-size:13px;"
            >
                Aún no hay archivos adjuntos en esta consulta.
            </div>
            <ul v-else class="list-group">
                <li
                    v-for="archivo in archivos"
                    :key="archivo.id"
                    class="list-group-item d-flex justify-content-between align-items-center"
                >
                    <span>
                        <i :class="iconoPorTipo(archivo.tipo)" class="mr-2"></i>
                        {{ archivo.nombre }}
                    </span>
                    <a :href="archivo.url_descarga" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Abrir archivo">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    props: {
        consultaId: {
            type: [String, Number],
            default: null
        }
    },
    data() {
        return {
            archivos: [],
            cargando: false
        }
    },
    watch: {
        consultaId: {
            immediate: true,
            handler(nuevoValor) {
                if (nuevoValor) this.cargarArchivos()
            }
        }
    },
    methods: {
        async cargarArchivos() {
            if (!this.consultaId) return

            this.cargando = true
            try {
                const response = await ApiService.get('/consultaIA/archivos/' + this.consultaId)
                this.archivos = response.data.archivos || []
            } catch (error) {
                console.error('Error al cargar archivos clínicos:', error)
                this.archivos = []
            } finally {
                this.cargando = false
            }
        },
        iconoPorTipo(tipo) {
            switch (tipo) {
                case 'pdf':
                    return 'fas fa-file-pdf text-danger'
                case 'word':
                    return 'fas fa-file-word text-primary'
                case 'imagen':
                    return 'fas fa-file-image text-success'
                default:
                    return 'fas fa-file text-secondary'
            }
        }
    }
}
</script>