<x-layouts.guest title="Iniciar sesión — SFconnecting">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-slate-100 px-8 py-10">

        {{-- Logo mobile --}}
        <div class="flex lg:hidden items-center gap-2 mb-10">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-semibold text-slate-900 text-lg">SFconnecting</span>
        </div>

        {{-- Encabezado --}}
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Iniciar sesión</h2>
            <p class="mt-1.5 text-sm text-slate-500">Accede con tus credenciales corporativas</p>
        </div>

        {{-- Formulario --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-5"
              x-data="{ loading: false }" @submit="loading = true">
            @csrf

            {{-- Email --}}
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-slate-700">
                    Correo electrónico
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    required
                    placeholder="tu@empresa.co"
                    class="block w-full rounded-xl border px-4 py-2.5 text-sm text-slate-900 bg-white
                           placeholder:text-slate-400 transition-all duration-150
                           focus:outline-none focus:ring-2 focus:border-transparent
                           {{ $errors->has('email') ? 'border-red-400 focus:ring-red-400' : 'border-slate-200 focus:ring-blue-500' }}"
                >
                @error('email')
                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div class="space-y-1.5" x-data="{ show: false }">
                <label class="block text-sm font-medium text-slate-700">
                    Contraseña
                </label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        name="password"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                        class="block w-full rounded-xl border px-4 py-2.5 pr-11 text-sm text-slate-900 bg-white
                               placeholder:text-slate-400 transition-all duration-150
                               focus:outline-none focus:ring-2 focus:border-transparent
                               {{ $errors->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-slate-200 focus:ring-blue-500' }}"
                    >
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors"
                        :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    >
                        {{-- Ojo abierto --}}
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{-- Ojo tachado --}}
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Error general --}}
            @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Botón --}}
            <button
                type="submit"
                x-bind:disabled="loading"
                class="relative w-full flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold
                       text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                       transition-all duration-150 shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed mt-2"
            >
                <svg x-show="loading" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="loading ? 'Verificando...' : 'Iniciar sesión'"></span>
            </button>
        </form>

    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        © {{ date('Y') }} Sistemas &middot; Uso corporativo exclusivo
    </p>
</x-layouts.guest>
