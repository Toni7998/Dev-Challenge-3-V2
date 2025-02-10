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
Route::middleware('auth')->group(function () {
    Route::get('/shopping-list', [ShoppingListController::class, 'index'])->name('shopping_list.index');

    // Ruta para agregar una lista
    Route::post('/shopping-list/add', [ShoppingListController::class, 'addList'])->name('shopping_list.add');

    // Ruta para agregar un ítem a una lista
    Route::post('/shopping-list/{listId}/item', [ShoppingListController::class, 'addItem'])->name('shopping_list.add_item');

    Route::delete('/shopping-list/{listId}/item/{itemId}', [ShoppingListController::class, 'deleteItem'])->name('shopping_list.delete_item');
    Route::post('/shopping-list/{listId}/share', [ShoppingListController::class, 'shareList'])->name('shopping_list.share');
    Route::post('/shopping_list/{listId}/toggle_done/{itemId}', [ShoppingListController::class, 'toggleDone'])->name('shopping_list.toggle_done');
    Route::delete('/shopping-list/{listId}', [ShoppingListController::class, 'delete'])->name('shopping_list.delete');
    Route::get('/shopping-list/accept/{ownerId}/{listId}', [ShoppingListController::class, 'acceptShare'])
        ->name('shopping_list.accept');

});

//Ruta para el firebase
Route::get('/firebase/store', [FirebaseController::class, 'storeData']);
Route::get('/firebase/get', [FirebaseController::class, 'getData']);
