<?php
session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

$nombre = $_SESSION["usuario_nombre"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Biblioteca</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            display: flex;
            background: #f0f2f5;
        }

        /* ==== SIDEBAR ==== */
        .sidebar {
            width: 250px;
            background: #343a40;
            height: 100vh;
            color: white;
            padding-top: 20px;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid #495057;
        }

        .sidebar a:hover {
            background: #495057;
        }

        /* ==== CONTENT ==== */
        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }

        header {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px;
        }

        .logout-btn {
            float: right;
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        h3 {
            margin-top: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }

        .box {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>ADMIN</h2>

    <a href="admin_usuarios_sistema.php">Usuarios del Sistema</a>
    <a href="admin_socios.php">Usuarios Comunes</a>
    <a href="admin_libros.php">Libros</a>
    <a href="admin_prestamos.php">Préstamos</a>
    <a href="admin_devoluciones.php">Registrar Devoluciones</a>
    <a href="admin_estadisticas.php">Estadísticas</a>
</div>

<!-- CONTENT -->
<div class="content">

    <header>
        Bienvenido, <?= htmlspecialchars($nombre) ?> (Administrador)
        <form action="logout.php" method="POST" style="display:inline;">
            <button class="logout-btn">Cerrar sesión</button>
        </form>
    </header>

    <h3>Panel Principal del Administrador</h3>

    <div class="box">
        <p>Desde el menú de la izquierda puedes gestionar completamente la biblioteca:</p>

        <ul>
            <li>Crear, editar y eliminar bibliotecarios</li>
            <li>Gestionar los usuarios socios</li>
            <li>Registrar, actualizar y eliminar libros</li>
            <li>Ver todos los préstamos y su estado</li>
            <li>Registrar devoluciones</li>
            <li>Ver estadísticas del sistema</li>
        </ul>
    </div>

</div>

</body>
</html>