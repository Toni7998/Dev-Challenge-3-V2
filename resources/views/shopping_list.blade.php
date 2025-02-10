<x-app-layout>
    @section('title', 'Mis Listas de Compras')

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold text-center mb-8 text-gray-900 dark:text-white">Mis Listas de Compras</h1>

        <!-- Mostrar mensajes de éxito o error -->
        @if(session('success'))
            <div class="alert alert-success mb-4 p-4 text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-4 p-4 text-red-700 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Instrucciones -->
        <div
            class="text-center text-gray-700 dark:text-gray-300 mb-6 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-md">
            <p class="text-lg font-medium">
                Crea una lista de compras y añade elementos organizados por categorías. Puedes compartir tu lista con
                otros usuarios y marcar los artículos como completados.
            </p>
        </div>

        <!-- Formulario para agregar una nueva lista -->
        <form action="{{ route('shopping_list.add') }}" method="POST" class="mb-8 text-center">
            @csrf
            <input type="text" name="list_name"
                class="p-3 mb-4 w-3/4 sm:w-1/2 max-w-md border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                placeholder="Nombre de la lista" required>
            <button type="submit"
                class="bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition-colors">Agregar Lista</button>
        </form>

        <!-- Verificar si hay listas de compras -->
        @if(empty($shoppingLists))
            <p class="text-center text-gray-500">No tienes listas de compras. ¡Crea una nueva!</p>
        @else
            @foreach ($shoppingLists as $listId => $list)
                <div class="bg-white shadow-md rounded-lg mb-6 p-4 dark:bg-gray-800 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-xl font-semibold">{{ $list['name'] }}</h5>
                        <div class="flex items-center space-x-2">
                            <!-- Formulario para compartir la lista -->
                            <form action="{{ route('shopping_list.share', $listId) }}" method="POST"
                                class="flex items-center space-x-2">
                                @csrf
                                <input type="email" name="email" placeholder="Correo del usuario"
                                    class="p-2 border rounded-md w-40" required>
                                <button type="submit"
                                    class="bg-gray-300 text-gray-700 p-2 rounded-md hover:bg-gray-400 transition-colors">
                                    Compartir
                                </button>
                            </form>
                            <!-- Formulario para eliminar la lista -->
                            <form action="{{ route('shopping_list.delete', $listId) }}" method="POST"
                                class="delete-list-form flex items-center space-x-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors">Eliminar
                                    Lista</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-none">
                            @foreach ($list['items'] ?? [] as $itemId => $item)
                                <li class="flex justify-between items-center p-3 border-b border-gray-300 dark:border-gray-600">
                                    <span>{{ $item['name'] }} ({{ $item['category'] }})</span>
                                    <div class="flex items-center space-x-2">
                                        <!-- Botón para marcar como hecho -->
                                        <button
                                            class="done-btn px-4 py-2 rounded-md transition-colors w-32 {{ $item['done'] ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white' }}"
                                            data-item-id="{{ $itemId }}" data-list-id="{{ $listId }}"
                                            data-done="{{ $item['done'] ? 'true' : 'false' }}">
                                            {{ $item['done'] ? 'Hecho' : 'Marcar como hecho' }}
                                        </button>
                                        <!-- Formulario para eliminar el ítem -->
                                        <form action="{{ route('shopping_list.delete_item', [$listId, $itemId]) }}" method="POST"
                                            class="flex items-center space-x-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-32">Eliminar</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <!-- Formulario para agregar un nuevo ítem -->
                        <form action="{{ route('shopping_list.add_item', $listId) }}" method="POST" class="mt-4 flex space-x-4">
                            @csrf
                            <input type="text" name="item_name"
                                class="p-3 w-full sm:w-1/2 max-w-xs border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                placeholder="Nombre del ítem" required>
                            <input type="text" name="category"
                                class="p-3 w-full sm:w-1/2 max-w-xs border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                placeholder="Categoría" required>
                            <button type="submit"
                                class="bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition-colors">Agregar
                                Ítem</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.done-btn').forEach((button) => {
                button.addEventListener('click', async function (event) {
                    const btn = event.target;
                    const itemId = btn.getAttribute('data-item-id');
                    const listId = btn.getAttribute('data-list-id');
                    const currentState = btn.getAttribute('data-done') === 'true';
                    const newState = !currentState;
                    try {
                        let response = await fetch(`/shopping_list/${listId}/toggle_done/${itemId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ done: newState })
                        });
                        let result = await response.json();
                        if (result.success) {
                            btn.textContent = newState ? 'Hecho' : 'Marcar como hecho';
                            btn.setAttribute('data-done', newState.toString());
                            btn.classList.toggle('bg-green-500', newState);
                            btn.classList.toggle('bg-yellow-500', !newState);
                        }
                    } catch (error) {
                        alert('Error al actualizar el estado.');
                    }
                });
            });
        });
    </script>
</x-app-layout>