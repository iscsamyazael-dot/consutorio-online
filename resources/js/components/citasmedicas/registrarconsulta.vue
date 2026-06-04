<template>
  <div class="form-card">

    <div class="form-top">
      <div>
        <h2>Registrar Consulta</h2>
        <p>Completa la información médica del paciente.</p>
      </div>
      <div class="top-badge">
        <i class="fas fa-calendar-check"></i>
        Agenda activa
      </div>
    </div>

    <form :action="storeUrl" method="POST">

      <input type="hidden" name="_token" :value="csrfToken">

      <div class="form-grid">

        <div class="form-group full">
          <label>Nombre del Paciente</label>
          <div class="input-modern">
            <i class="fas fa-user"></i>
            <input type="text" name="nombre_paciente" placeholder="Escribir nombre del paciente" required>
          </div>
        </div>

        <div class="form-group">
          <label>Fecha</label>
          <div class="input-modern">
            <i class="fas fa-calendar"></i>
            <input type="date" name="fecha_cita" required>
          </div>
        </div>

        <div class="form-group">
          <label>Hora</label>
          <div class="input-modern">
            <i class="fas fa-clock"></i>
            <input type="time" name="hora_cita" required>
          </div>
        </div>

        <div class="form-group">
          <label>Estado</label>
          <div class="input-modern">
            <i class="fas fa-check-circle"></i>
            <select name="estado">
              <option value="Pendiente">Pendiente</option>
              <option value="Completada">Completada</option>
              <option value="Cancelada">Cancelada</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Teléfono</label>
          <div class="input-modern">
            <i class="fas fa-phone"></i>
            <input type="text" name="telefono" placeholder="9991234567" required>
          </div>
        </div>

        <div class="form-group full">
          <label>Observaciones</label>
          <textarea name="observaciones" placeholder="Agregar observaciones médicas..."></textarea>
        </div>

      </div>

      <div class="actions">
        <a :href="indexUrl" class="btn-cancel">Cancelar</a>
        <button type="submit" class="btn-save">
          <i class="fas fa-save"></i>
          Guardar Consulta
        </button>
      </div>

    </form>
  </div>
</template>

<script>
export default {
  name: 'RegistrarConsulta',
  props: {
    storeUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
  }
}
</script>

<style scoped>
.form-card {
  background: rgba(255, 255, 255, 0.78);
  backdrop-filter: blur(18px);
  border-radius: 28px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.form-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  gap: 15px;
  flex-wrap: wrap;
}

.form-top h2 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
}

.form-top p {
  margin: 6px 0 0;
  color: #64748b;
}

.top-badge {
  background: linear-gradient(135deg, #dbeafe, #eef2ff);
  color: #3730a3;
  padding: 12px 16px;
  border-radius: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 22px;
}

.full { grid-column: span 2; }

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 10px;
  font-weight: 700;
  color: #334155;
}

.input-modern { position: relative; }

.input-modern i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.input-modern input,
.input-modern select {
  width: 100%;
  height: 56px;
  border-radius: 18px;
  border: 1px solid #dbe4ee;
  background: white;
  padding: 0 18px 0 48px;
  outline: none;
  transition: 0.25s ease;
  box-sizing: border-box;
}

.input-modern input:focus,
.input-modern select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.10);
}

textarea {
  width: 100%;
  min-height: 130px;
  border-radius: 20px;
  border: 1px solid #dbe4ee;
  background: white;
  padding: 18px;
  resize: vertical;
  outline: none;
  transition: 0.25s ease;
  box-sizing: border-box;
}

textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.10);
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 14px;
  margin-top: 30px;
}

.btn-cancel {
  background: white;
  border: 1px solid #dbe4ee;
  padding: 13px 22px;
  border-radius: 16px;
  font-weight: 700;
  color: #334155;
  text-decoration: none;
  transition: 0.25s ease;
}

.btn-cancel:hover {
  background: #f8fafc;
  color: #0f172a;
  text-decoration: none;
}

.btn-save {
  border: none;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  color: white;
  padding: 13px 24px;
  border-radius: 16px;
  font-weight: 700;
  transition: 0.25s ease;
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
  cursor: pointer;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.35);
}

@media (max-width: 768px) {
  .form-grid { grid-template-columns: 1fr; }
  .full { grid-column: span 1; }
  .form-card { padding: 22px; }
  .actions { flex-direction: column; }
  .btn-save, .btn-cancel { width: 100%; text-align: center; }
}
</style>