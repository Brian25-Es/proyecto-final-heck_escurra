<?php
require 'configdatabase.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'listar':
        $sql = "SELECT p.*, l.titulo AS libro, u.nombre_completo AS usuario
                FROM prestamos p
                LEFT JOIN libros l ON p.ID_Libro = l.ID
                LEFT JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario";
        $result = $conn->query($sql);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'insertar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO prestamos (ID_Libro, ID_Usuario, fecha_devolucion, estado_prestamo, observaciones)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss",
            $data['ID_Libro'], $data['ID_Usuario'], $data['fecha_devolucion'],
            $data['estado_prestamo'], $data['observaciones']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'editar':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE prestamos SET ID_Libro=?, ID_Usuario=?, fecha_devolucion=?, fecha_dev_real=?, estado_prestamo=?, observaciones=? WHERE ID_Prestamo=?");
        $stmt->bind_param("iissssi",
            $data['ID_Libro'], $data['ID_Usuario'], $data['fecha_devolucion'],
            $data['fecha_dev_real'], $data['estado_prestamo'], $data['observaciones'], $data['ID_Prestamo']
        );
        echo $stmt->execute() ? "ok" : "error";
        break;

    case 'borrar':
        $id = $_GET['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM prestamos WHERE ID_Prestamo=?");
        $stmt->bind_param("i", $id);
        echo $stmt->execute() ? "ok" : "error";
        break;
}
?>
