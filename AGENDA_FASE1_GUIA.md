# FASE 1 - Agenda Médica: Guía de Implementación

## Resumen de Cambios

Se ha completado la **FASE 1** del módulo Agenda con una implementación moderna, rápida y fácil de usar.

### ✅ Lo que se implementó:

#### 1. **Base de Datos Mejorada**
- Nueva migración: `2026_05_26_150000_enhance_citas_table.php`
  - Campo `color`: Para diferenciar estados visualmente (#3b82f6 por defecto)
  - Campo `confirmada_paciente`: Boolean para confirmar cita por paciente
  - Campo `recordatorio_enviado`: Boolean para control de recordatorios (FASE 2)
  - Campo `razon_cancelacion`: Texto para explicar cancelaciones
  - Campo `cancelada_en`: Timestamp para fecha de cancelación
  - Campo `duracion_minutos`: Integer más preciso que string

- Nueva tabla: `doctor_availabilities`
  - Almacena horarios de disponibilidad de médicos por día de semana
  - Validación de disponibilidad en tiempo real

#### 2. **Servicio de Validaciones** (`App\Http\CitaValidationService`)
Métodos disponibles:
- `validarSobreposicion()`: Detecta conflictos de horarios
- `validarDisponibilidadMedico()`: Verifica horarios de trabajo del médico
- `validarFechaFutura()`: Evita citas en el pasado
- `validarDuracionMinima()`: Valida duración (15-480 minutos)
- `validarCompleto()`: Ejecuta todas las validaciones

#### 3. **API RESTful** (`App\Http\Controllers\Api\CitaController`)

**Rutas disponibles:**
```
GET    /api/citas/rango          - Listar citas en rango de fechas (para FullCalendar)
POST   /api/citas                - Crear nueva cita
PUT    /api/citas/{id}           - Actualizar cita
DELETE /api/citas/{id}           - Eliminar cita
PUT    /api/citas/{id}/estado    - Cambiar estado de cita
POST   /api/citas/{id}/confirmar - Confirmar cita por paciente
GET    /api/pacientes/buscar     - Buscar pacientes (autocomplete)
GET    /api/medicos              - Obtener lista de médicos
```

#### 4. **Interfaz de Calendario** (`resources/views/citas/calendario.blade.php`)

**Características:**
- Calendario profesional con FullCalendar.io v6
- Vistas: Mes, Semana, Día, Lista
- Panel lateral con citas del día actual
- Formulario rápido para crear citas sin recargar
- Búsqueda de pacientes con autocomplete
- Selector de médicos dinámico
- Modal para ver detalles de cita
- Validación en tiempo real en cliente

**Cómo acceder:**
```
Ruta: GET /citas/calendario/view
Nombre: citas.calendario
```

#### 5. **Tests Completos**

**Tests Unitarios** (`tests/Unit/CitaValidationServiceTest.php`):
```bash
php artisan test --filter=CitaValidationServiceTest
```

**Tests Feature API** (`tests/Feature/CitaApiTest.php`):
```bash
php artisan test --filter=CitaApiTest
```

#### 6. **Factories para Testing**
- `Database\Factories\CitaFactory`
- `Database\Factories\DoctorAvailabilityFactory`

## 🚀 Cómo Usar

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Crear Disponibilidad de Médicos
```php
// En tinker o seed:
use App\Models\DoctorAvailability;
use App\Models\User;

$medico = User::where('role', 'medico')->first();

// Lunes a Viernes, 9 AM a 5 PM
for ($dia = 0; $dia < 5; $dia++) {
    DoctorAvailability::create([
        'user_id' => $medico->id,
        'dia_semana' => $dia,
        'hora_inicio' => '09:00',
        'hora_fin' => '17:00',
        'activo' => true,
    ]);
}
```

### 3. Acceder al Calendario
```
http://localhost/citas/calendario/view
```

### 4. Crear Cita vía API (desde Cliente)
```javascript
fetch('/api/citas', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
    },
    body: JSON.stringify({
        paciente_id: 1,
        user_id: 2,
        fecha_hora: '2026-06-01T10:00:00',
        duracion_minutos: 30,
        motivo: 'Consulta general',
        tipo_cita: 'Presencial'
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

### 5. Buscar Paciente
```javascript
fetch('/api/pacientes/buscar?q=juan')
    .then(res => res.json())
    .then(pacientes => console.log(pacientes));
```

## 📁 Archivos Creados/Modificados

### Nuevos Archivos:
```
app/Http/CitaValidationService.php
app/Models/DoctorAvailability.php
app/Http/Controllers/Api/CitaController.php
database/migrations/2026_05_26_150000_enhance_citas_table.php
database/migrations/2026_05_26_150001_create_doctor_availabilities_table.php
database/factories/CitaFactory.php
database/factories/DoctorAvailabilityFactory.php
resources/views/citas/calendario.blade.php
tests/Unit/CitaValidationServiceTest.php
tests/Feature/CitaApiTest.php
```

### Modificados:
```
app/Models/Cita.php                          (Agregados campos a fillable y casts)
app/Http/Controllers/CitaController.php       (Agregado método calendario())
routes/web.php                               (Agregadas rutas API y ruta calendario)
package.json                                 (Agregadas dependencias FullCalendar)
```

## 🔗 Estados de Cita
Los estados disponibles son:
- `pendiente`: Cita recién creada
- `confirmada`: Confirmada por paciente o médico
- `cancelada`: Cancelada con razón
- `atendida`: Completada

## 🎨 Colores por Estado (Personalizable)
El color se almacena en la base de datos en el campo `color`. Por defecto:
- `#3b82f6` (Azul): Estado pendiente/confirmada

Puedes cambiar colores según el estado en el servicio o controlador API.

## ⚠️ Próximos Pasos (FASE 2)

1. **Reagendamiento con Drag/Drop**: Permitir mover citas en el calendario
2. **Confirmaciones**: Notificar a pacientes para confirmar citas
3. **Recordatorios**: Enviar recordatorios antes de citas
4. **Notificaciones**: Email/SMS a pacientes
5. **Automatización**: Flujos automáticos basados en estados

---

**Fecha de Implementación:** 2026-05-26  
**Versión:** 1.0 FASE 1  
**Estado:** ✅ Completada y Probada
