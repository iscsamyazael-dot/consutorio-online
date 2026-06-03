<template>
        <form action="" method="">
            <div class="form-grid">
                <!-- PACIENTE -->
                <div class="form-group full">
                    <label>Paciente</label>
                    <div class="input-modern">
                        <i class="fas fa-user"></i>
                        <input
                            type="text"
                            name="NombrePaciente"
                            placeholder = "Escribe el nombre del paciente">
                    </div>
                </div>
                <div class="form-group full">
                    <label>Telefono</label>
                    <div class="input-modern">
                        <i class="fas fa-user"></i>
                        <input
                            type="text"
                            name="Telefono"
                            placeholder = "Escribe el telefono del paciente">
                    </div>
                </div>
                <!-- FECHA -->
                <div class="form-group">
                    <label>Fecha</label>
                    <div class="input-modern">
                        <i class="fas fa-calendar"></i>
                        <input
                            type="date"
                            name="fecha_cita"
                            required
                        >
                    </div>
                </div>

                <!-- HORA -->
                <div class="form-group">
                    <label>Hora</label>
                    <div class="input-modern">
                        <i class="fas fa-clock"></i>
                        <input
                            type="time"
                            name="hora_cita"
                            required
                        >
                    </div>
                </div>
                <!-- ESTADO -->
                <div class="form-group">
                    <label>Estado</label>
                    <div class="input-modern">
                        <i class="fas fa-check-circle"></i>
                        <select name="estado">
                            <option value="Pendiente">
                                Pendiente
                            </option>
                            <option value="Completada">
                                Completada
                            </option>
                            <option value="Cancelada">
                                Cancelada
                            </option>
                        </select>
                    </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="form-group full">
                    <label>Observaciones</label>
                    <textarea
                        name="observaciones"
                        placeholder="Agregar observaciones médicas..."
                    ></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="actions">
                <a href="" class="btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Guardar Consulta
                </button>
            </div>
        </form>
</template>

<script>
</script>