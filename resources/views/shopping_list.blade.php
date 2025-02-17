<x-app-layout>
    @section('title', 'Mis Listas de Compras')

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold text-center mb-8 text-gray-900 dark:text-white">Mis Listas de Compras</h1>

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

        <div class="mb-8 text-center">
            <!-- Formulario visible por defecto -->
            <form id="add-list-form" action="{{ route('shopping_list.add') }}" method="POST" class="mt-4 text-center">
                @csrf
                <input type="text" name="list_name"
                    class="p-3 mb-4 w-3/4 sm:w-1/2 max-w-md border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                    placeholder="Nombre de la lista" required>
                <button type="submit"
                    class="bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition-colors">Agregar
                    Lista</button>
            </form>
        </div>

        @if(empty($shoppingLists))
            <p class="text-center text-gray-500">No tienes listas de compras. ¡Crea una nueva!</p>
        @else
            <div class="flex flex-wrap gap-6 justify-center items-start">
                @foreach ($shoppingLists as $listId => $list)
                    <div
                        class="bg-white shadow-md rounded-lg p-4 dark:bg-gray-800 dark:text-gray-100 w-96 min-w-[320px] max-w-[420px]">
                        <div class="mb-4">
                            <h5 class="text-xl font-semibold cursor-pointer toggle-list" data-list-id="{{ $listId }}">
                                {{ $list['name'] }}
                                <span class="text-gray-500 text-sm">({{ count($list['items'] ?? []) }} artículos)</span>
                            </h5>
                        </div>

                        <div class="flex items-center justify-between mb-4 space-x-4">
                            <form action="{{ route('shopping_list.share', $listId) }}" method="POST"
                                class="flex items-center space-x-2 w-full">
                                @csrf
                                <input type="email" name="email" placeholder="Correo del usuario"
                                    class="p-2 border rounded-md w-full bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    required>
                                <button type="submit"
                                    class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 transition-colors">
                                    🔗
                                </button>
                            </form>

                            <form action="{{ route('shopping_list.delete', $listId) }}" method="POST" class="delete-list-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors"
                                    onclick="return confirmDeletion('¿Estás seguro de que deseas eliminar esta lista?')">
                                    🗑
                                </button>
                            </form>
                        </div>

                        <div id="list-content-{{ $listId }}" class="hidden transition-all duration-300">
                            <ul class="space-y-3">
                                @foreach ($list['items'] ?? [] as $itemId => $item)
                                    <li
                                        class="flex justify-between items-center p-3 bg-gray-100 dark:bg-gray-700 rounded-md shadow-md">
                                        <div class="flex items-center space-x-3 w-full">
                                            <button
                                                class="mark-done flex items-center justify-center w-10 h-10 rounded-full transition-all 
                                                                                                                        {{ $item['done'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-800' }}"
                                                data-item-id="{{ $itemId }}" data-list-id="{{ $listId }}"
                                                data-done="{{ $item['done'] ? 'true' : 'false' }}">

                                                <span class="text-lg">
                                                    {{ $item['done'] ? '✔' : '✖️' }}
                                                </span>
                                            </button>

                                            <div class="flex-1">
                                                <p
                                                    class="text-lg font-semibold {{ $item['done'] ? 'line-through text-gray-500' : 'text-gray-900 dark:text-gray-100' }}">
                                                    {{ $item['name'] }}
                                                </p>

                                                <div class="mt-2">
                                                    <span
                                                        class="text-sm font-medium bg-gray-300 dark:bg-gray-600 px-2 py-1 rounded-md text-gray-800 dark:text-gray-200">
                                                        Categoría: {{ $item['category'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('shopping_list.delete_item', [$listId, $itemId]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors"
                                                onclick="return confirmDeletion('¿Estás seguro de que deseas eliminar este ítem?')">
                                                🗑
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Modal para añadir un ítem -->
                            <button id="openModalBtn"
                                class="bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition-colors">Añadir
                                Ítem</button>

                            <!-- Modal -->
                            <div id="itemModal"
                                class="hidden fixed inset-0 bg-gray-700 bg-opacity-75 flex items-center justify-center">
                                <div class="bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full">
                                    <h3 class="text-xl font-semibold mb-4 text-white">Añadir Ítem</h3>
                                    <form action="{{ route('shopping_list.add_item', $listId) }}" method="POST"
                                        class="flex flex-col gap-3">
                                        @csrf
                                        <label for="item_name" class="text-sm text-gray-600 dark:text-gray-400">Nombre del
                                            ítem</label>
                                        <input type="text" id="item_name" name="item_name"
                                            class="p-3 w-full border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                            placeholder="Nombre del ítem" required>

                                        <label for="category" class="text-sm text-gray-600 dark:text-gray-400">Categoría</label>
                                        <input type="text" id="category" name="category"
                                            class="p-3 w-full border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                            placeholder="Categoría" required>

                                        <button type="submit"
                                            class="bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition-colors">➕</button>
                                    </form>
                                    <button id="closeModalBtn"
                                        class="mt-4 bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mostrar u ocultar los ítems de la lista al hacer clic en el nombre de la lista
            document.querySelectorAll('.toggle-list').forEach(list => {
                list.addEventListener('click', () => {
                    const listId = list.getAttribute('data-list-id');
                    const listContent = document.getElementById(`list-content-${listId}`);
                    if (listContent) {
                        listContent.classList.toggle('hidden');
                    }
                });
            });

            // Modal logic
            const openModalBtn = document.getElementById('openModalBtn');
            const itemModal = document.getElementById('itemModal');
            const closeModalBtn = document.getElementById('closeModalBtn');

            openModalBtn.addEventListener('click', () => {
                itemModal.classList.remove('hidden');
            });

            closeModalBtn.addEventListener('click', () => {
                itemModal.classList.add('hidden');
            });
        });

        // Confirmación antes de eliminar
        function confirmDeletion(message) {
            return confirm(message);  // Muestra un mensaje de confirmación antes de continuar
        }
    </script>
</x-app-layout>