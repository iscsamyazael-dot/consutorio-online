<template>
  <div class="search-container">
    <div class="search-filters">
      <!-- Buscador de texto -->
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

      <!-- Filtro por Empresa -->
      <div class="filter-box">
        <i class="fas fa-building filter-icon"></i>
        <select
          class="form-select filter-select"
          :value="empresaFilter"
          @change="$emit('update:empresaFilter', $event.target.value)"
        >
          <option value="">Todas las empresas</option>
          <option
            v-for="empresa in empresas"
            :key="empresa.id"
            :value="empresa.id"
          >
            {{ empresa.razon_social }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

const props = defineProps({
  modelValue: { type: String, default: '' },
  empresaFilter: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue', 'update:empresaFilter'])

const empresas = ref([])

// Cargar lista de empresas al montar
const cargarEmpresas = async () => {
  try {
    const response = await ApiServiceClinicaTrabajo.get('/empresa-cliente?select=true')
    empresas.value = response.data || []
  } catch (error) {
    console.error('Error al cargar empresas:', error)
  }
}

onMounted(() => {
  cargarEmpresas()
})
</script>

<style scoped>
.search-container {
  margin-bottom: 0;
}

.search-filters {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 300px;
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

/* Filtro de Empresa */
.filter-box {
  position: relative;
  min-width: 250px;
}

.filter-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #667eea;
  font-size: 14px;
  z-index: 2;
  pointer-events: none;
}

.filter-select {
  padding: 12px 16px 12px 44px;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #f8f9fa;
  cursor: pointer;
}

.filter-select:focus {
  background: white;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
  outline: none;
}

@media (max-width: 768px) {
  .search-filters {
    flex-direction: column;
  }
  
  .search-box,
  .filter-box {
    min-width: 100%;
  }
}
</style>