<!-- Predicción de Reabastecimiento -->
<template>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-chart-line"></i>
                Sugerencias de Reabastecimiento
            </h5>
            <button class="btn btn-sm btn-outline-secondary" @click="fetchPrediccion" :disabled="loading">
                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            </button>
        </div>
        <div class="card-body table-responsive">
            <!-- Estado: cargando -->
            <div v-if="loading" class="text-center text-muted py-4">
                <i class="fas fa-spinner fa-spin me-2"></i> Calculando predicciones...
            </div>

            <!-- Estado: error -->
            <div v-else-if="error" class="alert alert-danger mb-0">
                No se pudo cargar la predicción. <a href="#" @click.prevent="fetchPrediccion">Reintentar</a>
            </div>

            <!-- Estado: sin datos -->
            <div v-else-if="predicciones.length === 0" class="text-center text-muted py-4">
                <i class="fas fa-check-circle me-2 text-success"></i> No hay medicamentos registrados.
            </div>

            <table v-else class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Medicamento</th>
                        <th>Stock Actual</th>
                        <th>Consumo Diario Prom.</th>
                        <th>Días Restantes</th>
                        <th>Cantidad Sugerida</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in predicciones" :key="p.medicamento_id">
                        <td>
                            <strong>{{ p.nombre }}</strong>
                            <span class="text-muted"> {{ p.concentracion }}</span>
                            <br>
                            <small class="text-muted">{{ p.codigo }}</small>
                        </td>
                        <td>{{ p.stock_actual }}</td>
                        <td>
                            <span v-if="p.consumo_diario_promedio > 0">
                                {{ p.consumo_diario_promedio }} / día
                            </span>
                            <span v-else class="text-muted">Sin consumo reciente</span>
                        </td>
                        <td>
                            <span
                                v-if="p.dias_restantes_estimados !== null"
                                class="badge px-3 py-2"
                                :class="'badge-' + colorUrgencia(p.dias_restantes_estimados)">
                                {{ p.dias_restantes_estimados }} días
                            </span>
                            <span v-else class="badge badge-secondary px-3 py-2">
                                Sin estimar
                            </span>
                        </td>
                        <td>
                            <span v-if="p.cantidad_sugerida_pedir > 0" class="font-weight-bold text-primary">
                                {{ p.cantidad_sugerida_pedir }} unidades
                            </span>
                            <span v-else class="text-muted">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    emits: [],

    data() {
        return {
            predicciones: [],
            loading: false,
            error: false
        }
    },

    mounted() {
        this.fetchPrediccion()
    },

    methods: {
        async fetchPrediccion() {
            this.loading = true
            this.error = false
            try {
                // Ruta agregada en web.php:
                // Route::get('medicamentos/prediccion', [MedicamentoController::class, 'prediccion']);
                const response = await ApiService.get('/medicamentos/prediccion')
                this.predicciones = response.data
            } catch (error) {
                console.error('Error al cargar predicción de reabastecimiento:', error)
                this.error = true
            } finally {
                this.loading = false
            }
        },

        // Colorea el badge de días restantes según la urgencia.
        // Menos de 7 días = crítico, menos de 15 = advertencia, resto = ok.
        colorUrgencia(dias) {
            if (dias <= 7) return 'danger'
            if (dias <= 15) return 'warning'
            return 'success'
        }
    }
}
</script>