<?php
session_start();
require "backend/configdatabase.php";

$prestamo_id = intval($_GET["prestamo_id"] ?? 0);
$libro_id    = intval($_GET["libro_id"] ?? 0);

if (!$prestamo_id || !$libro_id) {
    exit("Faltan datos del préstamo.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar devolución</title>
</head>
<body>

<h2>Registrar devolución</h2>

<p>¿Confirmas que este libro fue devuelto?</p>

<form method="POST" action="backend/devolver.php">
    <input type="hidden" name="prestamo_id" value="<?= $prestamo_id ?>">
    <input type="hidden" name="libro_id" value="<?= $libro_id ?>">

    <button type="submit">Confirmar devolución</button>
</form>

</body>
</html>