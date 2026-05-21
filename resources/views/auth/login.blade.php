<x-guest-layout>

<div 
    class="min-h-screen w-full flex items-center justify-center relative overflow-hidden bg-cover bg-center px-4 py-10"
    style="background-image: url('/images/hospital.jpg');"
>

    {{-- OVERLAY OSCURO --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>

    {{-- CONTENIDO --}}
    <div class="w-full max-w-5xl relative z-10">

        {{-- LOGO Y TITULO --}}
        <div class="text-center mb-10">

            {{-- ICONO --}}
           {{-- LOGO --}}
<div class="flex justify-center mb-6">

    <img
        src="{{ asset('images/logo.png') }}"
        alt="Logo Hospital"
        class="w-32 h-32 object-contain drop-shadow-[0_0_35px_rgba(34,197,94,0.7)] animate-pulse"
    >

</div>
            {{-- TITULO --}}
            <h1 class="text-6xl font-black text-white tracking-tight">
                MEDICO - ONLINE
            </h1>

            {{-- SUBTITULO --}}
            <p class="text-yellow-300 uppercase tracking-[6px] mt-4 text-sm">
                Plataforma tecnológica segura
            </p>

        </div>

        {{-- FORMULARIO --}}
        <div class="relative z-20 bg-white/10 backdrop-blur-2xl border border-green-400/20 rounded-[40px] shadow-2xl p-12 md:p-16">

            <form method="POST" action="{{ route('login') }}" class="space-y-10">

                @csrf

                <div class="grid md:grid-cols-2 gap-8">

                    {{-- EMAIL --}}
                    <div>

                        <label class="block text-sm font-semibold text-white mb-3">
                            Usuario
                        </label>

                        <div class="relative">

                            <span class="absolute left-0 pl-4 inset-y-0 flex items-center text-green-400 pointer-events-none">
                                👤
                            </span>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="usuario@correo.com"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-black/30 border border-green-400/30 text-white placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-green-400/30 focus:border-green-400 transition duration-300"
                            >

                        </div>

                    </div>

                    {{-- PASSWORD --}}
                    <div>

                        <label class="block text-sm font-semibold text-white mb-3">
                            Contraseña
                        </label>

                        <div class="relative">

                            <span class="absolute left-0 pl-4 inset-y-0 flex items-center text-yellow-400 pointer-events-none">
                                🔒
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-12 pr-14 py-4 rounded-2xl bg-black/30 border border-yellow-400/30 text-white placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-yellow-400/30 focus:border-yellow-400 transition duration-300 relative z-50 pointer-events-auto"
                            >

                            {{-- BOTON VER PASSWORD --}}
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-300 z-50"
                            >
                                👁
                            </button>

                        </div>

                    </div>

                </div>

                {{-- OPCIONES --}}
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">

                    <label class="text-slate-300 flex items-center gap-2">

                        <input
                            type="checkbox"
                            name="remember"
                            class="accent-green-500"
                        >

                        Mantener sesión activa

                    </label>

                    @if (Route::has('password.request'))

                        <a
                            href="{{ route('password.request') }}"
                            class="text-red-400 hover:text-red-300 hover:underline"
                        >
                            Recuperar acceso
                        </a>

                    @endif

                </div>

                {{-- BOTON LOGIN --}}
                
                    Acceder al sistema
              
<div class="d-grid gap-2">
  <button class="btn btn-warning text-2xl font-black text-white tracking-tight" type="button"> iniciar sesión</button>

  
</div>
            </form>

        </div>

    </div>

</div>

{{-- SCRIPT --}}
<script>

    function togglePassword() {

        const pass = document.getElementById('password');

        pass.type = pass.type === 'password'
            ? 'text'
            : 'password';
    }

</script>

</x-guest-layout>