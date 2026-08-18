<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>
        Nota SOAP y Receta - {{ $consulta->folio ?? '' }}
    </title>

    <style>

        @page {
            margin: 28px 40px 45px 40px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #263238;
            line-height: 1.45;
        }

        /* =====================================================
           ENCABEZADO
        ===================================================== */

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-logo {
            width: 70px;
            vertical-align: middle;
        }

        .header-info {
            vertical-align: middle;
            padding-left: 12px;
        }

        .header-meta {
            width: 150px;
            text-align: right;
            vertical-align: middle;
        }

        .clinic-title {
            font-size: 15px;
            font-weight: bold;
            color: #087c8c;
            text-transform: uppercase;
        }

        .clinic-sub {
            font-size: 9px;
            color: #718096;
            margin-top: 3px;
        }

        .clinic-phone {
            font-size: 9px;
            color: #718096;
        }

        .meta-label {
            font-weight: bold;
            color: #1a202c;
        }

        .meta-value {
            color: #4a5568;
        }

        .header-line {
            border-top: 2px solid #087c8c;
            margin-top: 10px;
            margin-bottom: 12px;
        }

        /* =====================================================
           LOGO
        ===================================================== */

        .logo-img {
            width: 55px;
            height: 55px;
        }

        .logo-placeholder {
            width: 55px;
            height: 55px;
            border: 1px solid #cbd5e0;
            border-radius: 50%;
            text-align: center;
            line-height: 55px;
            font-size: 8px;
            color: #718096;
        }

        /* =====================================================
           MÉDICO
        ===================================================== */

        .doctor-box {
            background-color: #edf9fb;
            border: 1px solid #c5e9ee;
            border-radius: 4px;
            padding: 9px 12px;
            margin-bottom: 15px;
        }

        .doctor-name {
            font-size: 11px;
            font-weight: bold;
            color: #1a2737;
        }

        .doctor-role {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* =====================================================
           DATOS DEL PACIENTE
        ===================================================== */

        .patient-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .patient-table td {
            padding: 5px 0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .patient-label {
            width: 125px;
            font-weight: bold;
            color: #667085;
        }

        .patient-value {
            color: #273444;
        }

        /* =====================================================
           ETIQUETA DE ALERGIA
        ===================================================== */

        .allergy {
            display: inline-block;
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            color: #dc2626;
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }

        /* =====================================================
           TÍTULOS DE SECCIÓN
        ===================================================== */

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #087c8c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 14px;
            margin-bottom: 7px;
        }

        .section-title.soap {
            border-left: 4px solid #087c8c;
            padding-left: 7px;
        }

        /* =====================================================
           SOAP
        ===================================================== */

        .soap-box {
            border: 1px solid #d9e2ec;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .soap-item {
            margin-bottom: 8px;
        }

        .soap-item:last-child {
            margin-bottom: 0;
        }

        .soap-label {
            font-size: 9px;
            font-weight: bold;
            color: #087c8c;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .soap-text {
            font-size: 9.5px;
            color: #344054;
            text-align: justify;
        }

        /* =====================================================
           RECETA / RECOMENDACIONES
        ===================================================== */

        .recipe-box {
            background-color: #fff9e9;
            border: 1px solid #f6d365;
            border-left: 3px solid #e88900;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .recipe-title {
            font-size: 10px;
            font-weight: bold;
            color: #a64b00;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 7px;
        }

        .recipe-subtitle {
            font-size: 8.5px;
            font-weight: bold;
            color: #b45309;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .recipe-text {
            font-size: 9.5px;
            color: #344054;
            text-align: justify;
        }

        /* =====================================================
           DOS COLUMNAS
        ===================================================== */

        .two-columns {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .left-column {
            width: 72%;
            vertical-align: top;
            padding-right: 15px;
        }

        .right-column {
            width: 28%;
            vertical-align: top;
            border-left: 1px solid #e2e8f0;
            padding-left: 15px;
        }

        /* =====================================================
           MEDICAMENTOS
        ===================================================== */

        .med-title {
            font-size: 10px;
            font-weight: bold;
            color: #087c8c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }

        .tabla-meds {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-meds th {
            text-align: left;
            padding: 5px 4px;
            border-bottom: 1px solid #cbd5e0;
            color: #475569;
            font-size: 8px;
            text-transform: uppercase;
        }

        .tabla-meds td {
            padding: 6px 4px;
            border-bottom: 1px solid #edf2f7;
            font-size: 9px;
            vertical-align: top;
        }

        .empty-text {
            font-size: 9.5px;
            color: #475569;
            font-style: italic;
        }

        /* =====================================================
           SIGNOS VITALES
        ===================================================== */

        .vital-item {
            margin-bottom: 8px;
        }

        .vital-label {
            font-size: 7.5px;
            color: #718096;
            text-transform: uppercase;
        }

        .vital-value {
            font-size: 9px;
            font-weight: bold;
            color: #1a202c;
            margin-top: 1px;
        }

        /* =====================================================
           FIRMA
        ===================================================== */

        .signature-container {
            margin-top: 55px;
            text-align: right;
        }

        .signature-line {
            width: 165px;
            border-top: 1px solid #94a3b8;
            margin-left: auto;
            margin-bottom: 4px;
        }

        .signature-text {
            width: 165px;
            margin-left: auto;
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            border-top: 1px solid #d9e2ec;
            padding-top: 5px;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
        }

    </style>
</head>

<body>

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <table class="header">

        <tr>

            {{-- LOGO --}}
            <td class="header-logo">

                @if(!empty($logoPath))

                    <img
                        src="{{ $logoPath }}"
                        class="logo-img"
                        alt="Logo"
                    >

                @else

                    <div class="logo-placeholder">
                        LOGO
                    </div>

                @endif

            </td>

            {{-- DATOS DEL CONSULTORIO --}}
            <td class="header-info">

                <div class="clinic-title">
                    {{ $ubicacion->nombre ?? 'CONSULTORIO - ULTRA KANTUNIL' }}
                </div>

                <div class="clinic-sub">
                    {{ $ubicacion->direccion ?? 'calle 30 271' }}
                </div>

                <div class="clinic-phone">
                    Tel: {{ $ubicacion->telefono ?? '9881035145' }}
                </div>

            </td>

            {{-- FOLIO / FECHA --}}
            <td class="header-meta">

                <div>
                    <span class="meta-label">Folio:</span>
                    <span class="meta-value">
                        {{ $consulta->folio ?? '' }}
                    </span>
                </div>

                <div style="margin-top: 5px;">
                    <span class="meta-label">Fecha:</span>
                    <span class="meta-value">
                        {{ optional($consulta->created_at)->format('d/m/Y') ?? date('d/m/Y') }}
                    </span>
                </div>

            </td>

        </tr>

    </table>

    <div class="header-line"></div>


    {{-- =====================================================
         MÉDICO
    ====================================================== --}}

    <div class="doctor-box">

        <div class="doctor-name">
            Dr. {{ $medico->name ?? 'Alejandro Paredes Cocon' }}
        </div>

        <div class="doctor-role">
            Medico
        </div>

    </div>


    {{-- =====================================================
         DATOS DEL PACIENTE
    ====================================================== --}}

    <table class="patient-table">

        <tr>

            <td class="patient-label">
                Paciente:
            </td>

            <td class="patient-value">

                {{ $consulta->paciente->nombre ?? '' }}

                {{ $consulta->paciente->apellido ?? '' }}

            </td>

        </tr>

        <tr>

            <td class="patient-label">
                Edad / Sexo:
            </td>

            <td class="patient-value">

                {{ $consulta->paciente->edad ?? '' }} años

                &nbsp; · &nbsp;

                {{ $consulta->paciente->sexo ?? '' }}

                @if(!empty($consulta->paciente->tipo_sangre))

                    &nbsp; · &nbsp;

                    Tipo de sangre:
                    {{ $consulta->paciente->tipo_sangre }}

                @endif

            </td>

        </tr>

        <tr>

            <td class="patient-label">
                Alergias:
            </td>

            <td class="patient-value">

                @if(!empty($consulta->paciente->alergias))

                    <span class="allergy">
                        {{ $consulta->paciente->alergias }}
                    </span>

                @else

                    Sin alergias registradas

                @endif

            </td>

        </tr>

        <tr>

            <td class="patient-label">
                Diagnóstico:
            </td>

            <td class="patient-value">

                {{ $consulta->diagnostico ?? 'No registrado' }}

            </td>

        </tr>

    </table>


    {{-- =====================================================
         NOTA SOAP
    ====================================================== --}}

    <div class="section-title soap">
        NOTA MÉDICA (SOAP)
    </div>

    <div class="soap-box">

        {{-- SUBJETIVO --}}
        <div class="soap-item">

            <div class="soap-label">
                Subjetivo
            </div>

            <div class="soap-text">
                {{ $nota->subjetivo ?? 'No se registró información subjetiva.' }}
            </div>

        </div>


        {{-- OBJETIVO --}}
        <div class="soap-item">

            <div class="soap-label">
                Objetivo
            </div>

            <div class="soap-text">
                {{ $nota->objetivo ?? 'No se registraron hallazgos objetivos.' }}
            </div>

        </div>


        {{-- ANÁLISIS --}}
        <div class="soap-item">

            <div class="soap-label">
                Análisis
            </div>

            <div class="soap-text">

                {{ $nota->analisis
                    ?? $consulta->diagnostico
                    ?? 'No se registró análisis clínico.'
                }}

            </div>

        </div>


        {{-- PLAN --}}
        <div class="soap-item">

            <div class="soap-label">
                Plan
            </div>

            <div class="soap-text">
                {{ $nota->plan ?? 'No se registró plan de tratamiento.' }}
            </div>

        </div>


        {{-- PRONÓSTICO --}}
        <div class="soap-item">

            <div class="soap-label">
                Pronóstico
            </div>

            <div class="soap-text">
                {{ $nota->pronostico ?? 'No se registró pronóstico.' }}
            </div>

        </div>

    </div>


    {{-- =====================================================
         RECETA INTELIGENTE
    ====================================================== --}}

    <div class="section-title">
        RECETA INTELIGENTE
    </div>


    @php

        $indicaciones = null;

        if ($receta) {

            $indicaciones =
                $receta->indicaciones_generales
                ?? $receta->recomendacion
                ?? null;

        }

        if (!$indicaciones && !empty($nota->plan)) {
            $indicaciones = $nota->plan;
        }

    @endphp


    {{-- RECOMENDACIONES --}}
    @if(!empty($indicaciones))

        <div class="recipe-box">

            <div class="recipe-title">
                INDICACIONES / RECOMENDACIONES DEL MÉDICO
            </div>

            <div class="recipe-subtitle">
                Recomendación
            </div>

            <div class="recipe-text">
                {{ $indicaciones }}
            </div>

        </div>

    @endif


    {{-- =====================================================
         MEDICAMENTOS + SIGNOS VITALES
    ====================================================== --}}

    @php

        $medicamentos = [];

        if ($receta) {

            if (is_string($receta->medicamentos)) {

                $medicamentos =
                    json_decode($receta->medicamentos, true)
                    ?? [];

            } elseif (is_array($receta->medicamentos)) {

                $medicamentos =
                    $receta->medicamentos;

            } elseif (isset($receta->detalles)) {

                $medicamentos =
                    $receta->detalles;

            }

        }

    @endphp


    <table class="two-columns">

        <tr>

            {{-- =================================================
                 MEDICAMENTOS
            ================================================== --}}

            <td class="left-column">

                <div class="med-title">
                    MEDICAMENTOS
                </div>


                @if(!empty($medicamentos))

                    <table class="tabla-meds">

                        <thead>

                            <tr>

                                <th style="width:35%;">
                                    Medicamento
                                </th>

                                <th style="width:30%;">
                                    Dosis / Frecuencia
                                </th>

                                <th style="width:35%;">
                                    Instrucciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($medicamentos as $med)

                                @php

                                    $nombre =
                                        is_array($med)
                                        ? ($med['nombre']
                                            ?? $med['medicamento_nombre']
                                            ?? '')
                                        : ($med->nombre
                                            ?? $med->medicamento_nombre
                                            ?? '');

                                    $dosis =
                                        is_array($med)
                                        ? ($med['dosis_frecuencia']
                                            ?? $med['dosis']
                                            ?? '')
                                        : ($med->dosis_frecuencia
                                            ?? $med->dosis
                                            ?? '');

                                    $instrucciones =
                                        is_array($med)
                                        ? ($med['instrucciones'] ?? '')
                                        : ($med->instrucciones ?? '');

                                @endphp


                                <tr>

                                    <td>
                                        <strong>
                                            {{ $nombre }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $dosis }}
                                    </td>

                                    <td>
                                        {{ $instrucciones }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @else

                    <div class="empty-text">
                        No se registraron medicamentos para esta receta.
                    </div>

                @endif

            </td>


            {{-- =================================================
                 SIGNOS VITALES
            ================================================== --}}

            <td class="right-column">

                <div class="med-title">
                    SIGNOS VITALES
                </div>


                @if(isset($triage))

                    @if(!empty($triage->presion_arterial))

                        <div class="vital-item">

                            <div class="vital-label">
                                Presión arterial
                            </div>

                            <div class="vital-value">
                                {{ $triage->presion_arterial }}
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->frecuencia_cardiaca))

                        <div class="vital-item">

                            <div class="vital-label">
                                Frec. cardiaca
                            </div>

                            <div class="vital-value">
                                {{ $triage->frecuencia_cardiaca }} lpm
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->frecuencia_respiratoria))

                        <div class="vital-item">

                            <div class="vital-label">
                                Frec. respiratoria
                            </div>

                            <div class="vital-value">
                                {{ $triage->frecuencia_respiratoria }} rpm
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->spo2))

                        <div class="vital-item">

                            <div class="vital-label">
                                SpO2
                            </div>

                            <div class="vital-value">
                                {{ $triage->spo2 }}
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->temperatura))

                        <div class="vital-item">

                            <div class="vital-label">
                                Temperatura
                            </div>

                            <div class="vital-value">
                                {{ $triage->temperatura }} °C
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->peso))

                        <div class="vital-item">

                            <div class="vital-label">
                                Peso
                            </div>

                            <div class="vital-value">
                                {{ number_format($triage->peso, 2) }} kg
                            </div>

                        </div>

                    @endif


                    @if(!empty($triage->talla))

                        <div class="vital-item">

                            <div class="vital-label">
                                Talla
                            </div>

                            <div class="vital-value">
                                {{ number_format($triage->talla, 2) }} cm
                            </div>

                        </div>

                    @endif

                @else

                    <div class="empty-text">
                        No se registraron signos vitales.
                    </div>

                @endif

            </td>

        </tr>

    </table>


    {{-- =====================================================
         FIRMA
    ====================================================== --}}

    <div class="signature-container">

        <div class="signature-line"></div>

        <div class="signature-text">
            Firma
        </div>

    </div>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <div class="footer">

        {{ $ubicacion->nombre ?? 'CONSULTORIO - ULTRA KANTUNIL' }}

        &nbsp; · &nbsp;

        {{ $ubicacion->direccion ?? 'calle 30 271' }}

        &nbsp; · &nbsp;

        Tel:
        {{ $ubicacion->telefono ?? '9881035145' }}

        <br>

        Documento generado como apoyo clínico.
        No sustituye el criterio ni la firma del médico tratante.

    </div>

</body>

</html>