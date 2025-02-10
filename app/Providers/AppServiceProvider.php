<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Database::class, function ($app) {
            // Ruta del archivo de credenciales de Firebase
            $serviceAccountPath = storage_path('app/firebase/m12-proyecto.json');

            // Obtener la URL de la base de datos desde el archivo .env
            $databaseUrl = 'https://m12-proyecto-default-rtdb.europe-west1.firebasedatabase.app/';

            // Verificar si el archivo de credenciales existe
            if (!file_exists($serviceAccountPath)) {
                throw new \Exception("El archivo de credenciales de Firebase no se encontró en: $serviceAccountPath");
            }

            // Verificar si la URL de la base de datos está configurada correctamente
            if (empty($databaseUrl)) {
                throw new \Exception("La URL de la base de datos de Firebase no está configurada en el archivo .env.");
            }

            // Crear la instancia de Factory con las credenciales y la URL de la base de datos
            $factory = (new Factory)
                ->withServiceAccount($serviceAccountPath)
                ->withDatabaseUri($databaseUrl);

            // Crear y devolver la instancia de Database
            return $factory->createDatabase();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Puedes agregar cualquier otra configuración global aquí
    }
}
