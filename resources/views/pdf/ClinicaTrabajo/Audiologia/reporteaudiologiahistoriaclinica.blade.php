<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Audiometría {{ $ficha->folio }}</title>
<style>
    @page { margin: 26px 36px 50px 36px; }
    body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 10px; color: #263238; }
    table { border-collapse: collapse; width: 100%; }
    .clinic { font-size: 14px; font-weight: bold; color: #087c8c; text-transform: uppercase; }
    .sub { font-size: 9px; color: #718096; margin-top: 2px; }
    .meta { text-align: right; font-size: 10px; }
    .title { font-size: 12.5px; font-weight: bold; color: #1a2737; border-bottom: 2px solid #087c8c; padding-bottom: 3px; margin: 12px 0 6px 0; }
    .box { border: 1px solid #d9e2ec; border-radius: 3px; padding: 6px 8px; margin-bottom: 8px; }
    .lbl { font-weight: bold; color: #667085; width: 120px; padding: 2px 0; }
    .tabla th, .tabla td { border-bottom: 1px solid #e2e8f0; padding: 3px 4px; text-align: center; }
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
    // $datos = datos_json decodificado. Claves verificadas contra el JSON de la ficha 1.
    $h1 = $datos['hoja1'] ?? [];
    $h4 = $datos['hoja4'] ?? [];
    $audio = $ficha->audiometria ?? [];

    $dato = fn ($arr, $clave) => (isset($arr[$clave]) && $arr[$clave] !== '') ? $arr[$clave] : '—';

    // Lista las opciones marcadas (true, 1 o "Sí") como texto legible
    $marcados = fn ($arr) => collect($arr ?? [])
        ->filter(fn ($x) => in_array($x, [true, 1, '1', 'Sí', 'si'], true))
        ->keys()
        ->map(fn ($k) => ucfirst(str_replace('_', ' ', $k)))
        ->implode(', ');

    $ototoxicos = implode(', ', $ficha->ototoxicos_quimicos ?? []);

    $tipoHipoacusia = $h4['tipo_hipoacusia'] ?? null;
    if (empty($tipoHipoacusia)) {
        $tipoHipoacusia = 'No determinado (sin vía ósea suficiente)';
    }

    $colsDer = collect($columnas)->where('oido', 'derecho')->count();
    $colsIzq = collect($columnas)->where('oido', 'izquierdo')->count();
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
            <div><strong>Folio:</strong> {{ $ficha->folio }}</div>
            <div><strong>Fecha:</strong> {{ $ficha->created_at?->format('d/m/Y') ?? '—' }}</div>
        </td>
    </tr>
</table>

<div class="title">Audiometría tonal · Historia clínica audiológica</div>

{{-- DATOS DEL PACIENTE --}}
<table class="box">
    <tr>
        <td class="lbl">Paciente:</td>
        <td><strong>{{ $dato($h1, 'nombre') }}</strong></td>
        <td class="lbl">Edad / Sexo:</td>
        <td>{{ $dato($h1, 'edad') }} años · {{ $dato($h1, 'genero') }}</td>
    </tr>
    <tr>
        <td class="lbl">Fecha de nacimiento:</td>
        <td>{{ $dato($h1, 'fecha_nacimiento') }}</td>
        <td class="lbl">No. de empleado:</td>
        <td>{{ $dato($h1, 'numero_empleado') }}</td>
    </tr>
    <tr>
        <td class="lbl">Empresa:</td>
        <td>{{ $dato($h1, 'empresa_actual') }}</td>
        <td class="lbl">Puesto:</td>
        <td>{{ $dato($h1, 'puesto') }}</td>
    </tr>
    <tr>
        <td class="lbl">Antigüedad empresa:</td>
        <td>{{ $ficha->anos_empresa ?? '—' }} años · {{ $ficha->meses_empresa ?? '—' }} meses</td>
        <td class="lbl">Antigüedad puesto:</td>
        <td>{{ $ficha->anos_puesto ?? '—' }} años · {{ $ficha->meses_puesto ?? '—' }} meses</td>
    </tr>
</table>

{{-- EXPOSICIÓN Y ANTECEDENTES --}}
<div class="title">Exposición a ruido, ototóxicos y antecedentes</div>
<table class="box">
    <tr>
        <td class="lbl">Riesgo auditivo:</td>
        <td><strong>{{ $ficha->riesgo_auditivo ?? '—' }}</strong></td>
        <td class="lbl">EPP auditivo:</td>
        <td>{{ $ficha->epp_auditivo ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Ototóxicos:</td>
        <td>{{ $ototoxicos !== '' ? $ototoxicos : '—' }}</td>
        <td class="lbl">Antecedentes:</td>
        <td>{{ $marcados($ficha->antecedentes_medicos) ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Síntomas auditivos:</td>
        <td colspan="3">{{ $marcados($ficha->sintomas_auditivos) ?: '—' }}</td>
    </tr>
</table>

{{-- TABLA DE UMBRALES: las columnas de ósea y enmascarado vienen solo si hay datos --}}
<div class="title">Umbrales audiométricos (dB HL)</div>
<table class="tabla">
    <thead>
        <tr>
            <th class="izq">Frecuencia</th>
            @foreach($columnas as $c)
                <th>{{ $c['titulo'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($tabla as $f)
            <tr>
                <td class="izq"><strong>{{ $f['frecuencia'] }} Hz</strong></td>
                @foreach($columnas as $c)
                    <td>{{ $f['valores'][$c['clave']] ?? '—' }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td class="izq"><strong>PTA (500–3000 Hz)</strong></td>
            <td colspan="{{ $colsDer }}"><strong>{{ $pta['derecho'] ?? '—' }}</strong></td>
            <td colspan="{{ $colsIzq }}"><strong>{{ $pta['izquierdo'] ?? '—' }}</strong></td>
        </tr>
    </tbody>
</table>

{{-- AUDIOGRAMA --}}
@if(!empty($graficas['audiograma']))
    <div style="page-break-inside: avoid; margin-bottom: 18px;">
        <div class="title">Audiograma</div>
        <div style="text-align: center;">
            <img src="{{ $graficas['audiograma'] }}" style="width: 460px;">
        </div>
    </div>
@endif

{{-- RESUMEN --}}
<div class="title">Resumen</div>
<table class="box">
    <tr>
        <td class="lbl">PTA general:</td>
        <td><strong>{{ $ficha->pta_promedio ?? '—' }} dB HL</strong> (peor oído)</td>
    </tr>
    <tr>
        <td class="lbl">Clasificación general:</td>
        <td>{{ $ficha->clasificacion ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Oído derecho:</td>
        <td>{{ $audio['pta_der'] ?? '—' }} dB HL · {{ $audio['clasificacion_der'] ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Oído izquierdo:</td>
        <td>{{ $audio['pta_izq'] ?? '—' }} dB HL · {{ $audio['clasificacion_izq'] ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Tipo de hipoacusia:</td>
        <td>{{ $tipoHipoacusia }}</td>
    </tr>
</table>

{{-- INTERPRETACIÓN --}}
<div class="title">Interpretación</div>
<table class="box">
    <tr>
        <td>{{ $ficha->observaciones ?? '—' }}</td>
    </tr>
</table>

{{-- VALORACIÓN DEL MÉDICO --}}
<div class="title">Valoración</div>
<table class="box">
    <tr>
        <td>{{ $dato($h4, 'valoracion') }}</td>
    </tr>
</table>

{{-- DICTAMEN --}}
<div class="title">Dictamen</div>
<table class="box">
    <tr>
        <td class="lbl">Aptitud:</td>
        <td>
            <span class="badge {{ $ficha->aptitud === 'Apto' ? 'badge-ok' : 'badge-alerta' }}">
                {{ $ficha->aptitud ?? '—' }}
            </span>
        </td>
    </tr>
    <tr>
        <td class="lbl">Restricciones:</td>
        <td>{{ $dato($h4, 'restricciones') }}</td>
    </tr>
    <tr>
        <td class="lbl">Recomendaciones:</td>
        <td>
            @if(!empty($h4['recomendaciones']) && is_array($h4['recomendaciones']))
                <ul style="margin: 0; padding-left: 14px;">
                    @foreach($h4['recomendaciones'] as $rec)
                        <li>{{ $rec }}</li>
                    @endforeach
                </ul>
            @else
                —
            @endif
        </td>
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