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
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                function ($attribute, $value, $fail) {
                    $invited = InvitedUser::where('email', $value)
                        ->where('registered', false)
                        ->first();

                    if (!$invited) {
                        $fail('This email has not been invited to register. Please contact the administrator.');
                    }
                }
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
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
        // Get the invited user record
        $invited = InvitedUser::where('email', $data['email'])
            ->where('registered', false)
            ->first();

        // Use phone from registration form, or fall back to invited user's phone
        $phone = $data['phone'] ?? $invited->phone_number;

        // Set preferred channel to whatsapp if phone is provided
        $preferredChannel = !empty($phone) ? 'whatsapp' : 'in_app';

        // Create the user with the role from the invitation
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $phone,
            'role' => $invited->role,
            'preferred_channel' => $preferredChannel,
        ]);

        // Mark the invitation as registered
        $invited->markAsRegistered();

        return $user;
    }
}
