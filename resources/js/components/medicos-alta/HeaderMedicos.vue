<template>
  <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-3 text-dark">
                <i class="fas fa-user-md text-primary"></i>
                Gestión de Doctores
            </h1>
            <small class="m-2 text-muted">
                Administración del personal médico registrado
            </small>
        </div>

        <!-- Emitimos el evento al hacer clic -->
        <button @click="$emit('ir-a-registro')" class="btn btn-primary shadow-sm btn-lg">
            <i class="fas fa-plus-circle mr-1"></i>
            Registrar Médico
        </button>
  </div>
</template>

<script>
export default {
    emits: ['ir-a-registro'] // Declaramos el evento amigablemente
}
</script>