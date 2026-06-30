<template>
    <div class="top-stats">

        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <h2>{{ totalCitas }}</h2>
                <p>Citas registradas hoy</p>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h2>{{ confirmadas }}</h2>
                <p>Confirmadas</p>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <h2>{{ canceladas }}</h2>
                <p>Canceladas</p>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: 'Stracartas',
    props: {
        citas: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            totalCitas: 0,
            confirmadas: 0,
            canceladas: 0
        }
    },
    watch: {
        citas: {
            immediate: true,
            handler(nuevasCitas) {
                this.calcularEstadisticas(nuevasCitas)
            }
        }
    },
    methods: {
        calcularEstadisticas(citas) {
            // Filtra solo las citas cuya 'fecha' sea hoy.
            // Asume formato 'YYYY-MM-DD' (el típico de Laravel/MySQL para columnas date).
            const hoy = new Date().toISOString().split('T')[0]

            const citasHoy = citas.filter(c => {
                if (!c.fecha) return false
                // por si la fecha viene con hora incluida (datetime), recorta a los primeros 10 caracteres
                return c.fecha.substring(0, 10) === hoy
            })

            this.totalCitas = citasHoy.length

            this.confirmadas = citasHoy.filter(c =>
                this.normalizar(c.estado) === 'confirmada'
            ).length

            this.canceladas = citasHoy.filter(c =>
                this.normalizar(c.estado) === 'cancelada'
            ).length
        },
        normalizar(valor) {
            // Compara sin importar mayúsculas/minúsculas ni espacios extra
            return (valor || '').toString().trim().toLowerCase()
        }
    }
}
</script>

<style scoped>
.top-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    padding: 1rem 0;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.stat-card h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    line-height: 1.1;
}

.stat-card p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #6b7280;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-card.blue .stat-icon { background: #E6F1FB; color: #185FA5; }
.stat-card.blue h2         { color: #185FA5; }

.stat-card.green .stat-icon { background: #EAF3DE; color: #3B6D11; }
.stat-card.green h2         { color: #3B6D11; }

.stat-card.orange .stat-icon { background: #FAEEDA; color: #854F0B; }
.stat-card.orange h2         { color: #854F0B; }
</style>