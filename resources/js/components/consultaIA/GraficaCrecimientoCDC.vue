<template>
    <div class="cdc-chart-wrap" v-if="grafica">
        <svg :viewBox="`0 0 ${grafica.anchoSvg} ${grafica.altoSvg}`" class="cdc-chart">
            <polygon :points="grafica.zonaNormal" class="cdc-zona-normal" />
            <polygon :points="grafica.zonaSobrepeso" class="cdc-zona-sobrepeso" />
            <polyline :points="grafica.lineas.p5" class="cdc-linea cdc-linea-limite" />
            <polyline :points="grafica.lineas.p50" class="cdc-linea cdc-linea-mediana" />
            <polyline :points="grafica.lineas.p85" class="cdc-linea cdc-linea-limite" />
            <polyline :points="grafica.lineas.p95" class="cdc-linea cdc-linea-limite" />
            <circle :cx="grafica.punto.x" :cy="grafica.punto.y" r="4" class="cdc-punto-paciente" />
            <text v-for="tick in grafica.ejeX" :key="tick.a" :x="tick.x" :y="grafica.altoSvg - 4" class="cdc-eje-texto" text-anchor="middle">{{ tick.a }}a</text>
        </svg>
        <div v-if="grafica.fueraDeRango" class="cdc-fuera-rango">
            ⚠️ Z-score {{ zScore.toFixed(2) }} — fuera del rango graficado. Punto anclado al borde.
        </div>
        <div class="cdc-leyenda">
            <span><i class="cdc-swatch cdc-swatch-bajo"></i> Bajo peso (&lt;P5)</span>
            <span><i class="cdc-swatch cdc-swatch-normal"></i> Normal (P5-P85)</span>
            <span><i class="cdc-swatch cdc-swatch-sobrepeso"></i> Sobrepeso (P85-P95)</span>
            <span><i class="cdc-swatch cdc-swatch-obesidad"></i> Obesidad (&gt;P95)</span>
            <span><i class="cdc-swatch cdc-swatch-punto"></i> Este paciente</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'GraficaCrecimientoCDC',
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
            const valores = curva.flatMap(p => [p.p5, p.p95])
            const minVal = Math.min(...valores) * 0.9
            const maxVal = Math.max(...valores) * 1.1

            const x = agemos => padIzq + ((agemos - minAge) / (maxAge - minAge)) * (anchoSvg - padIzq - padDer)
            const yRaw = valor => altoSvg - padAbajo - ((valor - minVal) / (maxVal - minVal)) * (altoSvg - padArriba - padAbajo)
            const yClamp = valor => Math.min(Math.max(yRaw(valor), padArriba), altoSvg - padAbajo)

            const linea = clave => curva.map(p => `${x(p.agemos).toFixed(1)},${yRaw(p[clave]).toFixed(1)}`).join(' ')
            const lineaInv = clave => curva.slice().reverse().map(p => `${x(p.agemos).toFixed(1)},${yRaw(p[clave]).toFixed(1)}`).join(' ')

            const topeSuperior = padArriba
            const topeInferior = altoSvg - padAbajo
            const xIni = x(minAge).toFixed(1)
            const xFin = x(maxAge).toFixed(1)

            const zonaBajoPeso   = `${xIni},${topeInferior} ` + linea('p5') + ` ${xFin},${topeInferior}`
            const zonaObesidad   = `${xIni},${topeSuperior} ` + linea('p95') + ` ${xFin},${topeSuperior}`
            const zonaNormal     = linea('p85') + ' ' + lineaInv('p5')
            const zonaSobrepeso  = linea('p95') + ' ' + lineaInv('p85')

            const fueraDeRango = Math.abs(this.zScore) > 3.5

            return {
                anchoSvg, altoSvg,
                lineas: { p5: linea('p5'), p50: linea('p50'), p85: linea('p85'), p95: linea('p95') },
                zonaNormal, zonaSobrepeso,   // ya sin zonaBajoPeso ni zonaObesidad
                punto: { x: x(this.agemos), y: yClamp(this.bmi) },
                ejeX: [2, 6, 10, 14, 18].filter(a => a * 12 >= minAge && a * 12 <= maxAge).map(a => ({ a, x: x(a * 12) })),
                fueraDeRango
            }
        }
    }
}
</script>

<style scoped>
.cdc-chart-wrap { margin-top: 10px; width: 100%; }
.cdc-chart { width: 100%; height: auto; background: #fff; border: 1px solid #E3E8EF; border-radius: 8px; overflow: hidden; }
.cdc-zona-bajo { fill: rgba(220,38,38,.08); stroke: none; }
.cdc-zona-normal { fill: rgba(14,159,110,.12); stroke: none; }
.cdc-zona-sobrepeso { fill: rgba(217,119,6,.10); stroke: none; }
.cdc-zona-obesidad { fill: rgba(220,38,38,.08); stroke: none; }
.cdc-linea { fill: none; stroke-width: 1.2; }
.cdc-linea-mediana { stroke: #0E9F6E; stroke-width: 1.6; }
.cdc-linea-limite { stroke: #94A3B8; stroke-dasharray: 3 2; }
.cdc-punto-paciente { fill: #0F172A; stroke: #fff; stroke-width: 1.5; }
.cdc-eje-texto { font-size: 7px; fill: #94A3B8; font-family: 'Inter', sans-serif; }
.cdc-leyenda { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; font-size: .68rem; color: #51607A; }
.cdc-swatch { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 3px; vertical-align: middle; }
.cdc-swatch-bajo { background: rgba(220,38,38,.35); }
.cdc-swatch-normal { background: rgba(14,159,110,.4); }
.cdc-swatch-sobrepeso { background: rgba(217,119,6,.4); }
.cdc-swatch-obesidad { background: rgba(220,38,38,.35); }
.cdc-swatch-punto { background: #0F172A; }
.cdc-fuera-rango { font-size: .68rem; color: #B91C1C; background: #FEF2F2; border-radius: 6px; padding: 4px 8px; margin-top: 4px; }
</style>