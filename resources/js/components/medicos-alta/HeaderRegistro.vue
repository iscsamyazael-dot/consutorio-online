<template>

<div class="row mb-5">
        <div class="col-12 text-center text-md-left d-md-flex align-items-center justify-content-between header-custom-container p-4 rounded shadow-sm bg-white">
            <div>
                <h1 class="font-weight-black text-dark mb-1 tracking-tight">
                    <span class="badge badge-primary-gradient p-2 mr-2 rounded-lg">
                        <i class="fas fa-user-md text-white animate-pulse"></i>
                    </span> 
                    Registrar Nuevo Médico
                </h1>
                <p class="text-muted mb-0 ml-md-5 pl-md-2 font-weight-light">Alta y asignación de horarios para el personal de salud.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button @click="$emit('volver')" class="btn btn-default">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

</template>