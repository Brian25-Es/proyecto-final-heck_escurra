<?php
require 'configdatabase.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'listar':
        $result = $conn->query("SELECT * FROM usuarios");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'insertar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, email, telefono, direccion, dni, estado)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss",
            $data['nombre_completo'], $data['email'], $data['telefono'],
            $data['direccion'], $data['dni'], $data['estado']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'editar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, email=?, telefono=?, direccion=?, dni=?, estado=? WHERE ID_Usuario=?");
        $stmt->bind_param("ssssssi",
            $data['nombre_completo'], $data['email'], $data['telefono'],
            $data['direccion'], $data['dni'], $data['estado'], $data['ID_Usuario']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'borrar':
        $id = $_GET['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE ID_Usuario=?");
        $stmt->bind_param("i", $id);
        echo $stmt->execute() ? "ok" : "error";
        break;
}
?>
