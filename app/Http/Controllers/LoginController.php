<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Método para redirigir a GitHub
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    // Método para manejar el callback de GitHub
    public function handleGitHubCallback()
    {
        try {
            // Obtener los datos del usuario desde GitHub
            $githubUser = Socialite::driver('github')->user();

            // Verificar si el usuario ya existe en la base de datos
            $authUser = User::where('github_id', $githubUser->getId())->first();

            if ($authUser) {
                // Si ya existe, iniciar sesión
                Auth::login($authUser);
            } else {
                // Si no existe, registrar un nuevo usuario
                $authUser = User::create([
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                    'github_id' => $githubUser->getId(),
                    'password' => bcrypt('default_password'), // Puedes cambiar esta lógica
                ]);
                // Iniciar sesión con el nuevo usuario
                Auth::login($authUser);
            }

            // Redirigir al dashboard después de iniciar sesión
            return redirect()->intended(route('dashboard')); // Redirige al dashboard
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error al iniciar sesión con GitHub: ' . $e->getMessage());
        }
    }

}
