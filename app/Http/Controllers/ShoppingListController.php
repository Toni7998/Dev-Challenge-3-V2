<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class ShoppingListController extends Controller
{
    public function index()
    {
        $items = session()->get('shopping_list', []);
        $categories = session()->get('categories', []);

        // Añadir un identificador único a cada elemento de la lista
        $itemsWithIds = [];
        foreach ($items as $key => $item) {
            $itemsWithIds[] = [
                'id' => $key, // Usamos el índice como identificador
                'item_name' => $item['item_name'],
                'category' => $item['category']
            ];
        }

        return view('shopping_list', compact('itemsWithIds', 'categories'));
    }

    public function addItem(Request $request)
    {
        $items = session()->get('shopping_list', []);
        $items[] = [
            'item_name' => $request->input('item_name'),
            'category' => $request->input('category')
        ];
        session()->put('shopping_list', $items);

        return redirect()->route('shopping_list.index')->with('success', 'Element afegit correctament!');
    }

    public function addCategory(Request $request)
    {
        $categories = session()->get('categories', []);
        $categories[] = $request->input('category_name');
        session()->put('categories', $categories);

        return redirect()->route('shopping_list.index');
    }

    public function deleteItem(Request $request)
    {
        $items = session()->get('shopping_list', []);
        unset($items[$request->input('item_id')]);
        session()->put('shopping_list', array_values($items));

        return redirect()->route('shopping_list.index');
    }
}
