<x-app-layout>
    <style>
        /* Estilos generales */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .title {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Tarjetas */
        .card {
            background: #2a3b47;
            border: 1px solid #2a3b47;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 400px;
            overflow: hidden;
            color:rgb(216, 216, 216);
        }

        .card-header {
            background-color: #1a252f;
            border-bottom: 1px solid #1a252f;
            padding: 12px 16px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
            color:rgb(255, 255, 255);
        }

        .card-body {
            padding: 16px;
        }

        .card-body h6 {
            margin: 0 0 10px;
            font-size: 1rem;
            color:rgb(216, 216, 216);
        }

        .card-body p {
            margin: 0 0 10px;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #3182ce;
            color:rgb(255, 255, 255);
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
            text-align: center;
        }

        .btn:hover {
            background-color: #2b6cb0;
        }
    </style>

    <div class="container mx-auto px-4 py-6">
        <h1 class="title">Bienvenido, {{ $user->name }}</h1>

        <div class="dashboard flex flex-col md:flex-row gap-6 justify-center">
            <!-- Tarjeta de Información del Usuario -->
            <div class="card user-info">
                <div class="card-header">
                    <h5>Información del Usuario</h5>
                </div>
                <div class="card-body text-center">
                    <h6>Nombre: <strong>{{ $user->name }}</strong></h6>
                    <p>Correo: <strong>{{ $user->email }}</strong></p><br>
                    <a href="{{ route('profile.edit') }}" class="btn">Editar Perfil</a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>