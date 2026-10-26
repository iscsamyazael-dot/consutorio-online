<template>
  <div class="search-container">
    <div class="search-box">
      <i class="fas fa-search search-icon"></i>
      <input
        type="text"
        class="form-control search-input"
        placeholder="Buscar paciente por nombre o folio..."
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({ modelValue: { type: String, default: '' } })
defineEmits(['update:modelValue'])
</script>

<style scoped>
.search-container {
  margin-bottom: 0;
}

.search-box {
  position: relative;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
  font-size: 14px;
  z-index: 2;
}

.search-input {
  padding: 12px 16px 12px 44px;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.search-input:focus {
  background: white;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
  outline: none;
}

.search-input::placeholder {
  color: #adb5bd;
}
</style>