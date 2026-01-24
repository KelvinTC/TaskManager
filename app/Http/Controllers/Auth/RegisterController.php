<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InvitedUser;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Show the registration form with pre-filled data if invited.
     */
    public function showRegistrationForm(\Illuminate\Http\Request $request)
    {
        $phone = $request->query('phone');
        $invitedEmail = null;

        if ($phone) {
            $invited = InvitedUser::where('phone_number', $phone)
                ->where('registered', false)
                ->first();

            if ($invited) {
                $invitedEmail = $invited->email;
            }
        }

        return view('auth.register', compact('phone', 'invitedEmail'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:users',
                function ($attribute, $value, $fail) {
                    $invited = InvitedUser::where('phone_number', $value)
                        ->where('registered', false)
                        ->first();

                    if (!$invited) {
                        $fail('This phone number has not been invited to register. Please contact the administrator.');
                    }
                }
            ],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Get the invited user record by phone number
        $invited = InvitedUser::where('phone_number', $data['phone'])
            ->where('registered', false)
            ->first();

        // Use email from registration form, or fall back to invited user's email
        $email = $data['email'] ?? $invited->email;

        // Create the user with the role from the invitation
        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'role' => $invited->role,
            'preferred_channel' => 'whatsapp', // Always WhatsApp since phone is required
        ]);

        // Mark the invitation as registered
        $invited->markAsRegistered();

        return $user;
    }
}
