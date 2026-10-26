<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header modal-header-danger border-0">
          <h5 class="modal-title fw-bold">
            <i class="fas fa-trash me-2"></i>Eliminar Paciente
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>
        <div class="modal-body p-5 text-center">
          <div class="delete-icon-wrapper mb-4">
            <div class="delete-icon-circle">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
          </div>
          <h5 class="fw-bold mb-2">¿Estás seguro de eliminar este paciente?</h5>
          <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer border-0 justify-content-center pb-4 pt-0">
          <button type="button" class="btn btn-cancel rounded-pill px-4" @click="$emit('close')">
            <i class="fas fa-times me-1"></i>Cancelar
          </button>
          <button type="button" class="btn btn-confirm-delete rounded-pill px-4" @click="$emit('confirm')">
            <i class="fas fa-trash me-1"></i>Sí, eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ show: { type: Boolean, default: false } })
defineEmits(['close', 'confirm'])
</script>

<style scoped>
.modal-header-danger {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  padding: 20px 24px;
}

.delete-icon-wrapper {
  display: flex;
  justify-content: center;
}

.delete-icon-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse-danger 2s infinite;
}

.delete-icon-circle i {
  font-size: 36px;
  color: #f5576c;
}

@keyframes pulse-danger {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(245, 87, 108, 0.4);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 15px rgba(245, 87, 108, 0);
  }
}

.btn-cancel {
  background: #6c757d;
  color: white;
  border: none;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #5a6268;
  color: white;
  transform: translateY(-2px);
}

.btn-confirm-delete {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  border: none;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-confirm-delete:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
  color: white;
}
</style>