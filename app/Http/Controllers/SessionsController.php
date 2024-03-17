<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class SessionsController extends Controller
{
    public function create()
    {
        $showCaptcha = session()->get('login_attempts', 0) >= 3;
        return view('auth.login', compact('showCaptcha'));
    }

    public function store(Request $request)
    {
        $loginAttempts = session()->get('login_attempts', 0);
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if ($loginAttempts >= 3) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $credentials = $request->validate($rules);

        $remember = $request->has('rememberMe');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->forget('login_attempts');
           return redirect()->intended(RouteServiceProvider::HOME);
        }

        session()->put('login_attempts', $loginAttempts + 1);

        return back()->withErrors(['email' => "Your emails or passwords don't match."]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with(['success' => 'You have logged out of the system.']);
    }
}