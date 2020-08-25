<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
{

    use AuthenticatesUsers;


    protected $redirectTo = RouteServiceProvider::HOME;

    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return response()->json([
                'success' => 'false',
                'errors' => [
                    'Превышено число попыток входа. Попробуйте через 30 минут.'
                ]
            ]);
        }

        $this->incrementLoginAttempts($request);

        // attempt login with token
        if ($request->input('token')) {
            $this->auth->setToken($request->input('token'));

            $user = $this->auth->authenticate();
            if ($user) {
                return response()->json([
                    'success' => true,
                    'data' => $request->user(),
                    'token' => $request->input('token')
                ], 200);
            }
        }

        try {
            if (!$token = $this->auth->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'email' => [
                            'Неверный email или пароль'
                        ]
                    ]
                ], 422);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e
            ], 422);
        }

//        return $this->sendFailedLoginResponse($request);
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        $this->auth->invalidate();
        return response()->json([
            'success' => true
        ], 200);
    }
}
