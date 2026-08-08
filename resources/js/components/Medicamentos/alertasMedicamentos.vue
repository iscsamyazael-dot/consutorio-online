<!-- Interfaz alertas pequeñas -->
<template>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-danger">
                <i class="fas fa-bell"></i>
                Alertas Críticas
            </h5>
            <button class="btn btn-sm btn-outline-secondary" @click="$emit('actualizar-inventario')" :disabled="cargando">
                <i class="fas fa-sync-alt" :class="{ 'fa-spin': cargando }"></i>
            </button>
        </div>
        <div class="card-body">
            <!-- Estado: cargando -->
            <div v-if="cargando" class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin me-2"></i> Cargando alertas...
            </div>

            <!-- Estado: sin alertas -->
            <div v-else-if="alertas.length === 0" class="text-center text-muted py-3">
                <i class="fas fa-check-circle me-2 text-success"></i> Sin alertas pendientes
            </div>

            <!-- Alertas reales -->
            <div
                v-else
                v-for="alerta in alertas"
                :key="alerta.id"
                class="alert d-flex align-items-center"
                :class="claseAlerta(alerta.tipo)"
            >
                <i :class="iconoAlerta(alerta.tipo)" class="me-3"></i>
                <div>
                    <strong>{{ alerta.nombre }}</strong>
                    {{ alerta.mensaje }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// Umbral de días para considerar "próximo a caducar".
// Debe coincidir con MedicamentoController@resumen (30 días) y con
// InventarioMedico.vue / TablaMedicamentos.vue, para que todas las
// vistas muestren exactamente los mismos medicamentos como "por caducar".
const DIAS_LIMITE_CADUCIDAD = 30

export default {
    // Ya NO hace su propio fetch: recibe 'medicamentos' como prop desde
    // App.vue, la única fuente de la verdad compartida con la tabla y
    // las KPI cards. Así, cualquier edición/movimiento/eliminación que
    // dispare 'actualizar-inventario' en App.vue se refleja aquí al
    // instante, sin necesidad de recargar la página ni de cambiar de
    // pestaña para forzar un remount.
    props: {
        medicamentos: {
            type: Array,
            default: () => []
        },
        // Opcional: si el padre expone un estado de carga global,
        // se puede pasar aquí para mostrar el mismo spinner.
        cargando: {
            type: Boolean,
            default: false
        }
    },

    // Le avisa al padre que el usuario pidió refrescar manualmente
    // (botón de la campanita/sync), para que App.vue vuelva a pedir
    // los datos al API igual que hace tras guardar un cambio.
    emits: ['actualizar-inventario'],

    computed: {
        // Misma lógica que MedicamentoController@resumen, pero generando
        // el detalle de cada alerta en vez de solo el conteo.
        // IMPORTANTE: la fecha de caducidad se lee siempre de
        // medicamento.inventario.fecha_caducidad (fuente única de verdad,
        // la misma que usa el backend y la tabla de InventarioMedico.vue).
        //
        // Al ser un computed sobre la prop 'medicamentos', se recalcula
        // automáticamente cada vez que App.vue reasigna ese array
        // (por ejemplo después de guardar una edición), sin fetch propio.
        alertas() {
            const hoy = new Date()
            const limite = new Date()
            limite.setDate(limite.getDate() + DIAS_LIMITE_CADUCIDAD)

            const lista = []

            this.medicamentos.forEach((med) => {
                const inv = med.inventario
                if (!inv) return

                const nombreCompleto = `${med.nombre ?? ''} ${med.concentracion ?? ''}`.trim()

                if (inv.stock_actual == 0) {
                    lista.push({
                        id: `sin_stock_${med.id}`,
                        tipo: 'sin_stock',
                        nombre: nombreCompleto,
                        mensaje: 'sin existencias.'
                    })
                } else if (inv.stock_actual <= inv.stock_minimo) {
                    lista.push({
                        id: `stock_critico_${med.id}`,
                        tipo: 'stock_critico',
                        nombre: nombreCompleto,
                        mensaje: `tiene stock crítico (${inv.stock_actual} unidades).`
                    })
                }

                if (inv.fecha_caducidad) {
                    const fechaCad = new Date(inv.fecha_caducidad)
                    if (fechaCad >= hoy && fechaCad <= limite) {
                        const dias = Math.ceil((fechaCad - hoy) / (1000 * 60 * 60 * 24))
                        lista.push({
                            id: `por_caducar_${med.id}`,
                            tipo: 'por_caducar',
                            nombre: nombreCompleto,
                            mensaje: `caduca en ${dias} día${dias === 1 ? '' : 's'}.`
                        })
                    } else if (fechaCad < hoy) {
                        lista.push({
                            id: `caducado_${med.id}`,
                            tipo: 'caducado',
                            nombre: nombreCompleto,
                            mensaje: 'ya caducó.'
                        })
                    }
                }
            })

            return lista
        }
    },

    methods: {
        claseAlerta(tipo) {
            return {
                stock_critico: 'alert-danger',
                por_caducar: 'alert-warning',
                sin_stock: 'alert-secondary',
                caducado: 'alert-dark'
            }[tipo] || 'alert-light'
        },

        iconoAlerta(tipo) {
            return {
                stock_critico: 'fas fa-exclamation-triangle',
                por_caducar: 'fas fa-clock',
                sin_stock: 'fas fa-ban',
                caducado: 'fas fa-skull-crossbones'
            }[tipo] || 'fas fa-info-circle'
        }
    }
}
</script>