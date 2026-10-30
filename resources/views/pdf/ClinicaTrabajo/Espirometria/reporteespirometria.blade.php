<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Espirometría {{ $v->folio }}</title>
<style>
    @page { margin: 26px 36px 50px 36px; }
    body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 10px; color: #263238; }
    table { border-collapse: collapse; width: 100%; }
    .clinic { font-size: 14px; font-weight: bold; color: #087c8c; text-transform: uppercase; }
    .sub { font-size: 9px; color: #718096; margin-top: 2px; }
    .meta { text-align: right; font-size: 10px; }
    .title { font-size: 12.5px; font-weight: bold; color: #1a2737; border-bottom: 2px solid #087c8c; padding-bottom: 3px; margin: 12px 0 6px 0; }
    .box { border: 1px solid #d9e2ec; border-radius: 3px; padding: 6px 8px; margin-bottom: 8px; }
    .lbl { font-weight: bold; color: #667085; width: 110px; padding: 2px 0; }
    .tabla th, .tabla td { border-bottom: 1px solid #e2e8f0; padding: 3px 4px; text-align: right; }
    .tabla th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; color: #475569; }
    .tabla .izq { text-align: left; }
    .marcada { background: #fde68a; }
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
    $imc = ($v->peso_kg && $v->altura_cm) ? $v->peso_kg / pow($v->altura_cm / 100, 2) : null;
    $paqAnios = ($v->cigarrillos_dia && $v->anos_fumador) ? round($v->cigarrillos_dia * $v->anos_fumador / 20, 1) : null;
    $apto = $v->aptitud;
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
            <div><strong>Fecha:</strong> {{ optional($v->fecha_valoracion)->format('d/m/Y') ?? $v->fecha_valoracion }}</div>
            <div><strong>Prueba:</strong> {{ $v->fecha_prueba ? \Carbon\Carbon::parse($v->fecha_prueba)->format('d/m/Y') : '—' }}</div>
            <div><strong>Tipo:</strong> {{ ucfirst($v->tipo_evaluacion) }}</div>
        </td>
    </tr>
</table>

<div class="title">Resultados de la prueba de función pulmonar</div>

{{-- DATOS DEL PACIENTE --}}
<table class="box">
    <tr>
        <td class="lbl">Paciente:</td>
        <td><strong>{{ $v->nombre }}</strong></td>
        <td class="lbl">Edad / Sexo:</td>
        <td>{{ $v->paciente->edad_formateada ?? '' }} · {{ $v->sexo }}</td>
    </tr>
    <tr>
        <td class="lbl">Altura / Peso:</td>
        <td>{{ $n($v->altura_cm, 0) }} cm · {{ $n($v->peso_kg, 1) }} kg · IMC {{ $n($imc, 1) }}</td>
        <td class="lbl">Tabaquismo:</td>
        @php
            $tabaco = $v->fumador ? 'Fumador' : (($v->cigarrillos_dia || $v->anos_fumador) ? 'Ex fumador' : 'No fumador');
        @endphp
        <td>
            {{ $tabaco }}
            @if($v->cigarrillos_dia) · {{ $v->cigarrillos_dia }} cig/día @endif
            @if($v->anos_fumador) · {{ $v->anos_fumador }} años @endif
            @if($paqAnios !== null) · {{ $paqAnios }} paquete-año @endif
        </td>
    </tr>
    <tr>
        <td class="lbl">Empresa:</td>
        <td>{{ $v->empresa }}</td>
        <td class="lbl">Ecuación:</td>
        <td>{{ $v->referencia }} · {{ $v->interpretacion_predicha }}</td>
    </tr>
    <tr>
        <td class="lbl">Remitido por:</td>
        <td>{{ $v->remitido_por ?: '—' }}</td>
        <td class="lbl">Prescrito por:</td>
        <td>{{ $v->prescrito_por ?: '—' }}</td>
    </tr>
</table>

{{-- TABLA DE PARÁMETROS --}}
<div class="title">Parámetros espirométricos</div>
<table class="tabla">
    <thead>
        <tr>
            <th class="izq">Parámetro</th>
            <th>Unidad</th>
            <th>LLN</th>
            <th>Teór.</th>
            @if($hayZ)
                <th>Z</th>
            @endif
            <th>Mejor</th>
            <th>%Teór.</th>
            @foreach($maniobras as $m)
                <th>Prueba {{ $m['numero'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($filas as $f)
            <tr>
                <td class="izq"><strong>{{ $f['label'] }}</strong></td>
                <td>{{ $f['unidad'] }}</td>
                <td>{{ $n($f['lln'], $f['d']) }}</td>
                <td>{{ $n($f['teor'], $f['d']) }}</td>
                @if($hayZ)
                    <td>{{ $n($f['z'], 2) }}</td>
                @endif
                <td><strong>{{ $n($f['mejor'], $f['d']) }}</strong></td>
                <td>{{ $n($f['pct'], 0) }}</td>
                @foreach($maniobras as $m)
                    @php $celda = $f['pruebas'][$m['numero']] ?? ['valor' => null, 'marcada' => false]; @endphp
                    <td class="{{ $celda['marcada'] ? 'marcada' : '' }}">{{ $n($celda['valor'], $f['d']) }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
<div style="font-size: 8.5px; color: #64748b; margin-top: 4px;">
    * Valor marcado por el equipo (fuera del rango normal o cambio significativo entre pruebas).
</div>

@if(!empty($graficas['flujo']) || !empty($graficas['tiempo']))
    <div class="title">Gráficas espirométricas</div>
    <table>
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                @if(!empty($graficas['flujo']))
                    <div style="font-size: 9px; font-weight: bold; color: #475569;">Curva flujo / volumen</div>
                    <img src="{{ $graficas['flujo'] }}" style="width: 100%;">
                @endif
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                @if(!empty($graficas['tiempo']))
                    <div style="font-size: 9px; font-weight: bold; color: #475569;">Curva volumen / tiempo</div>
                    <img src="{{ $graficas['tiempo'] }}" style="width: 100%;">
                @endif
            </td>
        </tr>
    </table>
    <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
        Curvas generadas a partir de la tabla; la forma es aproximada y los valores son los del informe.
    </div>
@endif

@if(!empty($graficas['z']))
    <div style="page-break-inside: avoid;">
        <div class="title">Puntuación Z por parámetro</div>
        <img src="{{ $graficas['z'] }}" style="width: 420px;">
    </div>
@endif

{{-- CALIDAD E INTERPRETACIÓN --}}
<div class="title">Calidad e interpretación</div>
<table class="box">
    <tr>
        <td class="lbl">Calidad de sesión:</td>
        <td><strong>{{ $v->calidad_sesion ?? '—' }}</strong> · FEV1 {{ $v->calidad_fev1 ?? '—' }} · FVC {{ $v->calidad_fvc ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Variabilidad:</td>
        <td>FEV1 {{ $n($v->fev1_var_l, 2) }} L · FVC {{ $n($v->fvc_var_l, 2) }} L (criterio ATS/ERS: ≤ 0.150 L para grado A)</td>
    </tr>
    <tr>
        <td class="lbl">Interpretación:</td>
        <td>
            <span class="badge {{ $v->interpretacion_sistema === 'Espirometría Normal' ? 'badge-ok' : 'badge-alerta' }}">
                {{ $v->interpretacion_sistema ?? '—' }}
            </span>
        </td>
    </tr>
    @if($v->interpretacion_equipo)
    <tr>
        <td class="lbl">Texto del equipo:</td>
        <td>{{ $v->interpretacion_equipo }}</td>
    </tr>
    @endif
</table>

{{-- DICTAMEN --}}
<div class="title">Diagnóstico y dictamen</div>
<table class="box">
    <tr>
        <td class="lbl">Diagnóstico:</td>
        <td>{{ $v->diagnostico_medico ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Recomendaciones:</td>
        <td>{{ $v->recomendaciones ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Aptitud:</td>
        <td><strong>{{ $apto ?? '—' }}</strong></td>
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