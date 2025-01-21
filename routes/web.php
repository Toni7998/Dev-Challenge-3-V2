<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\FirebaseController;
/*
|---------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y están 
| dentro del grupo de middleware "web".
|
*/

// Ruta principal de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación de Google
Route::get('/google-auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/google-auth/callback', function () {
    $user_google = Socialite::driver('google')->stateless()->user();

    $user = User::updateOrCreate([
        'email' => $user_google->email,
    ], [
        'name' => $user_google->name,
        'email' => $user_google->email,
    ]);

    Auth::login($user, true);

    return redirect('/dashboard');
});

// Ruta del dashboard (usando la Opción 1, pasando el usuario directamente desde la ruta)
Route::get('/dashboard', function () {
    $user = auth()->user();  // Obtén el usuario autenticado
    $recentActivities = []; // Aquí deberías agregar la lógica para obtener las actividades recientes
    return view('dashboard', compact('user', 'recentActivities'));
})->middleware(['auth'])->name('dashboard');


// Rutas de perfil, protegidas por middleware de autenticación
Route::middleware('auth')->group(function () {
    // Ruta para mostrar el perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');  // Esta línea es nueva

    // Ruta para mostrar el formulario de edición
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // Ruta para actualizar el perfil
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Ruta para eliminar el perfil
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//  Rutas SSO Github
Route::get('auth/github', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    // Buscar usuario por email o crear uno nuevo
    $user = User::updateOrCreate(
        ['email' => $githubUser->getEmail()],
        [
            'name' => $githubUser->getName() ?? $githubUser->getNickname(),
            'email' => $githubUser->getEmail(),
            'github_id' => $githubUser->getId(),
            'avatar' => $githubUser->getAvatar(),
        ]
    );

    Auth::login($user);

    return redirect('/dashboard');
});


// Cargar rutas adicionales de autenticación
require __DIR__ . '/auth.php';

//  Listas
Route::get('/shopping_list', [ShoppingListController::class, 'index'])->name('shopping_list.index');
Route::post('/shopping_list/add', [ShoppingListController::class, 'addItem'])->name('shopping_list.add');
Route::post('/shopping_list/add_category', [ShoppingListController::class, 'addCategory'])->name('shopping_list.add_category');
Route::post('/shopping_list/delete', [ShoppingListController::class, 'deleteItem'])->name('shopping_list.delete');

//Ruta para el firebase
Route::get('/firebase/store', [FirebaseController::class, 'storeData']);
Route::get('/firebase/get', [FirebaseController::class, 'getData']);
