<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
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
    protected $redirectTo = RouteServiceProvider::HOME;

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
            'usuario' => ['required'],
            'curp' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],[
            'name.required' => 'El nombre es requerido',
            'usuario.required' => 'El tipo de usuario es requerido',
            'curp.required' => 'La curp es requerida',
            'email.required' => 'El correo es requerido',
            'password.required' => 'La contraseña es requerida',
            'password.min:8' => 'La contraseña debe de tener un minimo de 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data, $id, $role)
    {
        return User::create([
            'name' => $data['name'],
            'tipo_usuario' => $data['usuario'],
            'curp' => $data['curp'],
            'id_sivyc' => $id,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ])->assignRole($role);
    }
}
