<?php

namespace App\Http\Controllers;

use App\Mail\ConfigurationTestMail;
use App\Models\AppSetting;
use App\Models\TipoPqr;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ConfigurationController extends Controller
{
    public function index(): View
    {
        return view('configuration.index', [
            'settings' => AppSetting::current(),
            'categories' => TipoPqr::withCount('pqrs')->orderBy('nombre')->get(),
            'mailConfigured' => $this->mailConfigured(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'residential_name' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        $settings = AppSetting::current();
        $oldValues = $settings->only(array_keys($data));
        $settings->update($data);
        $changes = collect($settings->only(array_keys($data)))->filter(fn ($value, $key) => $oldValues[$key] !== $value)->all();

        if ($changes !== []) {
            AuditLogger::log($request, 'Configuración', 'actualizar', 'Actualizó los datos del conjunto residencial.', $settings,
                collect($changes)->mapWithKeys(fn ($value, $key) => [$key => $oldValues[$key]])->all(), $changes);
        }

        return back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        if (! $this->mailConfigured()) {
            return back()->withErrors(['mail' => 'Configura primero las credenciales SMTP de Gmail en el archivo .env.']);
        }

        try {
            Mail::to($request->user())->send(new ConfigurationTestMail(AppSetting::current()));
            AuditLogger::log($request, 'Configuración', 'probar_correo', "Envió un correo de prueba a {$request->user()->email}.");

            return back()->with('success', "Correo de prueba enviado a {$request->user()->email}.");
        } catch (Throwable $exception) {
            report($exception);
            AuditLogger::log($request, 'Configuración', 'fallo_correo', 'Falló la prueba de configuración de correo.');

            return back()->withErrors(['mail' => 'No fue posible enviar el correo de prueba. Revisa la configuración SMTP.']);
        }
    }

    private function mailConfigured(): bool
    {
        return config('mail.default') === 'smtp'
            && filled(config('mail.mailers.smtp.host'))
            && filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.mailers.smtp.password'))
            && filled(config('mail.from.address'));
    }
}
