<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;

class FirebaseController extends Controller
{
    private $database;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/m12-proyecto.json'))
            ->withDatabaseUri('https://m12-proyecto-default-rtdb.europe-west1.firebasedatabase.app')
            ->createDatabase();

        $this->database = $firebase;
    }

    public function storeData()
    {
        $userId = 'userId1';
        $listId = $this->database->getReference("users/{$userId}/lists")->push()->getKey();
        $this->database->getReference("users/{$userId}/lists/{$listId}")->set([
            'name' => 'Compra setmanal',
            'categories' => []
        ]);

        return "Datos guardados correctamente!";
    }

    public function getData()
    {
        $userId = 'userId1';
        $lists = $this->database->getReference("users/{$userId}/lists")->getValue();

        return response()->json($lists);
    }
}
