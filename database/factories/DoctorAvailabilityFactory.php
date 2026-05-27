<?php

namespace Database\Factories;

use App\Models\DoctorAvailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorAvailabilityFactory extends Factory
{
    protected $model = DoctorAvailability::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dia_semana' => $this->faker->numberBetween(0, 4), // Lunes a Viernes
            'hora_inicio' => '09:00:00',
            'hora_fin' => '17:00:00',
            'activo' => true,
        ];
    }
}
