// Misma lógica que alertasMedicamentos.vue y MedicamentoController@resumen.
// Se extrajo aquí para poder reutilizarla desde la campanita global,
// que no recibe 'medicamentos' como prop (vive fuera del árbol de esa página).
const DIAS_LIMITE_CADUCIDAD = 30

export function calcularAlertas(medicamentos = []) {
    const hoy = new Date()
    const limite = new Date()
    limite.setDate(limite.getDate() + DIAS_LIMITE_CADUCIDAD)

    const lista = []

    medicamentos.forEach((med) => {
        const inv = med.inventario
        if (!inv) return

        const nombreCompleto = `${med.nombre ?? ''} ${med.concentracion ?? ''}`.trim()

        if (inv.stock_actual == 0) {
            lista.push({
                id: `sin_stock_${med.id}`,
                tipo: 'sin_stock',
                nombre: nombreCompleto,
                mensaje: 'sin existencias.',
                read: false,
            })
        } else if (inv.stock_actual <= inv.stock_minimo) {
            lista.push({
                id: `stock_critico_${med.id}`,
                tipo: 'stock_critico',
                nombre: nombreCompleto,
                mensaje: `tiene stock crítico (${inv.stock_actual} unidades).`,
                read: false,
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
                    mensaje: `caduca en ${dias} día${dias === 1 ? '' : 's'}.`,
                    read: false,
                })
            } else if (fechaCad < hoy) {
                lista.push({
                    id: `caducado_${med.id}`,
                    tipo: 'caducado',
                    nombre: nombreCompleto,
                    mensaje: 'ya caducó.',
                    read: false,
                })
            }
        }
    })

    return lista
}