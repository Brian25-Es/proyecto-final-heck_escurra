<?php
require 'configdatabase.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'listar':
        $result = $conn->query("SELECT ID_User, user, nombre, email, rol, created_at FROM usuario_sistema");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'insertar':
        $data = json_decode(file_get_contents("php://input"), true);
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO usuario_sistema (user, password, nombre, email, rol)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss",
            $data['user'], $hash, $data['nombre'], $data['email'], $data['rol']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'editar':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE usuario_sistema SET user=?, password=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("sssssi",
                $data['user'], $hash, $data['nombre'], $data['email'], $data['rol'], $data['ID_User']
            );
        } else {
            $stmt = $conn->prepare("UPDATE usuario_sistema SET user=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("ssssi",
                $data['user'], $data['nombre'], $data['email'], $data['rol'], $data['ID_User']
            );
        }
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'borrar':
        $id = $_GET['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM usuario_sistema WHERE ID_User=?");
        $stmt->bind_param("i", $id);
        echo $stmt->execute() ? "ok" : "error";
        break;
}
?>
