<?php
session_start();
require "configdatabase.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $prestamo_id = intval($_POST["prestamo_id"]);
    $libro_id    = intval($_POST["libro_id"]);

    // 1. Cambiar estado del préstamo a devuelto
    $stmt = $conn->prepare("
        UPDATE prestamos 
        SET estado_prestamo='Devuelto',
            fecha_dev_real = CURDATE()
        WHERE ID_Prestamo = ?
    ");
    $stmt->bind_param("i", $prestamo_id);
    $stmt->execute();

    // 2. Cambiar libro a disponible
    $stmt2 = $conn->prepare("UPDATE libros SET estado='Disponible' WHERE ID = ?");
    $stmt2->bind_param("i", $libro_id);
    $stmt2->execute();

    echo "ok";
}
?>