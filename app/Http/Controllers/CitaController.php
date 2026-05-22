<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $citas = Cita::with(['paciente', 'medico'])
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->when($request->filled('fecha'), fn ($query) => $query->whereDate('fecha', $request->fecha))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('agenda.index', [
            'citas' => $citas,
            'nuevaCita' => new Cita(['estado' => 'programada']),
            'pacientes' => Paciente::orderBy('nombre')->get(),
            'medicos' => User::orderBy('name')->get(),
            'estados' => $this->estados(),
        ]);
    }

    public function create()
    {
        return view('agenda.create', [
            'cita' => new Cita(['estado' => 'programada']),
            'pacientes' => Paciente::orderBy('nombre')->get(),
            'medicos' => User::orderBy('name')->get(),
            'estados' => $this->estados(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['paciente_id'] = $this->resolvePacienteId($data);
        $data['user_id'] = $this->resolveMedicoId($data);

        Cita::create($data);

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita programada correctamente.');
    }

    public function show(Cita $agenda)
    {
        $agenda->load(['paciente', 'medico']);

        return view('agenda.show', ['cita' => $agenda]);
    }

    public function edit(Cita $agenda)
    {
        return view('agenda.edit', [
            'cita' => $agenda,
            'pacientes' => Paciente::orderBy('nombre')->get(),
            'medicos' => User::orderBy('name')->get(),
            'estados' => $this->estados(),
        ]);
    }

    public function update(Request $request, Cita $agenda)
    {
        $data = $this->validatedData($request);
        $data['paciente_id'] = $this->resolvePacienteId($data);
        $data['user_id'] = $this->resolveMedicoId($data);

        $agenda->update($data);

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(Cita $agenda)
    {
        $agenda->delete();

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita eliminada correctamente.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'paciente_id' => ['nullable', 'exists:pacientes,id'],
            'paciente_nombre' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
            'medico_nombre' => ['nullable', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'in:programada,confirmada,atendida,cancelada'],
            'notas' => ['nullable', 'string'],
        ]);
    }

    private function resolvePacienteId(array $data): int
    {
        if (! empty($data['paciente_id'])) {
            return (int) $data['paciente_id'];
        }

        $paciente = Paciente::firstOrCreate(
            ['nombre' => trim($data['paciente_nombre'])],
            ['estado' => 'activo']
        );

        return $paciente->id;
    }

    private function resolveMedicoId(array $data): ?int
    {
        if (! empty($data['user_id'])) {
            return (int) $data['user_id'];
        }

        if (empty(trim($data['medico_nombre'] ?? ''))) {
            return null;
        }

        $name = trim($data['medico_nombre']);
        $emailBase = Str::slug($name) ?: 'medico';
        $email = $emailBase.'@consultorio.local';
        $counter = 2;

        while (User::where('email', $email)->exists()) {
            $email = $emailBase.$counter.'@consultorio.local';
            $counter++;
        }

        $medico = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(16)),
            'role' => 'medico',
        ]);

        return $medico->id;
    }

    private function estados(): array
    {
        return [
            'programada' => 'Programada',
            'confirmada' => 'Confirmada',
            'atendida' => 'Atendida',
            'cancelada' => 'Cancelada',
        ];
    }
}
