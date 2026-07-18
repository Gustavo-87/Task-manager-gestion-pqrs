<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de verificación</title>
</head>
<body style="margin: 0; padding: 30px; background-color: #f3f4f6; font-family: Arial, sans-serif; color: #1f2937;">
    <div style="max-width: 560px; margin: 0 auto; padding: 30px; background-color: #ffffff; border-radius: 12px;">

        <div style="text-align: center; margin-bottom: 24px;">
            <img
                src="{{ $message->embed(public_path('images/logo-gestion-pqrs.jpg')) }}"
                alt="Gestión de PQRS"
                style="width: 130px; max-height: 130px; object-fit: contain; border-radius: 12px;"
            >
        </div>

        <h2 style="text-align: center; margin-bottom: 24px;">
            Verificación de inicio de sesión
        </h2>

        <p>Hola {{ $user->name }},</p>

        <p>
            Tu código de verificación para ingresar al sistema de Gestión de PQRS es:
        </p>

        <div style="margin: 28px 0; text-align: center;">
            <span style="display: inline-block; padding: 14px 24px; background-color: #37452c; color: #ffffff; font-size: 28px; font-weight: bold; letter-spacing: 8px; border-radius: 8px;">
                {{ $otp }}
            </span>
        </div>

        <p>Este código es válido durante 5 minutos.</p>

        <p>
            Si no intentaste iniciar sesión, puedes ignorar este mensaje.
        </p>

        <hr style="margin: 28px 0; border: 0; border-top: 1px solid #e5e7eb;">

        <p style="text-align: center; font-size: 13px; color: #6b7280;">
            Sistema de Gestión de PQRS
        </p>

        <p style="text-align: center; font-size: 13px;">
            Correo de contacto:
            <a
                href="mailto:{{ config('mail.from.address') }}"
                style="color: #37452c;"
            >
                {{ config('mail.from.address') }}
            </a>
        </p>
    </div>
</body>
</html>
