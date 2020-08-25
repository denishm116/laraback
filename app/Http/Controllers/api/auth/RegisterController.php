<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;

class RegisterController extends Controller
{

    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;
    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     *  @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if (!$validator->fails()) {
            $user = $this->create($request->all());
            $token = $this->auth->attempt($request->only('email', 'password'));

            return response()->json([
                'success' => true,
                'data' => $user,
                'token' => $token

            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
