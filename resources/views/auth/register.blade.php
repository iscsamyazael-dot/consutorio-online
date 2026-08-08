<x-guest-layout>

<div 
    class="min-h-screen flex items-center justify-center bg-cover bg-center relative px-4 py-6 overflow-hidden"
    style="background-image: url('/images/hospital.jpg');"
>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]"></div>

    {{-- Glow --}}
    <div class="absolute w-[400px] h-[400px] bg-cyan-400/20 rounded-full blur-3xl top-[-100px] left-[-100px]"></div>

    {{-- Contenedor --}}
    <div class="relative z-10 w-full max-w-2xl">

        {{-- Header --}}
        <div class="text-center mb-6">

            {{-- Logo --}}
            <div class="flex justify-center mb-4">

                <div class="w-20 h-20 rounded-full bg-white shadow-2xl flex items-center justify-center border-4 border-white/30">

                    <img 
                        src="/images/logo.png"
                        class="w-14 h-14 object-contain"
                    >

                </div>

            </div>

            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-wide">
                REGISTRO MÉDICO
            </h1>

            <p class="text-cyan-300 tracking-[5px] mt-3 uppercase text-xs font-semibold">
                Plataforma tecnológica segura
            </p>

        </div>

        {{-- Card --}}
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[30px] shadow-2xl p-6 md:p-8">

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="grid md:grid-cols-2 gap-5">

                    {{-- Nombre --}}
                    <div>

                        <label class="block text-white font-semibold mb-2 text-sm">
                            Nombre Completo
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Ingresa tu nombre"
                            class="w-full px-4 py-3 rounded-2xl bg-black/30 border border-cyan-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400/40 outline-none"
                        >

                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400 text-sm" />

                    </div>

                    {{-- Email --}}
                    <div>

                        <label class="block text-white font-semibold mb-2 text-sm">
                            Correo Electrónico
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            placeholder="correo@ejemplo.com"
                            class="w-full px-4 py-3 rounded-2xl bg-black/30 border border-cyan-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400/40 outline-none"
                        >

                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400 text-sm" />

                    </div>

                    {{-- Password --}}
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
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 rounded-2xl bg-black/30 border border-cyan-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400/40 outline-none"
                            >

                            <button
                                type="button"
                                onclick="togglePassword('password')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-sm"
                            >
                                👁
                            </button>

                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400 text-sm" />

                    </div>

                    {{-- Confirmar Password --}}
                    <div>

                        <label class="block text-white font-semibold mb-2 text-sm">
                            Confirmar Contraseña
                        </label>

                        <div class="relative">

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 rounded-2xl bg-black/30 border border-cyan-400 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400/40 outline-none"
                            >

                            <button
                                type="button"
                                onclick="togglePassword('password_confirmation')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-sm"
                            >
                                👁
                            </button>

                        </div>

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400 text-sm" />

                    </div>

                </div>

                {{-- Botón --}}
                <button
                    type="submit"
                    class="w-full mt-7 bg-cyan-400 hover:bg-cyan-300 transition-all duration-300 text-black font-bold py-3 rounded-2xl text-lg shadow-[0_0_20px_rgba(34,211,238,0.4)]"
                >
                    Crear Cuenta
                </button>

                {{-- Login --}}
                <div class="text-center mt-5">

                    <a 
                        href="{{ route('login') }}"
                        class="text-sm text-white hover:text-cyan-300 transition"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- Mostrar contraseña --}}
<script>

function togglePassword(id) {

    const input = document.getElementById(id);

    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

</script>

</x-guest-layout>