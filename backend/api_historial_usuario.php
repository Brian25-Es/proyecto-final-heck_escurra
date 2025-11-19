<?php
session_start();
require "configdatabase.php";

$sql = "
SELECT p.*, l.titulo, u.nombre_completo
FROM prestamos p
JOIN libros l ON p.ID_Libro = l.ID
JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
WHERE p.estado_prestamo='Devuelto'
ORDER BY p.fecha_dev_real DESC
";

$result = $conn->query($sql);

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>