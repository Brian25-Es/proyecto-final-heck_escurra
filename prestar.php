<?php
session_start();
require "backend/configdatabase.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $libro_id  = intval($_POST["libro_id"]);
    $usuario_id = intval($_POST["usuario_id"]);

    if ($libro_id <= 0 || $usuario_id <= 0) {
        echo "error";
        exit();
    }

    // Registrar préstamo (+14 días)
    $stmt = $conn->prepare("
        INSERT INTO prestamos (ID_Libro, ID_Usuario, fecha_devolucion, estado_prestamo)
        VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Activo')
    ");
    $stmt->bind_param("ii", $libro_id, $usuario_id);
    $stmt->execute();

    // Cambiar estado del libro
    $conn->query("UPDATE libros SET estado='Prestado' WHERE ID=$libro_id");

    echo "ok";
}
?>
