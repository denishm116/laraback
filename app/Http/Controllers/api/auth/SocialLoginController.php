<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocial;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\JWTAuth;

class SocialLoginController extends Controller
{
    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;

//        $this->middleware('social');
    }

    public function redirect($service)
    {
        return Socialite::driver($service)->stateless()->redirect();

    }

    public function callback($service)
    {
//        $serviceUser = Socialite::driver($service)->user();
//        dd($serviceUser);

        try {
            $serviceUser = Socialite::driver($service)->stateless()->user();
//            dd($serviceUser);
        } catch (\Exception $e) {
            return redirect(env('CLIENT_BASE_URL') . 'auth/social-callback?error=Unable to login using ' . $service . '. Please try again' . '&origin=login');
        }

        if ((env('RETRIEVE_UNVERIFIED_SOCIAL_EMAIL') == 0) && ($service != 'google') && ($service != 'yandex')) {
            $email = $serviceUser->getId() . '@' . $service . '.local';
        } else {
            $email = $serviceUser->getEmail();
        }

        $user = $this->getExistingUser($serviceUser, $email, $service);
        $newUser = false;
        if (!$user) {
            $newUser = true;
            $user = User::create([
                'name' => $serviceUser->getName(),
                'email' => $email,
                'password' => ''
            ]);
        }

        if ($this->needsToCreateSocial($user, $service)) {
            UserSocial::create([
                'user_id' => $user->id,
                'social_id' => $serviceUser->getId(),
                'service' => $service
            ]);
        }

//        dd(env('CLIENT_BASE_URL') . '/auth/social-callback?token=' . $this->auth->fromUser($user) . '&origin=' . ($newUser ? 'register' : 'login'));
        return redirect(env('CLIENT_BASE_URL') . '/auth/social-callback?token=' . $this->auth->fromUser($user) . '&origin=' . $user);
    }

    public function needsToCreateSocial(User $user, $service)
    {
        return !$user->hasSocialLinked($service);
    }

    public function getExistingUser($serviceUser, $email, $service)
    {
        if ((env('RETRIEVE_UNVERIFIED_SOCIAL_EMAIL') == 0) && ($service != 'google')  && ($service != 'yandex')) {
            $userSocial = UserSocial::where('social_id', $serviceUser->getId())->first();
            return $userSocial ? $userSocial->user : null;
        }
        return User::where('email', $email)->orWhereHas('social', function ($q) use ($serviceUser, $service) {
            $q->where('social_id', $serviceUser->getId())->where('service', $service);
        })->first();
    }
}
