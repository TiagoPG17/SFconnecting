<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthWebController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        if (! Auth::attempt(array_merge($credentials, ['activo' => true]), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas o la cuenta está desactivada.',
            ]);
        }

        $request->session()->regenerate();

        $token = Auth::user()->createToken('web-session')->plainTextToken;
        $request->session()->put('api_token', $token);

        ActividadLog::registrar('login', 'Autenticación', 'Inicio de sesión exitoso');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        ActividadLog::registrar('logout', 'Autenticación', 'Cierre de sesión');
        Auth::user()?->tokens()->where('name', 'web-session')->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
