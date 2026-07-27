<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"> 
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin-top: 18px; margin-bottom: 4px; color: #1d4ed8; border-bottom: 1px solid #e5e9f0; padding-bottom: 3px; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 14px; }
        .meta span { margin-right: 14px; }
        p { line-height: 1.5; margin: 0 0 6px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-final { background: #e8f6ee; color: #146c43; }
        .badge-borrador { background: #fff8e6; color: #92700f; }
    </style>
</head>
<body>
    <!-- //27072026 -->
    <h1>Nota de Evolución Clínica (PSOAPP)</h1> 
    <div class="meta">
        <span><strong>Folio:</strong> {{ $consulta->folio }}</span>
        <span><strong>Paciente:</strong> {{ $consulta->paciente->nombre ?? 'N/D' }}</span>
        <span><strong>Fecha:</strong> {{ $consulta->created_at->format('d/m/Y H:i') }}</span>
        @if($nota)
            <span class="badge {{ $nota->estado === 'final' ? 'badge-final' : 'badge-borrador' }}">
                {{ $nota->estado === 'final' ? 'NOTA FINAL' : 'BORRADOR' }}
            </span>
        @endif
    </div>
    @if($evaluacion)
        <h2>Diagnóstico probable</h2>
        <p>{{ $evaluacion->diagnostico_probable }}</p>
    @endif
    @if($nota)
        <h2>Presentación</h2>
        <p>{{ $nota->presentacion ?: 'No disponible' }}</p>
        <h2>Subjetivo</h2>
        <p>{{ $nota->subjetivo ?: 'No disponible' }}</p>
        <h2>Objetivo</h2>
        <p>{{ $nota->objetivo ?: 'No disponible' }}</p>
        <h2>Análisis</h2>
        <p>{{ $nota->analisis ?: 'No disponible' }}</p>
        <h2>Plan</h2>
        <p>{{ $nota->plan ?: 'No disponible' }}</p>
        <h2>Pronóstico</h2>
        <p>{{ $nota->pronostico ?: 'No disponible' }}</p>
    @else
        <p><em>Esta consulta todavía no tiene una nota PSOAPP generada.</em></p>
    @endif
    <p style="margin-top:24px; font-size:10px; color:#9ca3af;">
        Documento generado como apoyo clínico. No sustituye el criterio ni la firma del médico tratante.
    </p>
</body>
</html>