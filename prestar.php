<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_POST["usuario_id"];
    $libro_id   = $_POST["libro_id"];

    // Insertar préstamo
    $stmt = $conn->prepare("INSERT INTO prestamos (ID_Libro, ID_Usuario, fecha_devolucion, estado_prestamo)
                            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), 'Activo')");
    $stmt->bind_param("ii", $libro_id, $usuario_id);
    $stmt->execute();

    // Cambiar estado del libro a "Prestado"
    $conn->query("UPDATE libros SET estado='Prestado' WHERE ID=$libro_id");

    header("Location: dashboard.php");
}
?>
