<template>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-trash me-2"></i>Eliminar Paciente</h5>
                    <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="delete-icon mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="fw-bold mb-2">¿Estás seguro de eliminar este paciente?</h5>
                    <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" @click="$emit('close')">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" @click="$emit('confirm')">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({ show: { type: Boolean, default: false } })
defineEmits(['close', 'confirm'])
</script>