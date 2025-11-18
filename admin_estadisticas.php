<?php
session_start();
require "backend/configdatabase.php";

// Solo admin
if (!isset($_SESSION["usuario_rol"]) || $_SESSION["usuario_rol"] !== "admin") {
    header("Location: dashboard.php");
    exit();
}

// Libros más prestados
$libros = $conn->query("
    SELECT l.titulo, COUNT(p.ID_Prestamo) AS veces
    FROM prestamos p
    INNER JOIN libros l ON p.ID_Libro = l.ID
    GROUP BY l.id
    ORDER BY veces DESC
");

// Usuarios con más préstamos
$usuarios = $conn->query("
    SELECT u.nombre_completo, COUNT(p.ID_Prestamo) AS total
    FROM prestamos p
    INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    GROUP BY u.ID_Usuario
    ORDER BY total DESC
");

// Préstamos por mes
$meses = $conn->query("
    SELECT DATE_FORMAT(fecha_prestamo, '%Y-%m') AS mes,
           COUNT(*) AS cantidad
    FROM prestamos
    GROUP BY mes
    ORDER BY mes
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas</title>

<style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px; }

    h2 {
        background: #007bff;
        color: white;
        padding: 10px;
        border-radius: 8px;
    }

    .card {
        background: white;
        padding: 15px;
        margin: 20px 0;
        border-radius: 10px;
        box-shadow: 0 0 8px rgba(0,0,0,0.2);
    }

    /* EVITA QUE LA PÁGINA CREZCA INFINITO */
    .tabla-scroll {
        max-height: 350px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;
        background: white;
        border-radius: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #007bff;
        color: white;
        padding: 8px;
    }
    td {
        padding: 7px;
        border-bottom: 1px solid #ddd;
    }
</style>

</head>
<body>

<h1>📊 Estadísticas Generales</h1>

<!-- Libros más prestados -->
<div class="card">
<h2>📚 Libros más prestados</h2>
<div class="tabla-scroll">
<table>
    <tr><th>Título</th><th>Veces prestado</th></tr>
    <?php while ($l = $libros->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($l["titulo"]) ?></td>
        <td><?= $l["veces"] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
</div>


<!-- Usuarios con más préstamos -->
<div class="card">
<h2>👤 Usuarios con más préstamos</h2>
<div class="tabla-scroll">
<table>
    <tr><th>Usuario</th><th>Préstamos</th></tr>
    <?php while ($u = $usuarios->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($u["nombre_completo"]) ?></td>
        <td><?= $u["total"] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
</div>


<!-- Préstamos por mes -->
<div class="card">
<h2>📅 Préstamos por mes</h2>
<div class="tabla-scroll">
<table>
    <tr><th>Mes</th><th>Cantidad</th></tr>
    <?php while ($m = $meses->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($m["mes"]) ?></td>
        <td><?= $m["cantidad"] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
</div>

</body>
</html>