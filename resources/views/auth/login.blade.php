<!DOCTYPE html>
@php($tabLogo = $siteSettings->logo_path ? Storage::url($siteSettings->logo_path) : asset('logo-resuelve.png'))
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#12382f">
    <link rel="icon" href="{{ $tabLogo }}?v={{ $siteSettings->updated_at?->timestamp ?? 1 }}">
    <link rel="apple-touch-icon" href="{{ $tabLogo }}?v={{ $siteSettings->updated_at?->timestamp ?? 1 }}">
    <title>Iniciar sesión · {{ $siteSettings->nombre_conjunto }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page" style="--forest: {{ $siteSettings->color_principal }}">
    <main class="auth-shell">
        <section class="auth-brand-panel">
            <a class="brand auth-brand" href="/"><span class="brand-mark image-mark"><img src="{{ $siteSettings->logo_path ? Storage::url($siteSettings->logo_path) : asset('logo-resuelve.png') }}" alt=""></span><span><strong>Resuelve</strong><small>{{ $siteSettings->nombre_conjunto }}</small></span></a>
            <div class="auth-message">
                <span class="eyebrow light">PQRS para propiedad horizontal</span>
                <h1>Cada solicitud merece una respuesta clara y oportuna.</h1>
                <p>Administra peticiones, quejas, reclamos y sugerencias de tu conjunto residencial desde un solo lugar.</p>
            </div>
            <div class="auth-benefits"><span>✓ Seguimiento centralizado</span><span>✓ Control de vencimientos</span><span>✓ Información protegida</span></div>
        </section>
        <section class="auth-form-panel">
            <div class="auth-form-wrap">
                <span class="eyebrow">Acceso seguro</span>
                <h2>Bienvenido de nuevo</h2>
                <p>Ingresa con el correo asignado a tu perfil.</p>

                @if(session('success'))<div class="notice success" role="status"><span>✓</span>{{ session('success') }}</div>@endif

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf
                    <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nombre@correo.com" autocomplete="username" required autofocus class="@error('email') invalid @enderror">@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="password">Contraseña</label><div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.7"/></svg><svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.1A9 9 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.1 2.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6a9 9 0 0 0 3-.5"/></svg></button></div></div>
                    <div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1"> Mantener mi sesión iniciada</label><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></div>
                    <button type="submit" class="button primary login-button">Iniciar sesión <span>→</span></button>
                </form>
                <p class="security-note"><span>⌾</span> Tus datos de acceso están protegidos.</p>
            </div>
        </section>
    </main>
</body>
</html>
