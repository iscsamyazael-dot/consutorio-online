<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Examen TR {{ $ficha->folio }}</title>
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
    .alerta { color: #b91c1c; font-weight: bold; }
    .no-cortar { page-break-inside: avoid; }
    .footer { position: fixed; bottom: -30px; left: 0; right: 0; border-top: 1px solid #d9e2ec; padding-top: 4px; text-align: center; font-size: 8px; color: #94a3b8; }
    .firma { margin-top: 30px; width: 230px; text-align: center; font-size: 9.5px; }
    .firma-linea { border-top: 0.5px solid #94a3b8; margin-bottom: 4px; }
    ul { margin: 0; padding-left: 14px; }
    li { margin-bottom: 2px; }
</style>
</head>
<body>

@php
    use Carbon\Carbon;

    $h1 = $datos['hoja1'] ?? [];
    $h3 = $datos['hoja3'] ?? [];
    $h4 = $datos['hoja4'] ?? [];
    $audio = $ficha->audiometria ?? [];
    $frec = $audio['frecuencias'] ?? [];
    $frecuencias = ['125', '250', '500', '1000', '2000', '3000', '4000', '6000', '8000'];
    $aptitud = $ficha->aptitud ?? '';

    $mapaEtiquetas = [
        'migrana' => 'Migraña',
        'oidos' => 'Oídos',
        'neurologico' => 'Neurológico',
        'trauma_acustico' => 'Trauma acústico',
        'tinnitus_acufeno' => 'Tinnitus (acúfeno)',
        'trastorno_sueno_vigilia' => 'Trastorno sueño-vigilia',
        'ansiedad_generalizada' => 'Ansiedad generalizada',
        'cardiovascular' => 'Cardiovascular',
        'genitourinario' => 'Genitourinario',
        'osteomuscular' => 'Osteomuscular',
    ];
    $etiqueta = fn ($k) => $mapaEtiquetas[(string) $k] ?? ucfirst(str_replace('_', ' ', (string) $k));

    $dato = fn ($arr, $clave) => (isset($arr[$clave]) && $arr[$clave] !== '') ? $arr[$clave] : '—';

    $fecha = function ($v) {
        if (empty($v)) return '—';
        try {
            return Carbon::parse($v)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $v;
        }
    };

    // Convierte valores anidados en texto legible: true = "Sí", claves = etiquetas
    $formato = function ($v) use (&$formato, $etiqueta) {
        if ($v === null || $v === '' || $v === false) return null;
        if ($v === true) return 'Sí';
        if (!is_array($v)) return (string) $v;
        $partes = [];
        foreach ($v as $k => $x) {
            $t = $formato($x);
            if ($t === null) continue;
            if (is_int($k)) {
                $partes[] = $t;
            } elseif ($t === 'Sí') {
                $partes[] = $etiqueta($k);
            } else {
                $partes[] = $etiqueta($k) . ': ' . $t;
            }
        }
        return $partes ? implode(', ', $partes) : null;
    };

    $listaRiesgos = collect($ficha->riesgos ?? [])->map(fn ($r) => $etiqueta($r))->implode(', ');

    // Antecedentes: una fila por condición
    $filasAntecedentes = collect($ficha->antecedentes_salud ?? [])
        ->map(fn ($v, $k) => ['label' => $etiqueta($k), 'valor' => $formato($v)])
        ->filter(fn ($r) => $r['valor'] !== null)
        ->values()
        ->all();

    // Exploración física: signos vitales van aparte y "score" no se imprime
    $excluir = ['peso_kg', 'estatura_m', 'imc', 'presion_sistolica', 'presion_diastolica',
                'frecuencia_cardiaca', 'frecuencia_respiratoria', 'score', 'equilibrio'];
    $filasExploracion = collect($h3)
        ->except($excluir)
        ->map(function ($valor, $sistema) use ($etiqueta) {
            if (is_array($valor)) {
                $estado = $valor['estado'] ?? '—';
                $obs = $valor['observaciones'] ?? null;
            } else {
                $estado = $valor ?: '—';
                $obs = null;
            }
            return [
                'sistema' => $etiqueta($sistema),
                'estado' => (string) $estado,
                'obs' => $obs,
                'anormal' => stripos((string) $estado, 'anormal') !== false,
            ];
        })
        ->values()
        ->all();

    // Valoración del equilibrio: solo si el formulario la captura
    $equilibrio = $h3['equilibrio'] ?? ($h4['equilibrio'] ?? null);
    $textoEquilibrio = $formato($equilibrio);

    // Restricciones: texto libre capturado por el médico, una por línea
    $restricciones = $h4['restricciones'] ?? null;
    $restList = is_array($restricciones)
        ? array_values(array_filter($restricciones))
        : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $restricciones))));
    $pendienteRestr = $aptitud === 'Apto con restricciones' && empty($restList);

    // Evaluación visual en tabla
    $ev = $ficha->evaluacion_visual ?? [];
    $filasVision = [
        ['Cercana derecho', $ev['vision_cercana_der'] ?? '—'],
        ['Cercana izquierdo', $ev['vision_cercana_izq'] ?? '—'],
        ['Cercana ambos', $ev['vision_cercana_ao'] ?? '—'],
        ['Lejana derecho', $ev['vision_lejana_der'] ?? '—'],
        ['Lejana izquierdo', $ev['vision_lejana_izq'] ?? '—'],
        ['Lejana ambos', $ev['vision_lejana_ao'] ?? '—'],
    ];

    $proxima = $h4['proxima_evaluacion'] ?? null;

    $nombrePaciente = $ficha->paciente?->nombre ?? $dato($h1, 'nombre');
    $empresa = $ficha->empresa?->razon_social ?? $dato($h1, 'empresa');
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

<div class="title">Examen médico · Trabajo de alto riesgo</div>

{{-- IDENTIFICACIÓN Y EMPLEO --}}
<table class="box no-cortar">
    <tr>
        <td class="lbl">Paciente:</td>
        <td><strong>{{ $nombrePaciente }}</strong></td>
        <td class="lbl">Edad / Género:</td>
        <td>{{ $dato($h1, 'edad') }} años · {{ $dato($h1, 'genero') }}</td>
    </tr>
    <tr>
        <td class="lbl">Fecha de nacimiento:</td>
        <td>{{ $fecha($h1['fecha_nacimiento'] ?? null) }}</td>
        <td class="lbl">No. de empleado:</td>
        <td>{{ $dato($h1, 'numero_empleado') }}</td>
    </tr>
    <tr>
        <td class="lbl">Empresa:</td>
        <td>{{ $empresa }}</td>
        <td class="lbl">Puesto:</td>
        <td>{{ $dato($h1, 'puesto') }}</td>
    </tr>
    <tr>
        <td class="lbl">Antigüedad empresa:</td>
        <td>{{ $ficha->anos_empresa ?? '—' }} años · {{ $ficha->meses_empresa ?? '—' }} meses</td>
        <td class="lbl">Antigüedad puesto:</td>
        <td>{{ $ficha->anos_puesto ?? '—' }} años · {{ $ficha->meses_puesto ?? '—' }} meses</td>
    </tr>
    <tr>
        <td class="lbl">Riesgos evaluados:</td>
        <td colspan="3"><strong>{{ $listaRiesgos ?: '—' }}</strong></td>
    </tr>
</table>

{{-- ANTECEDENTES DE SALUD --}}
<div class="title">Antecedentes de salud</div>
<div class="box">
    <ul>
        @forelse($filasAntecedentes as $f)
            <li><strong>{{ $f['label'] }}:</strong> {{ $f['valor'] }}</li>
        @empty
            <li>Sin antecedentes registrados</li>
        @endforelse
    </ul>
</div>

{{-- EXPLORACIÓN FÍSICA --}}
<div class="title">Exploración física</div>
<table class="box no-cortar">
    <tr>
        <td class="lbl">Peso / Estatura:</td>
        <td>{{ $ficha->peso ?? '—' }} kg · {{ $ficha->estatura ?? '—' }} m · IMC {{ $ficha->imc ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Presión / F. cardiaca:</td>
        <td>{{ $ficha->presion_arterial ?? '—' }} mmHg · {{ $ficha->frecuencia_cardiaca ?? '—' }} lpm · F. respiratoria {{ $h3['frecuencia_respiratoria'] ?? '—' }} rpm</td>
    </tr>
</table>

<table class="tabla no-cortar">
    <thead>
        <tr>
            <th class="izq">Sistema</th>
            <th class="izq">Estado</th>
            <th class="izq">Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filasExploracion as $fila)
            <tr>
                <td class="izq"><strong>{{ $fila['sistema'] }}</strong></td>
                <td class="izq {{ $fila['anormal'] ? 'alerta' : '' }}">{{ $fila['estado'] }}</td>
                <td class="izq">{{ $fila['obs'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td class="izq" colspan="3">Sin hallazgos registrados</td></tr>
        @endforelse
    </tbody>
</table>

@if($textoEquilibrio)
    <div class="title">Valoración del equilibrio</div>
    <div class="box">{{ $textoEquilibrio }}</div>
@endif

{{-- PARACLÍNICOS --}}
<div class="no-cortar">
<div class="title">Paraclínicos</div>
<table class="box">
    <tr>
        <td class="lbl">ECG:</td>
        <td>{{ $ficha->ecg ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">RX de tórax:</td>
        <td>{{ $ficha->rx_torax ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Espirometría:</td>
        <td>{{ $formato($ficha->espirometria) ?? '—' }}</td>
    </tr>
</table>
</div>

<div class="title">Evaluación visual</div>
<table class="tabla no-cortar">
    <thead>
        <tr>
            <th class="izq">Agudeza visual</th>
            @foreach($filasVision as $v)
                <th>{{ $v[0] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="izq"><strong>Resultado</strong></td>
            <td colspan="6" class="izq">{{ $ev['resultado'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="izq"><strong>Valor</strong></td>
            @foreach($filasVision as $v)
                <td>{{ $v[1] }}</td>
            @endforeach
        </tr>
    </tbody>
</table>
@if(!empty($ev['reporte']))
    <div class="box" style="margin-top: 6px;">{{ $ev['reporte'] }}</div>
@endif

{{-- AUDIOMETRÍA --}}
<div class="title">Audiometría tonal (dB HL) · {{ $fecha($audio['fecha'] ?? null) }}</div>
<table class="tabla no-cortar">
    <thead>
        <tr>
            <th class="izq">Oído</th>
            @foreach($frecuencias as $f)
                <th>{{ $f }} Hz</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="izq"><strong>Derecho</strong></td>
            @foreach($frecuencias as $f)
                <td>{{ $frec['derecho'][$f] ?? '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <td class="izq"><strong>Izquierdo</strong></td>
            @foreach($frecuencias as $f)
                <td>{{ $frec['izquierdo'][$f] ?? '—' }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

<table class="box no-cortar" style="margin-top: 6px;">
    <tr>
        <td class="lbl">PTA derecho:</td>
        <td><strong>{{ $audio['pta_der'] ?? '—' }} dB HL</strong> · {{ $audio['clasificacion_der'] ?? '—' }}</td>
        <td class="lbl">PTA izquierdo:</td>
        <td><strong>{{ $audio['pta_izq'] ?? '—' }} dB HL</strong> · {{ $audio['clasificacion_izq'] ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Clasificación:</td>
        <td>{{ $audio['clasificacion'] ?? '—' }}</td>
        <td class="lbl">Tipo:</td>
        <td>{{ $audio['tipo_hipoacusia'] ?? '—' }}</td>
    </tr>
</table>

@if(!empty($grafica))
    <div class="no-cortar" style="margin-bottom: 18px;">
        <div class="title">Audiograma</div>
        <div style="text-align: center;">
            <img src="{{ $grafica }}" style="width: 460px;">
        </div>
    </div>
@endif

<div class="title">Interpretación audiológica</div>
<div class="box">{{ $audio['reporte'] ?? '—' }}</div>

{{-- DIAGNÓSTICOS --}}
<div class="title">Diagnósticos</div>
<div class="box">
    @forelse($ficha->diagnosticos ?? [] as $d)
        <div>{{ $d }}</div>
    @empty
        <div>—</div>
    @endforelse
</div>

{{-- OBSERVACIONES --}}
<div class="title">Observaciones</div>
<div class="box">{{ $ficha->observaciones ?? '—' }}</div>

{{-- DICTAMEN --}}
<div class="no-cortar">
    <div class="title">Dictamen</div>
    <table class="box">
        <tr>
            <td class="lbl">Aptitud:</td>
            <td>
                <span class="badge {{ $aptitud === 'Apto' ? 'badge-ok' : 'badge-alerta' }}">
                    {{ $aptitud ?: '—' }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="lbl">Restricciones:</td>
            <td>
                @if(!empty($restList))
                    <ul>
                        @foreach($restList as $r)
                            <li>{{ $r }}</li>
                        @endforeach
                    </ul>
                @elseif($pendienteRestr)
                    <span class="alerta">Pendiente: capturar restricciones antes de imprimir.</span>
                @else
                    No aplica
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Recomendaciones:</td>
            <td>
                @if(!empty($h4['recomendaciones']) && is_array($h4['recomendaciones']))
                    <ul>
                        @foreach($h4['recomendaciones'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                    </ul>
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Próxima evaluación:</td>
            <td>{{ $proxima ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Normas aplicables:</td>
            <td>NOM-009-STPS-2011 (trabajos en altura) · NOM-011-STPS-2001 (ruido) · NOM-033-STPS-2015 (espacios confinados)</td>
        </tr>
    </table>
</div>

{{-- FIRMA --}}
<div class="firma no-cortar">
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