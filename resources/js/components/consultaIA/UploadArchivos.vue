<template>
    <div class="card card-info card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-medical me-2"></i>
                Adjuntar documento clínico
            </h3>
        </div>

        <div class="card-body">

            <div class="border rounded p-4 text-center bg-light">

                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>

                <h5>Subir PDF, Word o Imagen</h5>

                <small class="text-muted">
                    Formatos permitidos:
                    PDF, DOC, DOCX, JPG, JPEG y PNG
                </small>

                <input
                    ref="inputArchivo"
                    class="form-control mt-4"
                    type="file"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    @change="seleccionarArchivo"
                >

                <div
                    v-if="archivo"
                    class="alert alert-info mt-3 text-start"
                >
                    <strong>Archivo:</strong>

                    {{ archivo.name }}

                    <br>

                    <strong>Tamaño:</strong>

                    {{ (archivo.size / 1024 / 1024).toFixed(2) }} MB
                </div>

                <button
                    class="btn btn-primary mt-3"
                    :disabled="!archivo || cargando"
                    @click="subirArchivo"
                >

                    <span
                        v-if="cargando"
                        class="spinner-border spinner-border-sm me-2"
                    ></span>

                    <i
                        v-else
                        class="fas fa-upload me-2"
                    ></i>

                    {{ cargando ? 'Subiendo...' : 'Subir archivo' }}

                </button>

            </div>

            <div
                v-if="mensaje"
                class="alert mt-3"
                :class="ok ? 'alert-success':'alert-danger'"
            >
                {{ mensaje }}
            </div>

            <!-- TEXTO EXTRAÍDO -->

            <div
                v-if="textoExtraido"
                class="card mt-4"
            >
                <div class="card-header">
                    Texto extraído
                </div>

                <div class="card-body">

                    <pre
                        style="white-space: pre-wrap"
                    >{{ textoExtraido }}</pre>

                </div>
            </div>

            <!-- IA -->

            <div
                v-if="resultadoIA"
                class="card mt-3 border-success"
            >

                <div class="card-header bg-success text-white">
                    Resultado IA
                </div>

                <div class="card-body">

                    <p>

                        <strong>Diagnóstico probable:</strong>

                        {{ resultadoIA.diagnostico_probable }}

                    </p>

                    <p>

                        <strong>Nivel de riesgo:</strong>

                        {{ resultadoIA.nivel_riesgo }}

                    </p>

                    <div
                        v-if="resultadoIA.sintomas?.length"
                    >

                        <strong>Síntomas detectados</strong>

                        <ul>

                            <li
                                v-for="s in resultadoIA.sintomas"
                                :key="s"
                            >
                                {{ s }}
                            </li>

                        </ul>

                    </div>

                    <div
                        v-if="resultadoIA.recomendaciones?.length"
                    >

                        <strong>Recomendaciones</strong>

                        <ul>

                            <li
                                v-for="r in resultadoIA.recomendaciones"
                                :key="r"
                            >
                                {{ r }}
                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>

import { ref } from 'vue'

const props = defineProps({

    consultaId: {
        type: Number,
        required: true
    }

})

const emit = defineEmits([
    'archivoProcesado'
])

const archivo = ref(null)

const inputArchivo = ref(null)

const cargando = ref(false)

const mensaje = ref('')

const ok = ref(false)

const textoExtraido = ref('')

const resultadoIA = ref(null)

const seleccionarArchivo = (e) => {

    const file = e.target.files[0]

    if (!file) return

    const permitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png'
    ]

    if (!permitidos.includes(file.type)) {

        alert('Formato no permitido')

        e.target.value=''

        return

    }

    archivo.value=file

}

const subirArchivo = async ()=>{

    if(!archivo.value) return

    cargando.value=true

    mensaje.value=''

    textoExtraido.value=''

    resultadoIA.value=null

    const formData=new FormData()

    formData.append('consulta_id',props.consultaId)

    formData.append('archivo',archivo.value)

    try{

        const response=await fetch('/consulta-ia/archivo',{

            method:'POST',

            headers:{
                'X-CSRF-TOKEN':document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },

            body:formData

        })

        const data=await response.json()

        if(!response.ok){

            throw new Error(data.error)

        }

        ok.value=true

        mensaje.value='Archivo procesado correctamente.'

        textoExtraido.value=data.texto_extraido

        resultadoIA.value=data.ia_data

        emit('archivoProcesado',data)

        archivo.value=null

        inputArchivo.value.value=''

    }

    catch(error){

        ok.value=false

        mensaje.value=error.message

    }

    finally{

        cargando.value=false

    }

}

</script>