<template>
  <div class="container-fluid px-0">
    <div class="header-empresas">
      <div class="row align-items-center">
        <!-- Título compacto -->
        <div class="col-auto">
          <div class="d-flex align-items-center">
            <div class="header-icon">
              <i class="fas fa-hospital-alt"></i>
            </div>
            <div class="ml-3">
              <h2 class="mb-0 font-weight-bold">Gestión de Empresas</h2>
              <small class="text-muted">Catálogo de empresas cliente</small>
            </div>
          </div>
        </div>
        
        <!-- Espacio flexible -->
        <div class="col"></div>
        
        <!-- Botón Registrar -->
        <div class="col-auto">
          <button @click="$emit('ir-a-registro')" class="btn-registrar">
            <div class="btn-registrar-content">
              <div class="btn-icon-circle">
                <i class="fas fa-plus"></i>
              </div>
              <div class="btn-text">
                <span class="btn-title">Registrar Empresa</span>
                <span class="btn-subtitle">Nueva empresa cliente</span>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {}
  }
}
</script>

<style scoped>
.header-empresas {
  background: #ffffff;
  padding: 18px 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 20px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.4rem;
  box-shadow: 0 4px 12px rgba(0, 97, 242, 0.25);
}

h2 {
  font-size: 1.3rem;
  letter-spacing: -0.3px;
  color: #1e293b;
}

h2 + small {
  font-size: 0.82rem;
}

.btn-registrar {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 97, 242, 0.3);
}

.btn-registrar:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4);
}

.btn-registrar-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-icon-circle {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
}

.btn-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.btn-title {
  color: white;
  font-weight: 700;
  font-size: 0.9rem;
  line-height: 1.2;
}

.btn-subtitle {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.72rem;
  line-height: 1.2;
  margin-top: 2px;
}
</style>