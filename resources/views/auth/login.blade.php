<x-guest-layout>

<div 
    class="min-h-screen flex items-center justify-center bg-cover bg-center relative px-4 py-6 overflow-hidden"
    style="background-image: url('/images/2h.jpg');"
>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]"></div>

    {{-- Glow animado --}}
    <div class="absolute w-[400px] h-[400px] bg-yellow-400/20 rounded-full blur-3xl top-[-100px] right-[-100px] animate-pulse"></div>

    {{-- Contenedor principal --}}
    <div class="relative z-10 w-full max-w-2xl animate-fadeIn">

        {{-- Header --}}
        <div class="text-center mb-6">

            {{-- Logo --}}
            <div class="flex justify-center mb-4">

                <div class="w-20 h-20 rounded-full bg-white shadow-2xl flex items-center justify-center border-4 border-white/30 animate-pulseSlow hover:scale-110 transition-all duration-500">

                    <img 
                        src="/images/logo.png"
                        class="w-14 h-14 object-contain"
                    >

                </div>

            </div>

            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-wide drop-shadow-lg">
                MÉDICO ONLINE
            </h1>

            <p class="text-yellow-300 tracking-[5px] mt-3 uppercase text-xs font-semibold">
                Plataforma tecnológica segura
            </p>

        </div>

        {{-- Card --}}
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[30px] shadow-2xl p-6 md:p-8 hover:shadow-yellow-400/30 hover:scale-[1.01] transition-all duration-500">

            {{-- Estado de sesión --}}
            <x-auth-session-status class="mb-4 text-green-400" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="grid md:grid-cols-2 gap-5">

                    {{-- Usuario --}}
                    <div>

                        <label class="block text-white font-semibold mb-2 text-sm">
                            Usuario
                        </label>

                        <div class="relative">

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-yellow-300">
                                👤
                            </span>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@consultorio.com"
                                class="w-full pl-12 pr-4 py-3 rounded-2xl bg-black/30 border border-yellow-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-yellow-400/40 outline-none transition-all duration-300 focus:scale-[1.02] hover:border-yellow-300"
                            >

                        </div>

                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400 text-sm" />

                    </div>

                    {{-- Contraseña --}}
                    <div>

                        <label class="block text-white font-semibold mb-2 text-sm">
                            Contraseña
                        </label>

                        <div class="relative">

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 rounded-2xl bg-black/30 border border-yellow-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-yellow-400/40 outline-none transition-all duration-300 focus:scale-[1.02] hover:border-yellow-300"
                            >

                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-sm hover:scale-125 transition"
                            >
                                👁
                            </button>

                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400 text-sm" />

                    </div>

                </div>

                {{-- Extras --}}
                <div class="flex flex-col md:flex-row items-center justify-between mt-6 gap-3">

                    <label class="flex items-center gap-2 text-white text-sm">

                        <input 
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-yellow-400 shadow-sm focus:ring-yellow-400"
                        >

                        Mantener sesión activa

                    </label>

                    @if (Route::has('password.request'))
                        <a 
                            href="{{ route('password.request') }}"
                            class="text-sm text-red-300 hover:text-red-200 transition hover:underline"
                        >
                            Recuperar acceso
                        </a>
                    @endif

                </div>

                {{-- Botón --}}
                <button
                    type="submit"
                    class="w-full mt-7 bg-yellow-400 hover:bg-yellow-300 hover:scale-[1.02] active:scale-95 transition-all duration-300 text-black font-bold py-3 rounded-2xl text-lg shadow-[0_0_20px_rgba(250,204,21,0.4)] animate-glow"
                >
                    Iniciar sesión
                </button>

                {{-- Registro --}}
                <div class="text-center mt-5">

                    <a 
                        href="{{ route('register') }}"
                        class="text-sm text-white hover:text-yellow-300 transition"
                    >
                        ¿No tienes cuenta? Regístrate
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- Mostrar contraseña --}}
<script>

function togglePassword() {

    const input = document.getElementById('password');

    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

</script>

{{-- Animaciones --}}
<style>

@keyframes fadeIn {

    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 1s ease;
}

@keyframes pulseSlow {

    0%, 100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }
}

.animate-pulseSlow {
    animation: pulseSlow 3s infinite;
}

@keyframes glow {

    0%, 100% {
        box-shadow: 0 0 20px rgba(250,204,21,0.4);
    }

    50% {
        box-shadow: 0 0 35px rgba(250,204,21,0.8);
    }
}

.animate-glow {
    animation: glow 2s infinite;
}

</style>

</x-guest-layout>