<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class TwitterController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleProviderCallback()
    {
        $user = Socialite::driver('twitter')->user();

        // Aquí puedes manejar la lógica para autenticar o registrar al usuario en tu sistema
        $authUser = User::where('twitter_id', $user->getId())->first();

        if ($authUser) {
            Auth::login($authUser);
        } else {
            // Registra un nuevo usuario si no existe
            $authUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(), // Twitter no siempre devuelve el email
                'twitter_id' => $user->getId(),
                'avatar' => $user->getAvatar(),
                // Otros campos necesarios
            ]);

            Auth::login($authUser);
        }

        return redirect()->intended('dashboard'); // Redirige a donde necesites
    }
}
