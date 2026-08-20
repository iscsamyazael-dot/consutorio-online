<template>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <!-- Encabezado -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="header-icon">
                            <i class="fas fa-filter"></i>
                        </span>
                        <h5 class="mb-0 fw-bold text-dark">
                            Filtrar Triage
                        </h5>
                    </div>
                    <small class="text-muted">
                        Consulta las evaluaciones de triage por fecha
                    </small>
                </div>

                <div class="filter-status">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Filtro por fecha
                </div>
            </div>

            <!-- Fila de filtro -->
            <div class="row g-3 align-items-end">

                <!-- Campo de Fecha -->
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="editorial-field">
                        <div class="field-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="dot-indicator bg-terracotta mb-2"></span>
                                <label class="field-label">Fecha de Triage</label>
                            </div>

                            <button
                                type="button"
                                class="pill-shortcut"
                                :class="{ active: esHoy }"
                                @click="seleccionarHoy"
                            >
                                <i class="bi bi-calendar-day me-1"></i>
                                Hoy
                            </button>
                        </div>

                        <div class="date-wrapper">
                            <i class="bi bi-calendar3 date-icon"></i>
                            <input
                                type="date"
                                v-model="filtros.fecha"
                                class="editorial-input"
                                @keyup.enter="aplicarFiltros"
                            >
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="col-12 col-md-4 col-lg-6">
                    <div class="filter-actions">
                        <!-- Restablecer -->
                        <transition name="soft-fade">
                            <button
                                v-if="hayFiltrosActivos"
                                type="button"
                                class="btn-editorial-ghost"
                                title="Mostrar todo el historial"
                                @click="limpiarFiltros"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Ver Todos</span>
                            </button>
                        </transition>

                        <!-- Filtrar -->
                        <button
                            type="button"
                            class="btn-editorial-primary"
                            @click="aplicarFiltros"
                        >
                            <span>Filtrar</span>
                            <i class="bi bi-arrow-right-short fs-5"></i>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: 'FiltroTriage',
    props: {
        fechaInicial: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            filtros: {
                fecha: this.fechaInicial
            }
        }
    },

    computed: {
        esHoy() {
            return !!this.filtros.fecha && this.filtros.fecha === this.obtenerFechaHoy();
        },

        hayFiltrosActivos() {
            return this.filtros.fecha !== '';
        }
    },

    watch: {
        fechaInicial(nuevaFecha) {
            this.filtros.fecha = nuevaFecha;
        }
    },

    methods: {
        obtenerFechaHoy() {
            const fecha = new Date();
            const anio = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');
            return `${anio}-${mes}-${dia}`;
        },

        seleccionarHoy() {
            this.filtros.fecha = this.obtenerFechaHoy();
            this.aplicarFiltros();
        },

        limpiarFiltros() {
            this.filtros.fecha = '';
            this.$emit('filtrar', { fecha: '' });
        },

        aplicarFiltros() {
            this.$emit('filtrar', {
                fecha: this.filtros.fecha
            });
        }
    }
}
</script>

<style scoped>
.editorial-field {
    width: 100%;
}

.field-header {
    margin-bottom: 8px;
}

.field-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.dot-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.bg-terracotta {
    background-color: #c96f4a;
}

/* FECHA */
.date-wrapper {
    position: relative;
}

.editorial-input {
    width: 100%;
    height: 44px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background-color: #fff;
    padding: 0 14px 0 42px;
    font-size: 0.9rem;
    color: #343a40;
    transition: all 0.2s ease;
}

.editorial-input:hover {
    border-color: #adb5bd;
}

.editorial-input:focus {
    outline: none;
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.08);
}

.date-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #c96f4a;
    pointer-events: none;
    z-index: 2;
}

/* BOTÓN HOY */
.pill-shortcut {
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    color: #6c757d;
    border-radius: 50px;
    padding: 3px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pill-shortcut:hover {
    background: #e9ecef;
}

.pill-shortcut.active {
    background: #fff3cd;
    border-color: #ffe69c;
    color: #997404;
}

/* ACCIONES */
.filter-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    min-height: 44px;
}

.btn-editorial-primary {
    height: 42px;
    border: 0;
    border-radius: 12px;
    background: #0d6efd;
    color: white;
    padding: 0 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-editorial-primary:hover {
    background: #0b5ed7;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.18);
}

.btn-editorial-ghost {
    height: 42px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background: white;
    color: #6c757d;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-editorial-ghost:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #ced4da;
}

/* ENCABEZADO */
.header-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #e7f1ff;
    color: #0d6efd;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}

.filter-status {
    border: 1px solid #e9ecef;
    background: #f8f9fa;
    color: #6c757d;
    border-radius: 50px;
    padding: 5px 11px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* TRANSICIÓN */
.soft-fade-enter-active,
.soft-fade-leave-active {
    transition: all 0.2s ease;
}

.soft-fade-enter-from,
.soft-fade-leave-to {
    opacity: 0;
    transform: translateX(5px);
}

/* RESPONSIVE */
@media (max-width: 767.98px) {
    .filter-actions {
        justify-content: flex-start;
        margin-top: 4px;
    }

    .btn-editorial-primary,
    .btn-editorial-ghost {
        flex: 1;
    }
}
</style>