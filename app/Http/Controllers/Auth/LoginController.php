<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Inloggen voor de kapper.
 * De routes staan in routes/web.php onder /admin.
 */
class LoginController extends Controller
{
    public function show()
    {
        // Iemand die al ingelogd is, sturen we meteen naar het dashboard.
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'password.required' => 'Vul je wachtwoord in.',
        ]);

        $remember = $request->boolean('remember');

        // Auth::attempt zoekt de gebruiker en controleert het wachtwoord.
        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Deze combinatie van e-mailadres en wachtwoord is onjuist.',
                ]);
        }

        // Nieuw sessie-id, zodat een oud id niet hergebruikt kan worden.
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
