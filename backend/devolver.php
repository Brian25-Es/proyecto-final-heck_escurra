<?php
session_start();
require "configdatabase.php";

// Solo admin o bibliotecario
if (!isset($_SESSION["usuario_id"])) {
    http_response_code(403);
    exit("No autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(403);
    exit("Método inválido. Esta página solo acepta POST.");
}

$prestamo_id = intval($_POST["prestamo_id"]);
$libro_id    = intval($_POST["libro_id"]);

// 1. Cambiar estado del préstamo
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

// Redirigir con mensaje
header("Location: ../admin_prestamos.php?msg=devuelto_ok");
exit();
?>