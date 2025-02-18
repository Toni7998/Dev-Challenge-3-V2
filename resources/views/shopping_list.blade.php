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
                                    class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors open-delete-modal"
                                    data-form-action="{{ route('shopping_list.delete', $listId) }}"
                                    data-message="¿Estás seguro de que deseas eliminar esta lista?">
                                    🗑
                                </button>
                            </form>
                        </div>

                        <div id="list-content-{{ $listId }}" class="hidden transition-all duration-300">
                            <ul class="space-y-3 mb-6">
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
                                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors open-delete-modal"
                                                data-form-action="{{ route('shopping_list.delete_item', [$listId, $itemId]) }}"
                                                data-message="¿Estás seguro de que deseas eliminar este ítem?">
                                                🗑
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Modal para añadir un ítem -->
                            <div class="text-center mb-6">
                                <button
                                    class="openModalBtn bg-green-500 text-white p-4 rounded-md hover:bg-green-600 transition-colors text-lg w-full sm:w-4/4 lg:w-2/2 mx-auto"
                                    data-list-id="{{ $listId }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white mx-auto" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal -->
                            <div class="itemModal hidden fixed inset-0 bg-gray-800 bg-opacity-80 flex items-center justify-center"
                                data-list-id="{{ $listId }}">
                                <div class="bg-gray-900 text-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                                    <h3 class="text-xl font-semibold mb-4">Añadir Ítem</h3>
                                    <form action="{{ route('shopping_list.add_item', $listId) }}" method="POST"
                                        class="flex flex-col gap-3">
                                        @csrf
                                        <label for="item_name" class="text-sm text-gray-400">Nombre del ítem</label>
                                        <input type="text" name="item_name"
                                            class="p-3 w-full border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                            placeholder="Nombre del ítem" required>

                                        <label for="category" class="text-sm text-gray-400">Categoría</label>
                                        <input type="text" name="category"
                                            class="p-3 w-full border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700"
                                            placeholder="Categoría" required>

                                        <button type="submit"
                                            class="bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition-colors">Añadir
                                            Item</button>
                                    </form>
                                    <button
                                        class="closeModalBtn mt-4 bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div id="delete-confirmation-modal"
        class="hidden fixed inset-0 bg-gray-800 bg-opacity-80 flex items-center justify-center z-50">
        <div class="bg-gray-900 text-white p-6 rounded-lg shadow-lg max-w-sm w-full">
            <p id="delete-message" class="mb-4">¿Estás seguro de que deseas eliminar este ítem?</p>
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-between">
                    <button type="submit"
                        class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600">Eliminar</button>
                    <button type="button" id="close-modal"
                        class="bg-gray-500 text-white p-2 rounded-md hover:bg-gray-600">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Obtener el estado de las listas abiertas desde localStorage
            const listStates = JSON.parse(localStorage.getItem('listStates')) || {}; // Obtener estado guardado

            // Restaurar el estado de las listas abiertas
            document.querySelectorAll('.toggle-list').forEach(list => {
                const listId = list.getAttribute('data-list-id');
                const listContent = document.getElementById(`list-content-${listId}`);

                if (listStates[listId]) {
                    listContent.classList.remove('hidden');
                }

                list.addEventListener('click', () => {
                    const isOpen = !listContent.classList.toggle('hidden');
                    listStates[listId] = isOpen;
                    localStorage.setItem('listStates', JSON.stringify(listStates));
                });
            });

            // Manejo de marcado de productos
            document.querySelectorAll('.mark-done').forEach(button => {
                button.addEventListener('click', async function () {
                    const itemId = this.getAttribute('data-item-id');
                    const listId = this.getAttribute('data-list-id');
                    const newState = this.getAttribute('data-done') === 'true' ? false : true;

                    // Cambiar la interfaz antes de enviar la solicitud
                    this.innerHTML = newState ? '✔' : '✖️';
                    this.classList.toggle('bg-green-500', newState);
                    this.classList.toggle('text-white', newState);
                    this.classList.toggle('bg-gray-300', !newState);
                    this.classList.toggle('text-gray-800', !newState);
                    this.closest('li').querySelector('p').classList.toggle('line-through', newState);
                    this.closest('li').querySelector('p').classList.toggle('text-gray-500', newState);
                    this.setAttribute('data-done', newState.toString());

                    try {
                        let response = await fetch(`/shopping_list/${listId}/toggle_done/${itemId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ done: newState })
                        });

                        let result = await response.json();
                        if (!result.success) {
                            throw new Error('Error en la actualización');
                        }
                    } catch (error) {
                        alert('Error al actualizar el estado.');

                        // Revertir cambios en caso de error
                        this.innerHTML = !newState ? '✔' : '✖️';
                        this.classList.toggle('bg-green-500', !newState);
                        this.classList.toggle('text-white', !newState);
                        this.classList.toggle('bg-gray-300', newState);
                        this.classList.toggle('text-gray-800', newState);
                        this.closest('li').querySelector('p').classList.toggle('line-through', !newState);
                        this.closest('li').querySelector('p').classList.toggle('text-gray-500', !newState);
                        this.setAttribute('data-done', (!newState).toString());
                    }
                });
            });

            // Manejo de modales para añadir ítems
            document.querySelectorAll('.openModalBtn').forEach(button => {
                button.addEventListener('click', function () {
                    const listId = this.getAttribute('data-list-id');
                    const modal = document.querySelector(`.itemModal[data-list-id="${listId}"]`);
                    if (modal) modal.classList.remove('hidden');
                });
            });

            // Cerrar los modales de añadir ítem
            document.querySelectorAll('.itemModal').forEach(modal => {
                const closeModalBtn = modal.querySelector('.closeModalBtn');
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
                }
            });

            // Mostrar el modal de confirmación de eliminación
            document.querySelectorAll('.open-delete-modal').forEach(button => {
                button.addEventListener('click', function () {
                    const message = this.getAttribute('data-message');
                    const formAction = this.getAttribute('data-form-action');

                    // Cambiar el mensaje y la acción del formulario en el modal
                    document.getElementById('delete-message').innerText = message;
                    document.getElementById('delete-form').setAttribute('action', formAction);

                    // Mostrar el modal
                    document.getElementById('delete-confirmation-modal').classList.remove('hidden');
                });
            });

            // Cerrar el modal de confirmación sin enviar el formulario
            document.getElementById('close-modal').addEventListener('click', function (event) {
                event.preventDefault(); // Evita que el formulario se envíe
                document.getElementById('delete-confirmation-modal').classList.add('hidden');
            });

        });
    </script>


</x-app-layout>