// ---------------------------------------------------------------------------
// Sincroniza el v-model de una hoja con el Master:
//  - Trabaja sobre una copia local (no muta la prop del padre).
//  - Completa los campos que falten con la estructura vacía de la hoja.
//  - Conserva claves extra que vengan del padre o de la IA.
//  - Evita bucles padre ↔ hijo comparando el contenido.
//
// Uso:
//   import modeloSincronizado from '../../mixins/modeloSincronizado'
//   mixins: [modeloSincronizado(crearVacio)]
//   ...y en el template usa  local.campo
// ---------------------------------------------------------------------------

const clonar = v => JSON.parse(JSON.stringify(v ?? null))

export function mezclar(base, valor) {
  if (Array.isArray(base)) {
    return Array.isArray(valor) ? clonar(valor) : clonar(base)
  }
  if (base && typeof base === 'object') {
    const v = valor && typeof valor === 'object' && !Array.isArray(valor) ? valor : {}
    const out = {}
    Object.keys(base).forEach(k => { out[k] = mezclar(base[k], v[k]) })
    Object.keys(v).forEach(k => { if (!(k in out)) out[k] = clonar(v[k]) })
    return out
  }
  return valor === undefined || valor === null ? base : valor
}

export default function modeloSincronizado(crearVacio, adaptarEntrada = v => v) {
  const normalizar = v => mezclar(crearVacio(), adaptarEntrada(v))

  return {
    props: {
      modelValue: { type: [Object, String], default: () => ({}) },
    },

    emits: ['update:modelValue'],

    data() {
      return { local: normalizar(this.modelValue) }
    },

    watch: {
      modelValue: {
        deep: true,
        handler(nuevo) {
          const normalizado = normalizar(nuevo)
          if (JSON.stringify(normalizado) === JSON.stringify(this.local)) return
          this.local = normalizado
        },
      },
      local: {
        deep: true,
        handler(valor) {
          this.$emit('update:modelValue', clonar(valor))
        },
      },
    },

    mounted() {
      // Entrega al Master la estructura completa desde el inicio
      this.$emit('update:modelValue', clonar(this.local))
    },
  }
}