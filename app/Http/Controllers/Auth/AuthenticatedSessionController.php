<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $remember = $request->boolean('remember');

        $otp = $user->generateOtp();

        Mail::send(
            'emails.otp',
            [
                'otp' => $otp,
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Código de verificación');
            }
        );

        Auth::guard('web')->logout();

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_remember', $remember);

        return redirect()->route('otp.verify');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        AuditLogger::log(
            $request,
            'Autenticación',
            'cerrar_sesion',
            'Cerró sesión en el sistema.'
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
