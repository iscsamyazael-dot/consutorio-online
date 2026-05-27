<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CitaFactory extends Factory
{
    protected $model = Cita::class;

    public function definition(): array
    {
        $fecha_hora = Carbon::now()->addDays(rand(1, 30))->setHour(rand(9, 17))->setMinute(0);

        return [
            'paciente_id' => Paciente::factory(),
            'user_id' => User::factory(),
            'fecha_hora' => $fecha_hora,
            'duracion_minutos' => $this->faker->randomElement([15, 30, 45, 60]),
            'duracion' => '30 minutos',
            'tipo_cita' => $this->faker->randomElement(['Presencial', 'Virtual']),
            'estado' => $this->faker->randomElement(['pendiente', 'confirmada', 'cancelada', 'atendida']),
            'motivo' => $this->faker->sentence(),
            'ubicacion' => $this->faker->randomElement(['Consultorio 1', 'Consultorio 2', 'Virtual']),
            'notas' => $this->faker->text(),
            'color' => '#3b82f6',
            'confirmada_paciente' => $this->faker->boolean(),
        ];
    }
}
