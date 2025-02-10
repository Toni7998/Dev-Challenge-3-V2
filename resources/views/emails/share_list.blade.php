<!DOCTYPE html>
<html>

<head>
    <title>Lista de Compras Compartida</title>
</head>

<body>
    <h1>📋 ¡Te han compartido una lista de compras!</h1>

    <p>Un usuario te ha compartido la lista de compras: <strong>{{ $listName }}</strong>.</p>

    <p>Puedes acceder a tus listas compartidas en cualquier momento desde tu sección de <a
            href="{{ $shoppingListUrl }}">Shopping List</a>.</p>

    <p>Si no esperabas recibir esta lista, puedes ignorar este correo.</p>

    <hr>
    <p>📌 Este es un mensaje automático, por favor no respondas a este correo.</p>
</body>

</html>