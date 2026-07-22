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
        .aviso { background: #fff8e6; border: 1px solid #f2d382; padding: 8px 10px; border-radius: 6px; font-size: 11px; color: #92700f; }
    </style>
</head>
<body>
    <h1>Receta</h1>
    <div class="meta">
        <span><strong>Folio:</strong> {{ $consulta->folio }}</span>
        <span><strong>Paciente:</strong> {{ $consulta->paciente->nombre ?? 'N/D' }}</span>
        <span><strong>Fecha:</strong> {{ $consulta->created_at->format('d/m/Y H:i') }}</span>
    </div>
    @if($evaluacion)
        <h2>Diagnóstico probable</h2>
        <p>{{ $evaluacion->diagnostico_probable }}</p>
        <h2>Recomendación</h2>
        <p>{{ $evaluacion->recomendacion }}</p>
    @endif
    <h2>Medicamentos</h2>
    <div class="aviso">
        Este PDF todavía no incluye la lista de medicamentos de Receta Inteligente
        (ese resultado vive en RecetaInteligente.vue / recetaInteligente()
        del controller, y aún no está conectado a este flujo de descarga).
        Pendiente de integrar.
    </div>
    <p style="margin-top:24px; font-size:10px; color:#9ca3af;">
        Documento generado como apoyo clínico. No sustituye el criterio ni la firma del médico tratante.
    </p>
</body>
</html>