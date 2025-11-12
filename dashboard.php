<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Panel principal</title></head>
<body>
    <h1>Bienvenido, <?= $_SESSION["usuario_nombre"] ?> 👋</h1>
    <p>Tu rol es: <?= $_SESSION["usuario_rol"] ?></p>

    <a href="usuarios/index.php">Gestionar usuarios</a> |
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>