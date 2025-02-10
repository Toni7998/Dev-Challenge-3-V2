<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Database;

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

        $shoppingLists = $this->database->getReference("shopping_lists/{$userId}")->getValue();
        $shoppingLists = is_array($shoppingLists) ? $shoppingLists : [];

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

        return redirect()->route('shopping_list.index')->with('success', 'Item añadido correctamente!');
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

    public function shareList(Request $request, $listId)
    {
        $userId = Auth::id();
        $sharedUserId = $request->input('shared_user_id');

        if (!$this->database->getReference("shopping_lists/{$userId}/{$listId}")->getValue()) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada!');
        }

        $this->database->getReference("shopping_lists/{$userId}/{$listId}/shared_with/{$sharedUserId}")->set(true);

        return redirect()->route('shopping_list.index')->with('success', 'Lista compartida correctamente!');
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


    public function delete($listId)
    {
        $userId = Auth::id();

        // Verificar si la lista existe en Firebase
        $listRef = $this->database->getReference("shopping_lists/{$userId}/{$listId}");
        $shoppingList = $listRef->getValue();

        if (!$shoppingList) {
            return redirect()->back()->with('error', 'Lista no encontrada.');
        }

        // Eliminar la lista de Firebase
        $listRef->remove();

        return redirect()->back()->with('success', 'Lista eliminada correctamente.');
    }



}