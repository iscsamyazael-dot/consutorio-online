<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ocupacional {{ $v->folio }}</title>
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
    $claseDictamen = $v->dictamen === 'apto' ? 'badge-ok' : 'badge-alerta';
    $textoDictamen = ucfirst(str_replace('_', ' ', $v->dictamen ?? '—'));
    $proxima = $v->vigencia_hasta ? \Carbon\Carbon::parse($v->vigencia_hasta)->format('d/m/Y') : 'Sin definir';
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
            <div><strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' ', $v->tipo ?? '—')) }}</div>
        </td>
    </tr>
</table>

<div class="title">Valoración de medicina del trabajo</div>

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
        <td>{{ $v->empresaCliente->razon_social ?? '—' }}</td>
        <td class="lbl">Puesto:</td>
        <td>{{ $v->puestoTrabajo->nombre ?? $v->puesto_nombre_snapshot ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Departamento:</td>
        <td>{{ $v->puestoTrabajo->departamento ?? '—' }}</td>
        <td class="lbl">Médico responsable:</td>
        <td>{{ $medico->name ?? '—' }}@if(!empty($cedula)) · Céd. Prof. {{ $cedula }}@endif</td>
    </tr>
</table>

{{-- SIGNOS VITALES --}}
<div class="title">Signos vitales y somatometría</div>
<table class="box">
    <tr>
        <td class="lbl">Presión arterial:</td>
        <td>{{ $v->ta_sistolica ?? '—' }}/{{ $v->ta_diastolica ?? '—' }} mmHg</td>
        <td class="lbl">FC / FR / SpO2:</td>
        <td>{{ $v->fc ?? '—' }} lpm · {{ $v->fr ?? '—' }} rpm · {{ $v->spo2 ?? '—' }}%</td>
    </tr>
    <tr>
        <td class="lbl">Temperatura:</td>
        <td>{{ $v->temperatura_c ?? '—' }} °C</td>
        <td class="lbl">Peso / Talla:</td>
        <td>{{ $v->peso_kg ?? '—' }} kg · {{ $v->estatura_cm ?? '—' }} cm</td>
    </tr>
    <tr>
        <td class="lbl">IMC:</td>
        <td colspan="3">{{ $v->imc ?? '—' }} {{ $v->imc_clasificacion ? '· ' . $v->imc_clasificacion : '' }}</td>
    </tr>
</table>

{{-- APTITUD Y RESTRICCIONES (primero, lo que la empresa necesita) --}}
<div class="title">Aptitud laboral</div>
<table class="box">
    <tr>
        <td class="lbl">Dictamen:</td>
        <td><span class="badge {{ $claseDictamen }}">{{ $textoDictamen }}</span></td>
    </tr>
    <tr>
        <td class="lbl">Restricciones:</td>
        <td>{{ $v->restricciones ?: 'Ninguna' }}</td>
    </tr>
    <tr>
        <td class="lbl">Recomendaciones:</td>
        <td>{{ $v->recomendaciones ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Próxima valoración:</td>
        <td>{{ $proxima }}</td>
    </tr>
</table>

{{-- RIESGOS DEL PUESTO (capturados en la valoración) --}}
<div class="title">Riesgos del puesto</div>
<table class="box">
    <tr>
        <td class="lbl">Agentes de exposición:</td>
        <td>{{ !empty($riesgos['agentes']) ? implode(', ', $riesgos['agentes']) : 'Ninguno' }}{{ !empty($riesgos['agentes_otros']) ? '. Otros: ' . $riesgos['agentes_otros'] : '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Condiciones de riesgo:</td>
        <td>{{ !empty($riesgos['condiciones']) ? implode(', ', $riesgos['condiciones']) : 'Ninguna' }}</td>
    </tr>
    <tr>
        <td class="lbl">Postura habitual:</td>
        <td>{{ !empty($riesgos['postura']) ? ucfirst(str_replace('_', ' ', $riesgos['postura'])) : '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Exigencias del puesto:</td>
        <td>{{ !empty($riesgos['exigencias']) ? implode(', ', $riesgos['exigencias']) : 'Ninguna' }}</td>
    </tr>
</table>
@if ($version === 'completo')

{{-- DIAGNÓSTICO Y HALLAZGOS --}}
<div class="title">Diagnóstico y hallazgos</div>
<table class="box">
    <tr>
        <td class="lbl">Diagnóstico clínico:</td>
        <td>{{ $v->diagnostico_clinico ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">CIE-11:</td>
        <td>{{ $v->cie11_codigo ? $v->cie11_codigo . ' — ' . $v->cie11_titulo : '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Hallazgos relevantes:</td>
        <td>{{ $v->analisis_correlacion_riesgos ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Derivación:</td>
        <td>{{ $v->derivaciones_especialista ?: 'No aplica' }}</td>
    </tr>
    <tr>
        <td class="lbl">Conclusión médica:</td>
        <td>{{ $v->dictamen_justificacion ?: '—' }}</td>
    </tr>
</table>

{{-- SEGUIMIENTO Y PLAN --}}
<div class="title">Seguimiento y plan</div>
<table class="box">
    <tr>
        <td class="lbl">Próxima valoración:</td>
        <td>{{ $proxima }}</td>
    </tr>
    <tr>
        <td class="lbl">Plan de intervención:</td>
        <td>{{ $v->plan_intervencion ?: '—' }}</td>
    </tr>
</table>

{{-- OBSERVACIONES --}}
<div class="title">Observaciones</div>
<table class="box">
    <tr>
        <td class="lbl">Observaciones del médico:</td>
        <td>{{ $v->observaciones ?: '—' }}</td>
    </tr>
</table>

@endif

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