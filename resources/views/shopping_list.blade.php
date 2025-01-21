@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-semibold text-center text-gray-900 dark:text-gray-100 mb-8">Llista de Compra</h1>

    <!-- Mostrar mensajes de éxito -->
    <div id="message" class="hidden bg-green-500 text-white p-4 rounded-md mb-6 text-center"></div>

    <!-- Formulario para agregar un nuevo elemento -->
    <form id="addItemForm" class="mb-8 text-center">
        <input type="text" name="item_name" id="item_name" placeholder="Nom de l'element" required
            class="p-3 mb-4 w-3/4 max-w-md border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <select name="category" id="category" required
            class="p-3 mb-4 w-3/4 max-w-md border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <option value="">Selecciona Categoria</option>
        </select>
        <button type="submit" class="bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition-colors">Afegir
            Element</button>
    </form>

    <!-- Formulario para agregar una nueva categoría -->
    <form id="addCategoryForm" class="mb-8 text-center">
        <input type="text" name="category_name" id="category_name" placeholder="Nom de la categoria" required
            class="p-3 mb-4 w-3/4 max-w-md border rounded-md text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <button type="submit" class="bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition-colors">Afegir
            Categoria</button>
    </form>

    <!-- Mostrar los elementos de la lista -->
    <h2 class="text-2xl font-semibold text-center text-gray-900 dark:text-gray-100 mb-6">Elements de la Llista</h2>
    <div id="shoppingList"></div>


</div>

<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.21.0/firebase-app.js';
    import { getDatabase, ref, push, remove, onValue, set } from 'https://www.gstatic.com/firebasejs/9.21.0/firebase-database.js';

    // Configuració de Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyDxW1e7hZTJ0gOTR2A5Xkxl1dsjmsxyz",
        authDomain: "m12-proyecto.firebaseapp.com",
        databaseURL: "https://m12-proyecto-default-rtdb.europe-west1.firebasedatabase.app",
        projectId: "m12-proyecto",
        storageBucket: "m12-proyecto.appspot.com",
        messagingSenderId: "109360802652583660232",
        appId: "1:109360802652583660232:web:9b8a1b2bc0f2388b124c68"
    };

    // Inicialitza Firebase
    const app = initializeApp(firebaseConfig);
    const database = getDatabase(app);

    document.addEventListener('DOMContentLoaded', async () => {
        const shoppingListRef = ref(database, 'shopping_list');
        const categoriesRef = ref(database, 'categories');

        const shoppingListContainer = document.getElementById('shoppingList');
        const categorySelect = document.getElementById('category');
        const messageBox = document.getElementById('message');

        const showMessage = (message) => {
            messageBox.textContent = message;
            messageBox.classList.remove('hidden');
            setTimeout(() => messageBox.classList.add('hidden'), 3000);
        };

        // Càrrega de categories
        onValue(categoriesRef, (snapshot) => {
            const categories = snapshot.val() || {};
            categorySelect.innerHTML = '<option value="">Selecciona Categoria</option>';
            Object.keys(categories).forEach((key) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = categories[key]?.name || 'Categoria sense nom';
                categorySelect.appendChild(option);
            });
        });

        // Càrrega de la llista de la compra
        onValue(shoppingListRef, (snapshot) => {
            shoppingListContainer.innerHTML = '';
            const items = snapshot.val() || {};
            Object.entries(items).forEach(([key, item]) => {
                const div = document.createElement('div');
                div.className = 'item bg-gray-200 dark:bg-gray-700 p-4 rounded-md mb-4 flex justify-between items-center shadow-md';
                div.innerHTML = `
                    <span class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        ${item.name} (${item.category || 'Sense categoria'})
                    </span>
                    <div class="flex items-center space-x-2">
                        <button class="done-btn ${item.done ? 'bg-green-500' : 'bg-gray-500'} text-white p-2 rounded-md transition-colors" data-id="${key}">
                            ${item.done ? 'Fet' : 'Per Fer'}
                        </button>
                        <button class="delete-btn bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition-colors" data-id="${key}">
                            Esborrar
                        </button>
                    </div>
                `;
                shoppingListContainer.appendChild(div);
            });

            // Gestió del botó "Esborrar"
            document.querySelectorAll('.delete-btn').forEach((button) => {
                button.addEventListener('click', (event) => {
                    const id = event.target.dataset.id;
                    remove(ref(database, 'shopping_list/' + id));
                    showMessage('Element esborrat correctament!');
                });
            });

            // Gestió del botó "Fet"
            document.querySelectorAll('.done-btn').forEach((button) => {
                button.addEventListener('click', (event) => {
                    const id = event.target.dataset.id;
                    const itemRef = ref(database, 'shopping_list/' + id);

                    // Alternar l'estat de "fet"
                    onValue(itemRef, (snapshot) => {
                        const item = snapshot.val();
                        const newState = !item.done;

                        // Actualitzar l'element amb el nou estat
                        set(itemRef, { ...item, done: newState });
                    }, { onlyOnce: true });
                });
            });
        });

        // Afegir nou element
        document.getElementById('addItemForm').addEventListener('submit', (event) => {
            event.preventDefault();
            const itemName = document.getElementById('item_name').value;
            const categoryId = document.getElementById('category').value;

            let categoryName = '';
            onValue(ref(database, 'categories/' + categoryId), (snapshot) => {
                const category = snapshot.val();
                if (category) {
                    categoryName = category.name;
                }

                push(shoppingListRef, { name: itemName, category: categoryName, done: false });
                showMessage('Element afegit correctament!');
            });

            event.target.reset();
        });

        // Afegir nova categoria
        document.getElementById('addCategoryForm').addEventListener('submit', (event) => {
            event.preventDefault();
            const categoryName = document.getElementById('category_name').value;

            push(categoriesRef, { name: categoryName });
            showMessage('Categoria afegida correctament!');
            event.target.reset();
        });
    });
</script>



@endsection