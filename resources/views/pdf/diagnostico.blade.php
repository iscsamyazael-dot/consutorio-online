<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px 36px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
        }
        table { border-collapse: collapse; width: 100%; }

        /* ---------- Encabezado: logo + consultorio + folio/fecha ---------- */
        .header-table td { vertical-align: middle; }
        .logo-img {
            width: 52px;
            height: 52px;
        }
        .clinica-nombre {
            font-size: 16px;
            font-weight: bold;
            color: #0B7285;
        }
        .clinica-sub {
            font-size: 9.5px;
            color: #6b7280;
            line-height: 1.5;
        }
        .fecha-box {
            text-align: right;
            font-size: 11px;
            color: #374151;
            white-space: nowrap;
        }
        .fecha-box strong { color: #111827; }
        .header-line {
            border-bottom: 2px solid #0B7285;
            margin: 10px 0 14px;
        }

        /* ---------- Médico que atiende ---------- */
        .medico-box {
            background: #f0fbfc;
            border: 1px solid #d3eef1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }
        .medico-nombre {
            font-size: 12.5px;
            font-weight: bold;
            color: #111827;
        }
        .medico-detalle {
            font-size: 10px;
            color: #4b5563;
        }

        /* ---------- Título + badge de estado ---------- */
        .titulo-table { margin-bottom: 10px; }
        .titulo-table td { vertical-align: middle; }
        .titulo-nota {
            font-size: 14.5px;
            font-weight: bold;
            color: #111827;
        }
        .titulo-sub {
            font-size: 9.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            float: right;
        }
        .badge-final { background: #e8f6ee; color: #146c43; border: 1px solid #bfe6cf; }
        .badge-borrador { background: #fff8e6; color: #92700f; border: 1px solid #fbe6a8; }

        /* ---------- Paciente / diagnóstico ---------- */
        .datos-table td {
            font-size: 11.5px;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .datos-label {
            color: #6b7280;
            font-weight: bold;
            width: 110px;
        }
        .datos-table { margin-bottom: 14px; }
        .badge-alerta {
            display: inline-block;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: bold;
        }

        /* ---------- Diagnóstico probable (destacado, igual que indicaciones del médico en receta) ---------- */
        .diagnostico-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 3px solid #d97706;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .diagnostico-box h2 {
            color: #92400e;
            margin: 0 0 6px;
        }
        .diagnostico-box p {
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            color: #374151;
            white-space: pre-line;
        }

        /* ---------- Secciones PSOAPP ---------- */
        h2 {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 16px 0 6px;
            color: #0B7285;
            border-bottom: 1px solid #e5e9f0;
            padding-bottom: 4px;
        }
        .seccion p {
            font-size: 11px;
            line-height: 1.5;
            margin: 0 0 6px;
            color: #374151;
            white-space: pre-line;
        }
        .seccion p.vacio {
            color: #9ca3af;
            font-style: italic;
        }

        .aviso-sin-nota {
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 4px;
            padding: 14px;
            text-align: center;
            color: #6b7280;
            font-style: italic;
            margin-top: 10px;
        }

        /* ---------- Firma / footer ---------- */
        .firma-wrap { margin-top: 50px; }
        .firma-box { width: 220px; margin-left: auto; text-align: center; }
        .firma-linea { border-top: 1px solid #9ca3af; }
        .firma-texto { font-size: 10px; color: #6b7280; margin-top: 4px; }

        .footer-nota {
            margin-top: 24px;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e9f0;
            padding-top: 8px;
            line-height: 1.5;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 12%;">
                @if(!empty($logoPath))
                    <img src="{{ $logoPath }}" class="logo-img">
                @endif
            </td>
            <td style="width: 48%;">
                {{-- Datos fijos del consultorio --}}
                <div class="clinica-nombre">Ultra Farmacia</div>
                <div class="clinica-sub">
                    Calle Centro<br>
                    Tel: 988 966 5839
                </div>
            </td>
            <td style="width: 40%;" class="fecha-box">
                <strong>Folio:</strong> {{ $consulta->folio }}<br>
                <strong>Fecha:</strong> {{ $consulta->created_at->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>
    <div class="header-line"></div>

    {{-- Médico que atiende la consulta (consultas.user_id) --}}
    <div class="medico-box">
        <div class="medico-nombre">{{ $medico->name ?? 'Médico no asignado' }}</div>
        @if(!empty($medico->rol))
            <div class="medico-detalle">{{ ucfirst($medico->rol) }}</div>
        @endif
    </div>

    <table class="titulo-table">
        <tr>
            <td>
                <div class="titulo-nota">Nota de Evolución Clínica</div>
                <div class="titulo-sub">Formato PSOAPP</div>
            </td>
            <td style="text-align: right;">
                @if($nota)
                    <span class="badge {{ $nota->estado === 'final' ? 'badge-final' : 'badge-borrador' }}">
                        {{ $nota->estado === 'final' ? 'NOTA FINAL' : 'BORRADOR' }}
                    </span>
                @endif
            </td>
        </tr>
    </table>

    @php
        $nombrePaciente = trim(
            ($consulta->paciente->nombre ?? '') . ' ' .
            ($consulta->paciente->apellido_paterno ?? '') . ' ' .
            ($consulta->paciente->apellido_materno ?? '')
        );
    @endphp
    <table class="datos-table">
        <tr>
            <td class="datos-label">Paciente:</td>
            <td>{{ $nombrePaciente ?: 'N/D' }}</td>
        </tr>
        <tr>
            <td class="datos-label">Edad / Sexo:</td>
            <td>
                {{ $consulta->paciente->edad ?? 'N/D' }} años
                &nbsp;·&nbsp;
                {{ $consulta->paciente->sexo ?? 'N/D' }}
                @if(!empty($consulta->paciente->tipo_sangre))
                    &nbsp;·&nbsp; Tipo de sangre: {{ $consulta->paciente->tipo_sangre }}
                @endif
            </td>
        </tr>
        @if(!empty($consulta->paciente->alergias))
        <tr>
            <td class="datos-label">Alergias:</td>
            <td><span class="badge-alerta">{{ $consulta->paciente->alergias }}</span></td>
        </tr>
        @endif
    </table>

    {{-- Diagnóstico probable (evaluaciones_ia.diagnostico_probable) --}}
    @if($evaluacion && !empty($evaluacion->diagnostico_probable))
    <div class="diagnostico-box">
        <h2 style="border-bottom:none; margin:0 0 6px; padding-bottom:0;">Diagnóstico probable</h2>
        <p>{{ $evaluacion->diagnostico_probable }}</p>
    </div>
    @endif

    @if($nota)
        <div class="seccion">
            <h2>Presentación</h2>
            <p class="{{ empty($nota->presentacion) ? 'vacio' : '' }}">{{ $nota->presentacion ?: 'No disponible' }}</p>

            <h2>Subjetivo</h2>
            <p class="{{ empty($nota->subjetivo) ? 'vacio' : '' }}">{{ $nota->subjetivo ?: 'No disponible' }}</p>

            <h2>Objetivo</h2>
            <p class="{{ empty($nota->objetivo) ? 'vacio' : '' }}">{{ $nota->objetivo ?: 'No disponible' }}</p>

            <h2>Análisis</h2>
            <p class="{{ empty($nota->analisis) ? 'vacio' : '' }}">{{ $nota->analisis ?: 'No disponible' }}</p>

            <h2>Plan</h2>
            <p class="{{ empty($nota->plan) ? 'vacio' : '' }}">{{ $nota->plan ?: 'No disponible' }}</p>

            <h2>Pronóstico</h2>
            <p class="{{ empty($nota->pronostico) ? 'vacio' : '' }}">{{ $nota->pronostico ?: 'No disponible' }}</p>
        </div>
    @else
        <div class="aviso-sin-nota">
            Esta consulta todavía no tiene una nota PSOAPP generada.
        </div>
    @endif

    <div class="firma-wrap">
        <div class="firma-box">
            <div class="firma-linea"></div>
            <div class="firma-texto">Firma</div>
        </div>
    </div>

    <p class="footer-nota">
        Ultra Farmacia &nbsp;·&nbsp; Calle Centro &nbsp;·&nbsp; Tel: 988 966 5839<br>
        Documento generado como apoyo clínico. No sustituye el criterio ni la firma del médico tratante.
    </p>

</body>
</html>