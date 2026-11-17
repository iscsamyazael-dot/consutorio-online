<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ergonomía {{ $v->folio }}</title>
<style>
    @page { margin: 26px 36px 50px 36px; }
    body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 10px; color: #263238; }
    table { border-collapse: collapse; width: 100%; }
    .clinic { font-size: 14px; font-weight: bold; color: #087c8c; text-transform: uppercase; }
    .sub { font-size: 9px; color: #718096; margin-top: 2px; }
    .meta { text-align: right; font-size: 10px; }
    .title { font-size: 12.5px; font-weight: bold; color: #1a2737; border-bottom: 2px solid #087c8c; padding-bottom: 3px; margin: 12px 0 6px 0; }
    .box { border: 1px solid #d9e2ec; border-radius: 3px; padding: 6px 8px; margin-bottom: 8px; }
    .lbl { font-weight: bold; color: #667085; width: 130px; padding: 2px 0; }
    .tabla th, .tabla td { border-bottom: 1px solid #e2e8f0; padding: 3px 4px; text-align: right; }
    .tabla th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; color: #475569; }
    .tabla .izq { text-align: left; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 9.5px; }
    .badge-ok { background: #dcfce7; color: #166534; }
    .badge-alerta { background: #fef3c7; color: #92400e; }
    .footer { position: fixed; bottom: -30px; left: 0; right: 0; border-top: 1px solid #d9e2ec; padding-top: 4px; text-align: center; font-size: 8px; color: #94a3b8; }
    .firma { margin-top: 30px; width: 230px; text-align: center; font-size: 9.5px; }
    .firma-linea { border-top: 0.5px solid #94a3b8; margin-bottom: 4px; }
</style>
</head>
<body>

@php
    $n = fn ($x, $d = 2) => ($x === null || $x === '') ? '—' : number_format((float) $x, $d);
    $fecha = $v->fecha_valoracion ? \Carbon\Carbon::parse($v->fecha_valoracion)->format('d/m/Y') : '—';
    $claseAptitud = $v->aptitud === 'Apto' ? 'badge-ok' : 'badge-alerta';
@endphp

{{-- ENCABEZADO --}}
<table>
    <tr>
        <td style="width: 80px;">
            @if(!empty($logoPath))
                <img src="{{ $logoPath }}" width="70" alt="Logo">
            @endif
        </td>
        <td>
            <div class="clinic">{{ $ubicacion->nombre ?? 'Consultorio' }}</div>
            <div class="sub">{{ $ubicacion->direccion ?? '' }}</div>
            <div class="sub">Tel: {{ $ubicacion->telefono ?? '' }}</div>
        </td>
        <td class="meta">
            <div><strong>Folio:</strong> {{ $v->folio }}</div>
            <div><strong>Fecha:</strong> {{ $fecha }}</div>
            <div><strong>Hora:</strong> {{ $v->hora_valoracion ? substr($v->hora_valoracion, 0, 5) : '—' }}</div>
            <div><strong>Tipo:</strong> {{ ucfirst($v->tipo_evaluacion ?? '—') }}</div>
        </td>
    </tr>
</table>

<div class="title">Valoración ergonómica ocupacional</div>

{{-- APTITUD Y RESTRICCIONES (primero, lo que la empresa necesita) --}}
<div class="title">Aptitud laboral</div>
<table class="box">
    <tr>
        <td class="lbl">Aptitud:</td>
        <td><span class="badge {{ $claseAptitud }}">{{ $v->aptitud ?? '—' }}</span></td>
    </tr>
    <tr>
        <td class="lbl">Restricciones:</td>
        <td>
            {{ $v->restricciones_laborales ?: '—' }}
            @if($v->restricciones_descripcion)
                <br>{{ $v->restricciones_descripcion }}
            @endif
        </td>
    </tr>
    <tr>
        <td class="lbl">Recomendaciones para el puesto:</td>
        <td>{{ $v->recomendaciones_puesto ?: '—' }}</td>
    </tr>
</table>

{{-- IDENTIFICACIÓN --}}
<div class="title">Datos del trabajador y del puesto</div>
<table class="box">
    <tr>
        <td class="lbl">Trabajador:</td>
        <td><strong>{{ $v->paciente->nombre ?? '—' }}</strong></td>
        <td class="lbl">Edad / Sexo:</td>
        <td>{{ $v->paciente->edad_formateada ?? '—' }} · {{ $v->paciente->genero ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Empresa:</td>
        <td>{{ $v->empresa->razon_social ?? '—' }}</td>
        <td class="lbl">Puesto:</td>
        <td>{{ $v->puesto->nombre ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Departamento:</td>
        <td>{{ $v->puesto->departamento ?? '—' }}</td>
        <td class="lbl">Médico responsable:</td>
        <td>{{ $medico->name ?? '—' }}@if(!empty($cedula)) · Céd. Prof. {{ $cedula }}@endif</td>
    </tr>
</table>

{{-- DIAGNÓSTICO Y EXAMEN MÉDICO --}}
<div class="title">Diagnóstico y examen médico</div>
<table class="box">
    <tr>
        <td class="lbl">Exploración osteomuscular:</td>
        <td>{{ $v->exploracion_osteomuscular_hallazgos ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Diagnóstico:</td>
        <td>{{ $v->diagnostico_relacionado ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Acciones recomendadas:</td>
        <td>{{ $v->acciones_recomendadas ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Derivación:</td>
        <td>{{ $v->derivacion_especialista ?: 'No aplica' }}</td>
    </tr>
</table>

{{-- HALLAZGOS POR REGIÓN (solo los que marcó el médico) --}}
@php
    $regiones = collect($datos['examenMedico']['hallazgos'] ?? [])
        ->filter(fn ($marcada) => $marcada === true)
        ->keys()
        ->map(fn ($k) => ucfirst(str_replace('_', ' ', $k)))
        ->implode(', ');
@endphp
<table class="box">
    <tr>
        <td class="lbl">Regiones con hallazgo:</td>
        <td>{{ $regiones !== '' ? $regiones : 'Sin hallazgos marcados' }}</td>
    </tr>
</table>

{{-- INSTRUMENTO UTILIZADO --}}
<div class="title">Evaluación con instrumento: {{ $v->instrumento_utilizado ?? '—' }}</div>
<table class="box">
    @if($v->instrumento_utilizado === 'RULA')
        <tr>
            <td class="lbl">Puntuación RULA:</td>
            <td><strong>{{ $n($v->rula_puntuacion, 0) }}</strong> · Nivel: {{ str_replace('_', ' ', $v->rula_nivel_riesgo ?? '—') }}</td>
        </tr>
        <tr>
            <td class="lbl">Áreas evaluadas:</td>
            <td>{{ $v->rula_areas_evaluadas ?: '—' }}</td>
        </tr>
    @elseif($v->instrumento_utilizado === 'REBA')
        <tr>
            <td class="lbl">Puntuación REBA:</td>
            <td><strong>{{ $n($v->reba_puntuacion, 0) }}</strong> · Acción: {{ str_replace('_', ' ', $v->reba_nivel_accion ?? '—') }}</td>
        </tr>
        <tr>
            <td class="lbl">Áreas evaluadas:</td>
            <td>{{ $v->reba_areas_evaluadas ?: '—' }}</td>
        </tr>
    @elseif($v->instrumento_utilizado === 'NIOSH')
        <tr>
            <td class="lbl">Índice de levantamiento:</td>
            <td>
                <strong>{{ $n($v->niosh_indice_levantamiento, 2) }}</strong>
                @if($v->niosh_indice_levantamiento !== null)
                    · {{ $v->niosh_es_seguro ? 'Seguro' : 'No seguro' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Peso real / recomendado:</td>
            <td>{{ $n($v->niosh_peso_real_kg, 2) }} kg / {{ $n($v->niosh_peso_recomendado_kg, 2) }} kg</td>
        </tr>
        <tr>
            <td class="lbl">Variables evaluadas:</td>
            <td>{{ $v->niosh_variables_evaluadas ?: '—' }}</td>
        </tr>
    @elseif($v->instrumento_utilizado === 'OTRO')
        <tr>
            <td class="lbl">Instrumento:</td>
            <td>{{ $v->otro_descripcion ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Puntuación:</td>
            <td>{{ $n($v->otro_puntuacion, 0) }}</td>
        </tr>
    @endif
    <tr>
        <td class="lbl">Justificación del instrumento:</td>
        <td>{{ $v->justificacion_instrumento ?: '—' }}</td>
    </tr>
</table>

{{-- SEGUIMIENTO Y OBSERVACIONES --}}
<div class="title">Seguimiento y observaciones</div>
<table class="box">
    <tr>
        <td class="lbl">Requiere seguimiento:</td>
        <td>{{ $v->seguimiento_requerido ? 'Sí' : 'No' }}</td>
    </tr>
    @if($v->seguimiento_requerido)
        <tr>
            <td class="lbl">Tipo de seguimiento:</td>
            <td>{{ $datos['examenMedico']['tipo_seguimiento'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Próxima valoración:</td>
            <td>{{ $v->plazo_proximo_seguimiento ? \Carbon\Carbon::parse($v->plazo_proximo_seguimiento)->format('d/m/Y') : '—' }}</td>
        </tr>
    @endif
    <tr>
        <td class="lbl">Observaciones del médico:</td>
        <td>{{ $v->notas_ergonomo ?: '—' }}</td>
    </tr>
</table>

{{-- FIRMA --}}
<div class="firma">
    <div class="firma-linea"></div>
    <div style="font-weight: bold;">{{ $medico->name ?? '' }}</div>
    @if(!empty($cedula))
        <div style="font-size: 8.5px; color: #6b7280;">Céd. Prof. {{ $cedula }}</div>
    @endif
    <div style="font-size: 8.5px; color: #6b7280;">Médico responsable</div>
</div>

<div class="footer">
    {{ $ubicacion->nombre ?? '' }} · {{ $ubicacion->direccion ?? '' }} · Tel: {{ $ubicacion->telefono ?? '' }}<br>
    Documento generado como apoyo clínico. No sustituye el criterio ni la firma del médico tratante.
</div>

</body>
</html>