<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold text-gray-900">
            Verificación de seguridad
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            @if ($usesFixedOtp)
                Ingresa el código de acceso de demostración.
            @else
                Enviamos un código de 6 dígitos al correo:
            @endif
        </p>

        @unless ($usesFixedOtp)
            <p class="mt-1 text-sm font-semibold text-gray-800">
                {{ $email }}
            </p>
        @endunless

        @unless ($usesFixedOtp)
            <p class="mt-2 text-sm text-gray-600">
                El código será válido durante 5 minutos.
            </p>
        @endunless
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify.post') }}">
        @csrf

        <div>
            <x-input-label
                for="code"
                value="Código de verificación"
            />

            <x-text-input
                id="code"
                class="mt-1 block w-full text-center text-xl tracking-[0.4em]"
                type="text"
                name="code"
                :value="old('code')"
                required
                autofocus
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                pattern="[0-9]{5,6}"
            />

            <x-input-error
                :messages="$errors->get('code')"
                class="mt-2"
            />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Verificar código
            </x-primary-button>
        </div>
    </form>

    @unless ($usesFixedOtp)
        <form
            method="POST"
            action="{{ route('otp.resend') }}"
            class="mt-4 text-center"
        >
            @csrf

            <button
                type="submit"
                class="text-sm font-medium text-gray-600 underline hover:text-gray-900"
            >
                Reenviar código
            </button>
        </form>
    @endunless

    <div class="mt-4 text-center">
        <a
            href="{{ route('login') }}"
            class="text-sm text-gray-600 underline hover:text-gray-900"
        >
            Volver al inicio de sesión
        </a>
    </div>
</x-guest-layout>
