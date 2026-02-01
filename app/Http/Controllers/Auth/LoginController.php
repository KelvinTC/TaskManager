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

        $username = $credentials['email'] ?? null;
        $plain = $credentials['password'] ?? null;

        if ($username && $plain) {
            // Normalize the username - remove spaces
            $username = preg_replace('/\s+/', '', $username);

            // Check if username looks like a phone number
            if (preg_match('/^\+?[0-9]{9,15}$/', $username)) {
                // Normalize phone number formats
                $normalizedPhone = $this->normalizePhoneNumber($username);

                // Try to find user by normalized phone number
                $user = User::where('phone', $normalizedPhone)->first();

                if (!$user) {
                    // Try original input
                    $user = User::where('phone', $username)->first();
                }

                if (!$user) {
                    // Try with/without + prefix
                    $phoneVariant = str_starts_with($normalizedPhone, '+')
                        ? substr($normalizedPhone, 1)
                        : '+' . $normalizedPhone;
                    $user = User::where('phone', $phoneVariant)->first();
                }
            } else {
                // Find by email
                $user = User::where('email', $username)->first();
            }

            if ($user) {
                $stored = (string) $user->password;
                // If the stored password is not a bcrypt hash (starts with $2y$),
                // and the submitted password matches exactly, rehash and save.
                if (!preg_match('/^\$2y\$/', $stored)) {
                    if (hash_equals($stored, (string) $plain)) {
                        $user->password = bcrypt($plain);
                        $user->save();
                    } else {
                        return false;
                    }
                }

                // Attempt login with the user's actual email
                $credentials['email'] = $user->email;
            }
        }

        try {
            return $this->guard()->attempt(
                $credentials,
                $request->boolean('remember')
            );
        } catch (\RuntimeException $e) {
            report($e);
            return false;
        }
    }

    /**
     * Normalize phone number to standard format (+263...).
     *
     * Handles formats:
     * - 0783017279 → +263783017279
     * - + 263783017279 → +263783017279
     * - +263783017279 → +263783017279
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all spaces
        $phone = preg_replace('/\s+/', '', $phone);

        // If starts with 0, replace with +263
        if (str_starts_with($phone, '0')) {
            return '+263' . substr($phone, 1);
        }

        // If starts with 263 (no +), add +
        if (str_starts_with($phone, '263')) {
            return '+' . $phone;
        }

        // If already has +, return as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        return $phone;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
