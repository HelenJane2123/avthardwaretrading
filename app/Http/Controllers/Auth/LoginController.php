<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Rules\Recaptcha;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    protected $maxAttempts = 5; // Lock after 5 tries
    protected $decayMinutes = 1; // Lock for 1 minute

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out successfully.');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $seconds = $this->limiter()->availableIn($this->throttleKey($request));

            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ])->withInput($request->only('email', 'remember'));
        }

        if ($this->attemptLogin($request)) {
            $request->session()->regenerate();

            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => ['required', new Recaptcha()],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        return Auth::attempt($this->credentials($request), $request->filled('remember'));
    }

    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    protected function sendLoginResponse(Request $request)
    {
        return $this->authenticated($request, Auth::user()) ?: redirect()->intended($this->redirectPath());
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input($this->username())) . '|' . $request->ip();
    }

    protected function username()
    {
        return 'email';
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->password_reset_flag) {
            $user->password_reset_flag = false;
            $user->save();

            session()->flash('passwordResetNotice', 'Your password has been reset by the administrator. Please change your password for security.');
        }

        return redirect()->intended($this->redirectPath());
    }
    
}
