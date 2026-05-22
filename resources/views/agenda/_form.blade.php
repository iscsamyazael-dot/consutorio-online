@csrf
@php
    $formId = $formId ?? 'agenda_form';
    $cancelAsButton = $cancelAsButton ?? false;
    $nombrePaciente = trim(optional($cita->paciente)->nombre . ' ' . optional($cita->paciente)->apellido_paterno . ' ' . optional($cita->paciente)->apellido_materno);
    $nombreMedico = optional($cita->medico)->name;
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $formId }}_paciente_nombre">Paciente</label>
        <input type="hidden" name="paciente_id" id="{{ $formId }}_paciente_id" value="{{ old('paciente_id', $cita->paciente_id) }}">
        <input type="text" name="paciente_nombre" id="{{ $formId }}_paciente_nombre"
            class="form-control @error('paciente_nombre') is-invalid @enderror @error('paciente_id') is-invalid @enderror"
            value="{{ old('paciente_nombre', $nombrePaciente) }}" list="{{ $formId }}_pacientes_list" placeholder="Escriba el nombre del paciente" autocomplete="off" required>
        <datalist id="{{ $formId }}_pacientes_list">
            @foreach ($pacientes as $paciente)
                <option value="{{ trim($paciente->nombre . ' ' . $paciente->apellido_paterno . ' ' . $paciente->apellido_materno) }}" data-id="{{ $paciente->id }}"></option>
            @endforeach
        </datalist>
        <small class="form-text text-muted">Puede elegir una sugerencia o escribir un paciente nuevo.</small>
        @error('paciente_nombre')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
        @error('paciente_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="{{ $formId }}_medico_nombre">Medico</label>
        <input type="hidden" name="user_id" id="{{ $formId }}_user_id" value="{{ old('user_id', $cita->user_id) }}">
        <input type="text" name="medico_nombre" id="{{ $formId }}_medico_nombre"
            class="form-control @error('medico_nombre') is-invalid @enderror @error('user_id') is-invalid @enderror"
            value="{{ old('medico_nombre', $nombreMedico) }}" list="{{ $formId }}_medicos_list" placeholder="Escriba el nombre del medico" autocomplete="off">
        <datalist id="{{ $formId }}_medicos_list">
            @foreach ($medicos as $medico)
                <option value="{{ $medico->name }}" data-id="{{ $medico->id }}"></option>
            @endforeach
        </datalist>
        <small class="form-text text-muted">Puede elegir una sugerencia, escribir un medico nuevo o dejarlo sin asignar.</small>
        @error('medico_nombre')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
        @error('user_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $formId }}_fecha">Fecha</label>
        <input type="date" name="fecha" id="{{ $formId }}_fecha" class="form-control @error('fecha') is-invalid @enderror"
            value="{{ old('fecha', optional($cita->fecha)->format('Y-m-d')) }}" required>
        @error('fecha')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $formId }}_hora_inicio">Hora de inicio</label>
        <input type="time" name="hora_inicio" id="{{ $formId }}_hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror"
            value="{{ old('hora_inicio', $cita->hora_inicio ? substr($cita->hora_inicio, 0, 5) : '') }}" required>
        @error('hora_inicio')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $formId }}_hora_fin">Hora de fin</label>
        <input type="time" name="hora_fin" id="{{ $formId }}_hora_fin" class="form-control @error('hora_fin') is-invalid @enderror"
            value="{{ old('hora_fin', $cita->hora_fin ? substr($cita->hora_fin, 0, 5) : '') }}">
        @error('hora_fin')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label for="{{ $formId }}_motivo">Motivo de la cita</label>
        <input type="text" name="motivo" id="{{ $formId }}_motivo" class="form-control @error('motivo') is-invalid @enderror"
            value="{{ old('motivo', $cita->motivo) }}" maxlength="255" required>
        @error('motivo')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $formId }}_estado">Estado</label>
        <select name="estado" id="{{ $formId }}_estado" class="form-control @error('estado') is-invalid @enderror" required>
            @foreach ($estados as $value => $label)
                <option value="{{ $value }}" @selected(old('estado', $cita->estado) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('estado')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label for="{{ $formId }}_notas">Notas</label>
        <textarea name="notas" id="{{ $formId }}_notas" rows="4" class="form-control @error('notas') is-invalid @enderror">{{ old('notas', $cita->notas) }}</textarea>
        @error('notas')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="text-right">
    @if ($cancelAsButton)
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" data-agenda-dismiss>
            <i class="fas fa-times"></i> Cancelar
        </button>
    @else
        <a href="{{ route('agenda.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancelar
        </a>
    @endif
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save"></i> Guardar
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pacienteInput = document.getElementById('{{ $formId }}_paciente_nombre');
        const pacienteId = document.getElementById('{{ $formId }}_paciente_id');
        const pacienteOptions = Array.from(document.querySelectorAll('#{{ $formId }}_pacientes_list option'));
        const medicoInput = document.getElementById('{{ $formId }}_medico_nombre');
        const medicoId = document.getElementById('{{ $formId }}_user_id');
        const medicoOptions = Array.from(document.querySelectorAll('#{{ $formId }}_medicos_list option'));

        if (!pacienteInput || !medicoInput) {
            return;
        }

        pacienteInput.addEventListener('input', function () {
            const match = pacienteOptions.find((option) => option.value === pacienteInput.value);
            pacienteId.value = match ? match.dataset.id : '';
        });

        medicoInput.addEventListener('input', function () {
            const match = medicoOptions.find((option) => option.value === medicoInput.value);
            medicoId.value = match ? match.dataset.id : '';
        });
    });
</script>
