<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\DoctorAvailability;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    protected User $medico;
    protected Paciente $paciente;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->medico = User::factory()->create(['role' => 'medico']);
        $this->paciente = Paciente::factory()->create();
    }

    public function test_get_citas_por_rango(): void
    {
        $fecha = Carbon::now()->addDay();
        $cita = Cita::factory()->create([
            'fecha_hora' => $fecha,
            'paciente_id' => $this->paciente->id,
            'user_id' => $this->medico->id,
        ]);

        $response = $this->getJson(route('api.citas.rango', [
            'start' => $fecha->format('Y-m-d'),
            'end' => $fecha->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertStringContainsString($this->paciente->nombre, $response->json()[0]['title']);
    }

    public function test_crear_cita(): void
    {
        $fecha = Carbon::now()->addDay()->setHour(10);

        // Configurar disponibilidad del médico
        DoctorAvailability::factory()->create([
            'user_id' => $this->medico->id,
            'dia_semana' => $fecha->dayOfWeek - 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
        ]);

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $this->paciente->id,
            'user_id' => $this->medico->id,
            'fecha_hora' => $fecha->toIso8601String(),
            'duracion_minutos' => 30,
            'motivo' => 'Consulta general',
            'tipo_cita' => 'Presencial',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('citas', [
            'paciente_id' => $this->paciente->id,
            'motivo' => 'Consulta general',
        ]);
    }

    public function test_crear_cita_falla_sin_disponibilidad(): void
    {
        $fecha = Carbon::now()->addDay()->setHour(10);

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $this->paciente->id,
            'user_id' => $this->medico->id,
            'fecha_hora' => $fecha->toIso8601String(),
            'duracion_minutos' => 30,
            'motivo' => 'Consulta general',
            'tipo_cita' => 'Presencial',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_actualizar_cita(): void
    {
        $cita = Cita::factory()->create([
            'paciente_id' => $this->paciente->id,
            'user_id' => $this->medico->id,
        ]);

        $response = $this->putJson("/api/citas/{$cita->id}", [
            'motivo' => 'Consulta actualizada',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'motivo' => 'Consulta actualizada',
        ]);
    }

    public function test_cambiar_estado_cita(): void
    {
        $cita = Cita::factory()->create([
            'estado' => 'pendiente',
        ]);

        $response = $this->putJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'confirmada',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);
    }

    public function test_eliminar_cita(): void
    {
        $cita = Cita::factory()->create();

        $response = $this->deleteJson("/api/citas/{$cita->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }

    public function test_buscar_paciente(): void
    {
        $paciente = Paciente::factory()->create([
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ]);

        $response = $this->getJson('/api/pacientes/buscar?q=Juan');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals($paciente->id, $response->json()[0]['id']);
    }

    public function test_obtener_medicos(): void
    {
        $response = $this->getJson('/api/medicos');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json()));
    }
}
