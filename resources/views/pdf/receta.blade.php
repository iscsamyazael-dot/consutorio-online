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
            width: 68px;
            height: 68px;
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

        /* ---------- Indicaciones / recomendaciones del médico ---------- */
        .indicaciones-medico-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 3px solid #d97706;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .indicaciones-medico-box h2 {
            color: #92400e;
            margin: 0 0 6px;
        }
        .indicaciones-medico-box p {
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            color: #374151;
            white-space: pre-line;
        }
        .indicaciones-medico-box .bloque + .bloque {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #fde68a;
        }
        .indicaciones-medico-box .bloque-label {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: bold;
            color: #b45309;
            display: block;
            margin-bottom: 2px;
        }

        /* ---------- Cuerpo: medicamentos + signos vitales ---------- */
        .cuerpo-table td { vertical-align: top; }
        .col-meds { width: 68%; padding-right: 18px; }
        .col-vitales {
            width: 32%;
            vertical-align: top;
        }

        h2 {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 8px;
            color: #0B7285;
        }

        .med-item {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .med-nombre {
            font-weight: bold;
            font-size: 12px;
            color: #111827;
        }
        .med-detalles {
            margin: 3px 0 0 16px;
            color: #374151;
            font-size: 11px;
        }
        .med-instrucciones {
            margin-top: 2px;
            margin-left: 16px;
            color: #6b7280;
            font-style: italic;
        }

        .indicaciones h2 { margin-top: 18px; }
        .indicaciones p {
            font-size: 11px;
            line-height: 1.5;
            margin: 0 0 6px;
        }

        /* ---------- Signos vitales (mismo diseño que la nota de evolución) ---------- */
        .vitales-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 12px;
        }
        .vitales-box h2 {
            border-bottom: none;
            margin: 0 0 8px;
            padding-bottom: 0;
        }
        .vitales-table td {
            font-size: 10.5px;
            padding: 6px 4px;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .vital-valor {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }
        .vital-valor.alerta {
            color: #b91c1c;
        }
        .vital-etiqueta {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #6b7280;
            margin-top: 2px;
        }
        .vitales-vacio {
            font-size: 10.5px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ---------- Firmas / footer ---------- */
        .firma-wrap { margin-top: 50px; }
        .firmas-table { width: 100%; }
        .firmas-table td.firma-box {
            width: 33.33%;
            text-align: center;
            padding: 0 12px;
        }
        .firma-linea { border-top: 1px solid #9ca3af; margin: 0 6px; }
        .firma-texto {
            font-size: 10px;
            color: #374151;
            margin-top: 4px;
            font-weight: bold;
        }
        .firma-subtexto {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 1px;
        }

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

    @php
        // Datos del consultorio/sucursal del médico que atendió esta
        // consulta ($ubicacion viene del controlador, ya resuelto por
        // medico -> configuracion_medico_sucursal -> ubicaciones).
        // Si el médico no tiene sucursal configurada, se usa el texto
        // genérico como respaldo para que el documento no salga vacío.
        $nombreConsultorio = $ubicacion->nombre ?? 'Ultra Farmacia';
        $direccionConsultorio = $ubicacion->direccion ?? 'Calle Centro';
        $telefonoConsultorio = $ubicacion->telefono ?? '988 966 5839';
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 15%;">
                @if(!empty($logoPath))
                    <img src="{{ $logoPath }}" class="logo-img">
                @endif
            </td>
            <td style="width: 45%;">
                {{-- Datos del consultorio: ahora dinámicos según el médico --}}
                <div class="clinica-nombre">{{ $nombreConsultorio }}</div>
                <div class="clinica-sub">
                    {{ $direccionConsultorio }}<br>
                    Tel: {{ $telefonoConsultorio }}
                </div>
            </td>
            <td style="width: 40%;" class="fecha-box">
                <strong>Folio:</strong> {{ $consulta->folio }}<br>
                <strong>Fecha:</strong> {{ $consulta->created_at->format('d/m/Y') }}
            </td>
        </tr>
    </table>
    <div class="header-line"></div>

    {{-- Médico que atiende la consulta (consultas.user_id) --}}
    <div class="medico-box">
        <div class="medico-nombre">{{ $medico->name ?? 'Médico no asignado' }}</div>
        @if(!empty($medico->cedula_profesional))
            <div class="medico-detalle">Céd. Prof. {{ $medico->cedula_profesional }}</div>
        @endif
        @if(!empty($medico->rol))
            <div class="medico-detalle">{{ ucfirst($medico->rol) }}</div>
        @endif
    </div>

    <table class="datos-table">
        @php
            $nombrePaciente = trim(
                ($consulta->paciente->nombre ?? '') . ' ' .
                ($consulta->paciente->apellido_paterno ?? '') . ' ' .
                ($consulta->paciente->apellido_materno ?? '')
            );
        @endphp
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
        @if($evaluacion)
        <tr>
            <td class="datos-label">Diagnóstico:</td>
            <td>{{ $evaluacion->diagnostico_probable }}</td>
        </tr>
        @endif
    </table>

    {{-- Indicaciones y recomendación del médico (evaluaciones_ia.indicaciones_medico / .recomendacion) --}}
    @if($evaluacion && (!empty($evaluacion->indicaciones_medico) || !empty($evaluacion->recomendacion)))
    <div class="indicaciones-medico-box">
        <h2>Indicaciones / Recomendaciones del médico</h2>

        @if(!empty($evaluacion->indicaciones_medico))
        <div class="bloque">
            <span class="bloque-label">Indicaciones</span>
            <p>{{ $evaluacion->indicaciones_medico }}</p>
        </div>
        @endif

        @if(!empty($evaluacion->recomendacion))
        <div class="bloque">
            <span class="bloque-label">Recomendación</span>
            <p>{{ $evaluacion->recomendacion }}</p>
        </div>
        @endif
    </div>
    @endif

    @if($receta || $triage)
    <table class="cuerpo-table">
        <tr>
            <td class="col-meds">
                <h2>Medicamentos</h2>
                @if($receta && $receta->medicamentos && count($receta->medicamentos) > 0)
                    @foreach($receta->medicamentos as $index => $med)
                        <div class="med-item">
                            <div class="med-nombre">{{ $index + 1 }}. {{ $med['nombre'] ?? '' }}</div>
                            <div class="med-detalles">
                                {{ $med['dosis'] ?? '' }} &nbsp;·&nbsp; {{ $med['frecuencia'] ?? '' }} &nbsp;·&nbsp; {{ $med['duracion'] ?? '' }}
                            </div>
                            @if(!empty($med['instrucciones']))
                                <div class="med-instrucciones">{{ $med['instrucciones'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p><em>No se registraron medicamentos para esta receta.</em></p>
                @endif

                @if(!empty($receta->indicaciones_generales))
                <div class="indicaciones">
                    <h2>Recomendación</h2>
                    <p>{{ $receta->indicaciones_generales }}</p>
                </div>
                @endif
            </td>

            <td class="col-vitales">
                {{--
                    Signos vitales: mismo diseño que la nota de evolución
                    clínica (cajitas con valor grande + etiqueta), pero en
                    grid de 2 columnas para caber en la columna lateral.
                    Mapeo de columnas reales de la tabla `triage`:
                        presion, frecuencia_cardiaca, frecuencia_respiratoria,
                        temperatura, saturacion, peso, talla
                --}}
                <div class="vitales-box">
                    <h2>Signos vitales</h2>
                    @if($triage)
                        @php
                            $tieneAlgunVital = !empty($triage->presion) || !empty($triage->frecuencia_cardiaca)
                                || !empty($triage->frecuencia_respiratoria) || !empty($triage->saturacion)
                                || !empty($triage->temperatura) || !empty($triage->peso) || !empty($triage->talla);
                        @endphp

                        @if($tieneAlgunVital)
                            <table class="vitales-table">
                                <tr>
                                    <td>
                                        <div class="vital-valor">{{ $triage->presion ?? '—' }}</div>
                                        <div class="vital-etiqueta">T.A. (mmHg)</div>
                                    </td>
                                    <td>
                                        <div class="vital-valor">{{ $triage->frecuencia_cardiaca ?? '—' }}</div>
                                        <div class="vital-etiqueta">F.C. (lpm)</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="vital-valor">{{ $triage->frecuencia_respiratoria ?? '—' }}</div>
                                        <div class="vital-etiqueta">F.R. (rpm)</div>
                                    </td>
                                    <td>
                                        <div class="vital-valor">{{ $triage->temperatura ?? '—' }}</div>
                                        <div class="vital-etiqueta">Temp. (°C)</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="vital-valor {{ isset($triage->saturacion) && $triage->saturacion < 92 ? 'alerta' : '' }}">
                                            {{ $triage->saturacion ?? '—' }}
                                        </div>
                                        <div class="vital-etiqueta">SpO2 (%)</div>
                                    </td>
                                    <td>
                                        <div class="vital-valor">{{ $triage->peso ?? '—' }}</div>
                                        <div class="vital-etiqueta">Peso (kg)</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="vital-valor">{{ $triage->talla ?? '—' }}</div>
                                        <div class="vital-etiqueta">Talla (cm)</div>
                                    </td>
                                    <td></td>
                                </tr>
                            </table>
                        @else
                            <div class="vitales-vacio">Sin signos vitales registrados.</div>
                        @endif
                    @else
                        <div class="vitales-vacio">Sin triage registrado para esta consulta.</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
    @endif

    <div class="firma-wrap">
        <table class="firmas-table">
            <tr>
                <td class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">{{ $medico->name ?? 'Médico' }}</div>
                    <div class="firma-subtexto">Médico tratante</div>
                </td>
                <td class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">{{ $nombrePaciente ?: 'Paciente' }}</div>
                    <div class="firma-subtexto">Paciente</div>
                </td>
                <td class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">&nbsp;</div>
                    <div class="firma-subtexto">Testigo</div>
                </td>
            </tr>
        </table>
    </div>

    <p class="footer-nota">
        {{ $nombreConsultorio }} &nbsp;·&nbsp; {{ $direccionConsultorio }} &nbsp;·&nbsp; Tel: {{ $telefonoConsultorio }}<br>
        Documento generado como apoyo clínico. No sustituye el criterio ni la firma del médico tratante.
    </p>

</body>
</html>