<?php
require 'configdatabase.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'listar':
        $result = $conn->query("SELECT * FROM libros");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'insertar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, isbn, editorial, año, categoria, descripcion, estado)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss",
            $data['titulo'], $data['autor'], $data['isbn'], $data['editorial'],
            $data['año'], $data['categoria'], $data['descripcion'], $data['estado']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'editar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE libros SET titulo=?, autor=?, isbn=?, editorial=?, año=?, categoria=?, descripcion=?, estado=? WHERE ID=?");
        $stmt->bind_param("ssssisssi",
            $data['titulo'], $data['autor'], $data['isbn'], $data['editorial'],
            $data['año'], $data['categoria'], $data['descripcion'], $data['estado'], $data['ID']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'borrar':
        $id = $_GET['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM libros WHERE ID=?");
        $stmt->bind_param("i", $id);
        echo $stmt->execute() ? "ok" : "error";
        break;
}
?>