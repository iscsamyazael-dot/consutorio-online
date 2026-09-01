<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #1e293b; font-size: 12px; }
        .header { display: table; width: 100%; border-bottom: 3px solid {{ $empresa->color_primario ?? '#0ea5e9' }}; padding-bottom: 10px; margin-bottom: 20px; }
        .header-logo { display: table-cell; width: 80px; vertical-align: middle; }
        .header-logo img { width: 60px; height: 60px; object-fit: contain; }
        .header-info { display: table-cell; vertical-align: middle; text-align: center; }
        .header-info h1 { color: {{ $empresa->color_primario ?? '#0f4c5c' }}; font-size: 18px; margin: 0; }
        .header-info p { margin: 2px 0; font-size: 10px; color: #64748b; }
        .header-folio { display: table-cell; width: 140px; vertical-align: middle; text-align: right; font-size: 10px; }
        .titulo-seccion { color: {{ $empresa->color_primario ?? '#0f4c5c' }}; font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.datos td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        table.datos td.etiqueta { font-weight: bold; width: 35%; color: #475569; }
        .qr-box { text-align: center; border: 1px dashed #cbd5e1; padding: 20px; margin-top: 20px; border-radius: 8px; }
        .qr-box img { width: 160px; height: 160px; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">
            @if($empresa->logo_url)
                <img src="{{ public_path( $empresa->logo_url) }}">
            @endif
        </div>
        <div class="header-info">
            <h1>{{ mb_strtoupper($empresa->nombre_empresa,'UTF-8') }}</h1>
            <p>{{ $empresa->direccion }}</p>
            <p>Tel: {{ $empresa->telefono }}</p>
        </div>
        <div class="header-folio">
            <strong>Folio:</strong> {{ $paciente->paciente_id }}<br>
            <strong>Fecha:</strong> {{ now()->format('Y-m-d') }}
        </div>
    </div>

    <div class="titulo-seccion">EXPEDIENTE DEL PACIENTE</div>
    <table class="datos">
        <tr><td class="etiqueta">Nombre</td><td>{{ $paciente->nombre }}</td></tr>
        <tr><td class="etiqueta">Edad</td><td>{{ $paciente->edad }}</td></tr>
        <tr><td class="etiqueta">Sexo</td><td>{{ $paciente->sexo }}</td></tr>
        <tr><td class="etiqueta">Fecha de nacimiento</td><td>{{ $paciente->fecha_nacimiento }}</td></tr>
        <tr><td class="etiqueta">Teléfono</td><td>{{ $paciente->telefono }}</td></tr>
        <tr><td class="etiqueta">Email</td><td>{{ $paciente->email }}</td></tr>
        <tr><td class="etiqueta">CURP</td><td>{{ $paciente->curp }}</td></tr>
        <tr><td class="etiqueta">Dirección</td><td>{{ $paciente->direccion }}</td></tr>
        <tr><td class="etiqueta">Tipo de sangre</td><td>{{ $paciente->tipo_sangre }}</td></tr>
        <tr><td class="etiqueta">Contacto de emergencia</td><td>{{ $paciente->contacto_emergencia }} ({{ $paciente->telefono_emergencia }})</td></tr>
        <tr><td class="etiqueta">Alergias</td><td>{{ $paciente->alergias ?: 'Ninguna registrada' }}</td></tr>
        <tr><td class="etiqueta">Antecedentes médicos</td><td>{{ $paciente->antecedentes_medicos ?: 'Ninguno registrado' }}</td></tr>
    </table>

    <div class="qr-box">
        <p style="font-weight:bold; margin-bottom:10px;">Presente este código QR al llegar a la clínica para su registro:</p>
        <img src="{{ $qrBase64 }}">
    </div>

    <div class="footer">
        Documento generado como expediente y comprobante de registro &bull; {{ $empresa->nombre_empresa }} &bull; {{ $empresa->direccion }} &bull; Tel: {{ $empresa->telefono }}
    </div>
</body>
</html>