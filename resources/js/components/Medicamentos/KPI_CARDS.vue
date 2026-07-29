<template>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-primary shadow-sm rounded-4">
                <div class="inner">
                    <h3>{{ cargando ? '...' : stockTotal }}</h3>
                    <p>Stock Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-capsules"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-danger shadow-sm rounded-4">
                <div class="inner">
                    <h3>{{ cargando ? '...' : criticos }}</h3>
                    <p>Medicamentos Críticos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-warning shadow-sm rounded-4">
                <div class="inner">
                    <h3>{{ cargando ? '...' : proximosCaducar }}</h3>
                    <p>Próximos a Caducar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-secondary shadow-sm rounded-4">
                <div class="inner">
                    <h3>{{ cargando ? '...' : sinExistencia }}</h3>
                    <p>Sin Existencia</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MedicamentosResumen',

    props: {
        medicamentos: {
            type: Array,
            default: () => []
        },
        cargando: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        stockTotal() {
            return this.medicamentos.reduce((total, m) => {
                return total + (m.inventario ? Number(m.inventario.stock_actual) : 0)
            }, 0)
        },

        // Críticos: tiene stock, pero está por debajo o igual al mínimo
        criticos() {
            return this.medicamentos.filter(m => {
                if (!m.inventario) return false
                const actual = Number(m.inventario.stock_actual)
                const minimo = Number(m.inventario.stock_minimo)
                return actual > 0 && actual <= minimo
            }).length
        },

        // Sin existencia: stock en 0
        sinExistencia() {
            return this.medicamentos.filter(m => {
                return m.inventario && Number(m.inventario.stock_actual) === 0
            }).length
        },

        // Próximos a caducar: fecha_caducidad dentro de los próximos 30 días
        proximosCaducar() {
            const hoy = new Date()
            const limite = new Date()
            limite.setDate(hoy.getDate() + 30)

            return this.medicamentos.filter(m => {
                if (!m.inventario || !m.inventario.fecha_caducidad) return false
                const fechaCad = new Date(m.inventario.fecha_caducidad)
                return fechaCad >= hoy && fechaCad <= limite
            }).length
        }
    }
}
</script>