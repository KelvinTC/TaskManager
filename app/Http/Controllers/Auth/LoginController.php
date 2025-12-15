<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Attempt to log the user into the application.
     *
     * This override self-heals any legacy/plaintext passwords by rehashing them
     * on first successful login with the correct plaintext password. This
     * prevents RuntimeException from the Bcrypt hasher when encountering
     * non-bcrypt values in the database.
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);

        // Only handle the conventional email login case
        $email = $credentials['email'] ?? null;
        $plain = $credentials['password'] ?? null;

        if ($email && $plain) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $stored = (string) $user->password;
                // If the stored password is not a bcrypt hash (starts with $2y$),
                // and the submitted password matches exactly, rehash and save.
                if (!preg_match('/^\$2y\$/', $stored)) {
                    if (hash_equals($stored, (string) $plain)) {
                        $user->password = bcrypt($plain);
                        $user->save();
                    }
                }
            }
        }

        return $this->guard()->attempt(
            $credentials,
            $request->boolean('remember')
        );
    }
}
