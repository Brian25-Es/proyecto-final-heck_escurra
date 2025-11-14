<?php
session_start();
require "configdatabase.php";

$usuario_id = $_SESSION["usuario_id"];

$sql = "
SELECT p.*, l.titulo
FROM prestamos p
JOIN libros l ON p.ID_Libro = l.ID
WHERE p.ID_Usuario = $usuario_id AND p.estado_prestamo='Activo'
";

$result = $conn->query($sql);

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>