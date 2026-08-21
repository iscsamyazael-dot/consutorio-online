<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Hola {{ $paciente->nombre }},</h2>

    <p>
        Gracias por registrarte en nuestro consultorio. Te compartimos tu
        código QR personal — guárdalo en tu celular o imprímelo.
    </p>

    <p>
        En tu próxima visita, solo muestra este código al llegar y tus datos
        se cargarán automáticamente, sin necesidad de volver a llenar el
        formulario de registro.
    </p>

    <p>Nos vemos pronto.</p>
</body>
</html>