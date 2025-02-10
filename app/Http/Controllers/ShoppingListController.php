<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Contract\Database;
use App\Models\User;
use App\Mail\ShareListMail;

class ShoppingListController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para ver sus listas.');
        }

        // Obtener listas propias
        $userLists = $this->database->getReference("shopping_lists/{$userId}")->getValue();
        $userLists = is_array($userLists) ? $userLists : [];

        // Obtener listas compartidas
        $allLists = $this->database->getReference("shopping_lists")->getValue();
        $sharedLists = [];

        foreach ($allLists as $ownerId => $lists) {
            foreach ($lists as $listId => $list) {
                if (isset($list['shared_with'][$userId])) {
                    $sharedLists[$listId] = $list;
                }
            }
        }

        // Combinar listas propias y compartidas
        $shoppingLists = array_merge($userLists, $sharedLists);

        return view('shopping_list', compact('shoppingLists'));
    }

    public function addList(Request $request)
    {
        $userId = Auth::id();
        $listRef = $this->database->getReference("shopping_lists/{$userId}")->push();
        $listRef->set([
            'name' => $request->input('list_name'),
            'owner' => $userId,
            'shared_with' => [],
            'items' => []
        ]);

        return redirect()->route('shopping_list.index')->with('success', 'Lista creada correctamente!');
    }

    public function addItem(Request $request, $listId)
    {
        $userId = Auth::id();
        $listPath = "shopping_lists/{$userId}/{$listId}/items";

        if (!$this->database->getReference("shopping_lists/{$userId}/{$listId}")->getValue()) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada!');
        }

        $itemRef = $this->database->getReference($listPath)->push();
        $itemRef->set([
            'name' => $request->input('item_name'),
            'category' => $request->input('category'),
            'done' => false
        ]);

        return redirect()->route('shopping_list.index')->with('success', 'Ítem añadido correctamente!');
    }

    public function deleteItem($listId, $itemId)
    {
        $userId = Auth::id();
        $listPath = "shopping_lists/{$userId}/{$listId}/items/{$itemId}";

        if (!$this->database->getReference("shopping_lists/{$userId}/{$listId}")->getValue()) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada!');
        }

        $this->database->getReference($listPath)->remove();

        return redirect()->route('shopping_list.index')->with('success', 'Item eliminado correctamente!');
    }

    public function toggleDone(Request $request, $listId, $itemId)
    {
        $userId = Auth::id();
        $itemRef = $this->database->getReference("shopping_lists/{$userId}/{$listId}/items/{$itemId}");
        $item = $itemRef->getValue();

        if (!$item) {
            return response()->json(['error' => 'Ítem no encontrado.'], 404);
        }

        $newState = $request->input('done', false);
        $itemRef->update(['done' => $newState]);

        return response()->json([
            'success' => true,
            'done' => $newState
        ]);
    }

    public function shareList(Request $request, $listId)
    {
        $userId = Auth::id();
        $email = $request->input('email');

        // Buscar usuario por email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('shopping_list.index')->with('error', 'Usuario no encontrado!');
        }

        $sharedUserId = $user->id; // ID del usuario con el que compartimos la lista
        $listPath = "shopping_lists/{$userId}/{$listId}";

        // Obtener la lista
        $list = $this->database->getReference($listPath)->getValue();

        if (!$list) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada!');
        }

        // Obtener el nombre de la lista
        $listName = $list['name'] ?? 'Lista sin nombre';
        // **Verificar si el usuario ya tiene esta lista compartida**
        $sharedUsers = $list['shared_with'] ?? [];
        if (array_key_exists($sharedUserId, $sharedUsers)) {
            return redirect()->route('shopping_list.index')->with('error', 'Este usuario ya tiene acceso a esta lista!');
        }

        // **Agregar al usuario a "shared_with" en Firebase**
        $this->database->getReference("{$listPath}/shared_with/{$sharedUserId}")->set(true);

        // Enviar correo notificando al usuario
        Mail::to($email)->send(new ShareListMail($listId, $userId, $listName));

        return redirect()->route('shopping_list.index')->with('success', 'La lista ha sido compartida exitosamente!');
    }


    public function acceptShare($ownerId, $listId)
    {
        $userId = Auth::id();
        $listPath = "shopping_lists/{$ownerId}/{$listId}";

        $list = $this->database->getReference($listPath)->getValue();

        if (!$list) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada!');
        }

        $this->database->getReference("{$listPath}/shared_with/{$userId}")->set(true);

        return redirect()->route('shopping_list.index')->with('success', 'Lista agregada a tus listas compartidas!');
    }

    public function delete($listId)
    {
        $userId = Auth::id();

        // Primero, buscamos en todas las listas si el usuario es el propietario o si la lista está compartida con él
        $list = $this->database->getReference("shopping_lists/")->getValue();

        // Verificar si la lista existe
        $listFound = null;
        foreach ($list as $ownerId => $lists) {
            foreach ($lists as $id => $item) {
                if ($id == $listId) {
                    $listFound = $item;
                    break 2; // Romper el bucle si encontramos la lista
                }
            }
        }

        if (!$listFound) {
            return redirect()->back()->with('error', 'Lista no encontrada.');
        }

        // Verificar si el usuario es el propietario de la lista
        if ($listFound['owner'] != $userId) {
            return redirect()->back()->with('error', 'No puedes eliminar esta lista porque no eres el propietario.');
        }

        // Eliminar la lista si el usuario es el propietario
        $this->database->getReference("shopping_lists/{$userId}/{$listId}")->remove();

        return redirect()->back()->with('success', 'Lista eliminada correctamente.');
    }

}
