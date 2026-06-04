<template>


    <!-- PERFIL -->
    <div class="col-lg-4">
        <div class="card shadow border-0 h-100">
            <div class="card-body text-center">

                <div class="mb-4">
                    <span class="badge bg-primary rounded-circle p-4">
                        <i class="fas fa-user-md fa-4x"></i>
                    </span>
                </div>

                <h4 class="fw-bold mb-1">
                    Dr. Gael 
                </h4>

                <p class="text-muted mb-3">
                    Médico General
                </p>

                <span class="badge bg-success mb-4">
                    Activo
                </span>

                <div class="d-grid">
                    <button
                        class="btn btn-outline-primary rounded-pill"
                        @click="abrirSelectorImagen"
                        type="button"
                    >
                        <i class="fas fa-camera me-2"></i>
                        Cambiar Foto
                    </button>

                    <input
                        ref="inputFoto"
                        type="file"
                        accept="image/*"
                        class="d-none"
                        @change="seleccionarFoto"
                    >
                </div>

                <hr>

                <div class="text-start">
                    <p class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        doctor@consultorio.com
                    </p>

                    <p class="mb-3">
                        <i class="fas fa-user-tag text-primary me-2"></i>
                        Médico
                    </p>

                    <p class="mb-0">
                        <i class="fas fa-calendar text-primary me-2"></i>
                        Registrado: 29/05/2026
                    </p>
                </div>

            </div>
        </div>
    </div>

    


</template>


<script setup>

import {ref} from 'vue'

const inputFoto = ref(null)

const abrirSelectorImagen = () => {
    inputFoto.value.click()
}

const seleccionarFoto = (event) => {
    const archivo = event.target.files[0]

    if (!archivo) return

    console.log('Archivo seleccionado:', archivo)

    // Aquí posteriormente enviarás la imagen a Laravel
}


</script>