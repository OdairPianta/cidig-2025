<?php

namespace App\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login from the slide-down panel of the public menu. It follows the same rules as the admin
 * login: valid credentials and an account allowed into the panel (not a pending sign-up request).
 */
class SiteLoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $failed = back()->withInput($request->only('email'))
            ->withErrors(['email' => 'E-mail ou senha incorretos.'], 'siteLogin');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return $failed;
        }

        if (! Auth::user()->canAccessPanel(Filament::getPanel('admin'))) {
            Auth::logout();

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Seu cadastro ainda aguarda autorização.'], 'siteLogin');
        }

        $request->session()->regenerate();

        return redirect()->back();
    }
}
