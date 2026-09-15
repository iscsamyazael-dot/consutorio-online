<template>
    <div class="oms-chart-wrap" v-if="grafica">
        <svg :viewBox="`0 0 ${grafica.anchoSvg} ${grafica.altoSvg}`" class="oms-chart">
            <polygon :points="grafica.zonaNormal" class="oms-zona-normal" />
            <polyline :points="grafica.lineas.sd3" class="oms-linea oms-linea-extrema" />
            <polyline :points="grafica.lineas.sd2" class="oms-linea oms-linea-alerta" />
            <polyline :points="grafica.lineas.sd_2" class="oms-linea oms-linea-alerta" />
            <polyline :points="grafica.lineas.sd_3" class="oms-linea oms-linea-extrema" />
            <polyline :points="grafica.lineas.sd0" class="oms-linea oms-linea-mediana" />
            <circle :cx="grafica.punto.x" :cy="grafica.punto.y" r="4" class="oms-punto-paciente" />
            <text v-for="tick in grafica.ejeX" :key="tick.m" :x="tick.x" :y="grafica.altoSvg - 4" class="oms-eje-texto" text-anchor="middle">{{ tick.m }}m</text>
        </svg>
        <div v-if="grafica.fueraDeRango" class="oms-fuera-rango">
            ⚠️ Z-score {{ zScore.toFixed(2) }} — fuera del rango graficado (más allá de ±3 DE). Punto anclado al borde.
        </div>
        <div class="oms-leyenda">
            <span><i class="oms-swatch oms-swatch-normal"></i> Normal (-2 a +1 DE)</span>
            <span><i class="oms-swatch oms-swatch-alerta"></i> Alerta (±2 a ±3 DE)</span>
            <span><i class="oms-swatch oms-swatch-punto"></i> Este paciente</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'GraficaCrecimientoOMS',
    props: {
        curva: { type: Array, required: true },
        agemos: { type: Number, required: true },
        bmi: { type: Number, required: true },
        zScore: { type: Number, required: true }
    },
    computed: {
        grafica() {
            if (!this.curva?.length) return null

            const anchoSvg = 500, altoSvg = 80
            const padIzq = 30, padDer = 8, padArriba = 8, padAbajo = 18
            const curva = this.curva

            const minAge = curva[0].agemos
            const maxAge = curva[curva.length - 1].agemos
            const valores = curva.flatMap(p => [p.sd_3, p.sd3])
            const minVal = Math.min(...valores) * 0.95
            const maxVal = Math.max(...valores) * 1.05

            const x = agemos => padIzq + ((agemos - minAge) / (maxAge - minAge)) * (anchoSvg - padIzq - padDer)
            const yRaw = valor => altoSvg - padAbajo - ((valor - minVal) / (maxVal - minVal)) * (altoSvg - padArriba - padAbajo)
            const yClamp = valor => Math.min(Math.max(yRaw(valor), padArriba), altoSvg - padAbajo)

            const linea = clave => curva.map(p => `${x(p.agemos).toFixed(1)},${yRaw(p[clave]).toFixed(1)}`).join(' ')

            const zonaNormal = linea('sd1') + ' ' +
                curva.slice().reverse().map(p => `${x(p.agemos).toFixed(1)},${yRaw(p.sd_2).toFixed(1)}`).join(' ')

            // clínicamente preciso: fuera de rango si |z| > 3, sin importar
            // dónde caiga el pixel en un lienzo que mezcla todas las edades
            const fueraDeRango = Math.abs(this.zScore) > 3

            return {
                anchoSvg, altoSvg,
                lineas: { sd3: linea('sd3'), sd2: linea('sd2'), sd_1: linea('sd_1'), sd0: linea('sd0'), sd_2: linea('sd_2'), sd_3: linea('sd_3') },
                zonaNormal,
                punto: { x: x(this.agemos), y: yClamp(this.bmi) },
                ejeX: [0, 6, 12, 18, 24].filter(m => m >= minAge && m <= maxAge).map(m => ({ m, x: x(m) })),
                fueraDeRango
            }
        }
    }
}
</script>

<style scoped>
.oms-chart-wrap {
    margin-top: 10px;
    width: 100%;
}
.oms-chart { width: 100%; height: auto; background: #fff; border: 1px solid #E3E8EF; border-radius: 8px; overflow: hidden;}
.oms-zona-normal { fill: rgba(14, 159, 110, 0.12); stroke: none; }
.oms-linea { fill: none; stroke-width: 1.2; }
.oms-linea-mediana { stroke: #0E9F6E; stroke-width: 1.6; }
.oms-linea-alerta { stroke: #D97706; stroke-dasharray: 3 2; }
.oms-linea-extrema { stroke: #DC2626; stroke-dasharray: 2 2; }
.oms-punto-paciente { fill: #0F172A; stroke: #fff; stroke-width: 1.5; }
.oms-eje-texto { font-size: 7px; fill: #94A3B8; font-family: 'Inter', sans-serif; }
.oms-leyenda { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; font-size: .72rem; color: #51607A; }
.oms-swatch { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 3px; vertical-align: middle; }
.oms-swatch-normal { background: rgba(14,159,110,.4); }
.oms-swatch-alerta { background: #D97706; }
.oms-swatch-punto { background: #0F172A; }
.oms-fuera-rango {
    font-size: .68rem;
    color: #B91C1C;
    background: #FEF2F2;
    border-radius: 6px;
    padding: 4px 8px;
    margin-top: 4px;
}
</style>