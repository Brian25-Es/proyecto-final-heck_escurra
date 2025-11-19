<?php
require "configdatabase.php";

$sql = "
SELECT p.*, l.titulo, u.nombre_completo
FROM prestamos p
JOIN libros l ON p.ID_Libro = l.ID
JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
WHERE p.estado_prestamo IN ('Activo','Vencido')
ORDER BY p.fecha_prestamo DESC
";

$result = $conn->query($sql);

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>