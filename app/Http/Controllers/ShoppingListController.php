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
        $request->validate([
            'email' => 'required|email'
        ]);

        $userId = Auth::id();
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('shopping_list.index')->with('error', 'El usuario con ese email no existe.');
        }

        $sharedUserId = $user->id;

        // Verificar si la lista existe
        $listPath = "shopping_lists/{$userId}/{$listId}";
        $list = $this->database->getReference($listPath)->getValue();

        if (!$list) {
            return redirect()->route('shopping_list.index')->with('error', 'Lista no encontrada.');
        }

        // Verificar si ya está compartida con el usuario
        if (isset($list['shared_with'][$sharedUserId])) {
            return redirect()->route('shopping_list.index')->with('info', 'Esta lista ya está compartida con este usuario.');
        }

        // Guardar en Firebase que la lista se ha compartido
        $this->database->getReference("{$listPath}/shared_with/{$sharedUserId}")->set(true);

        // Enviar correo de invitación
        Mail::to($email)->send(new ShareListMail($listId, $userId));

        return redirect()->route('shopping_list.index')->with('success', 'Invitación enviada correctamente.');
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
        $listPath = "shopping_lists/{$userId}/{$listId}";

        $list = $this->database->getReference($listPath)->getValue();

        if (!$list) {
            return redirect()->back()->with('error', 'Lista no encontrada.');
        }

        $this->database->getReference($listPath)->remove();

        return redirect()->back()->with('success', 'Lista eliminada correctamente.');
    }
}
