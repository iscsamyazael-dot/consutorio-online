<?php
// Configuración de la base de datos
$host = '163.192.138.122';
$db   = 'consultorio_online';
$user = 'ultratech_admin';
$pass = 'ultratech@2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);

    // Obtener el folio desde la URL
    $folio = isset($_GET['folio']) ? $_GET['folio'] : '';

    if (!empty($folio)) {
        // Consultar la cita con los JOINs para obtener nombres descriptivos
        $sql = "SELECT 
                    c.id, c.folio, c.fecha, c.hora, c.estado, 
                    p.nombre AS paciente_nombre, 
                    m.nombre AS medico_nombre, 
                    e.nombre AS especialidad_nombre
                FROM citas c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON c.especialidad_id = e.id
                WHERE c.folio = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$folio]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cita) {
            echo "<h1>Cita Validada</h1>";
            echo "<p><strong>Paciente:</strong> " . htmlspecialchars($cita['paciente_nombre']) . "</p>";
            echo "<p><strong>Médico:</strong> " . htmlspecialchars($cita['medico_nombre']) . "</p>";
            echo "<p><strong>Especialidad:</strong> " . htmlspecialchars($cita['especialidad_nombre']) . "</p>";
            echo "<p><strong>Fecha:</strong> " . htmlspecialchars($cita['fecha']) . "</p>";
            echo "<p><strong>Hora:</strong> " . htmlspecialchars($cita['hora']) . "</p>";
            echo "<p><strong>Estado:</strong> " . htmlspecialchars($cita['estado']) . "</p>";
        } else {
            echo "<h1>Error</h1><p>Cita no encontrada.</p>";
        }
    } else {
        echo "<h1>Error</h1><p>Folio no proporcionado.</p>";
    }
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
