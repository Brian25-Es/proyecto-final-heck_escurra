<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "backend/configdatabase.php";

// Solo admin puede entrar
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Función para evitar deprecated de PHP8 al pasar NULL
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Obtener préstamos activos
$activos = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo
    FROM prestamos p
    INNER JOIN libros l ON p.ID_Libro = l.ID
    INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE p.estado_prestamo = 'Activo'
    ORDER BY p.fecha_prestamo DESC
");

// Obtener préstamos vencidos
$vencidos = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo
    FROM prestamos p
    INNER JOIN libros l ON p.ID_Libro = l.ID
    INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE p.estado_prestamo = 'Vencido'
    ORDER BY p.fecha_devolucion ASC
");

// Obtener préstamos devueltos
$devueltos = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo
    FROM prestamos p
    INNER JOIN libros l ON p.ID_Libro = l.ID
    INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE p.estado_prestamo = 'Devuelto'
    ORDER BY p.fecha_dev_real DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administración de Préstamos</title>
<style>
body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 20px; }
h2 { margin-bottom: 5px; }
.section { background: white; padding: 15px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 8px; border: 1px solid #ccc; }
th { background: #007bff; color: white; }
.btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; color: white; }
.btn-back { background: #6c757d; }
</style>
</head>
<body>

<a href="dashboard_admin.php"><button class="btn btn-back">Volver al panel admin</button></a>

<h1>Control de Préstamos</h1>

<!-- ===========================
     PRÉSTAMOS ACTIVOS
     =========================== -->
<div class="section">
    <h2>📘 Préstamos Activos</h2>
    <table>
        <thead>
            <tr>
                <th>Libro</th>
                <th>Usuario</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $activos->fetch_assoc()): ?>
            <tr>
                <td><?= h($p["titulo"]) ?></td>
                <td><?= h($p["nombre_completo"]) ?></td>
                <td><?= h($p["fecha_prestamo"]) ?></td>
                <td><?= h($p["fecha_devolucion"]) ?></td>
                <td><?= h($p["estado_prestamo"]) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ===========================
     PRÉSTAMOS VENCIDOS
     =========================== -->
<div class="section">
    <h2>⏰ Préstamos Vencidos</h2>
    <table>
        <thead>
            <tr>
                <th>Libro</th>
                <th>Usuario</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Días atraso</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $vencidos->fetch_assoc()): ?>
            <tr>
                <td><?= h($p["titulo"]) ?></td>
                <td><?= h($p["nombre_completo"]) ?></td>
                <td><?= h($p["fecha_prestamo"]) ?></td>
                <td><?= h($p["fecha_devolucion"]) ?></td>
                <td>
                    <?php
                        $hoy = new DateTime();
                        $fecha_dev = new DateTime($p["fecha_devolucion"]);
                        $dias = $fecha_dev->diff($hoy)->days;
                        echo $dias;
                    ?>
                </td>
                <td><?= h($p["estado_prestamo"]) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ===========================
     PRÉSTAMOS DEVUELTOS
     =========================== -->
<div class="section">
    <h2>📗 Préstamos Devueltos</h2>
    <table>
        <thead>
            <tr>
                <th>Libro</th>
                <th>Usuario</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Fecha Devuelta</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $devueltos->fetch_assoc()): ?>
            <tr>
                <td><?= h($p["titulo"]) ?></td>
                <td><?= h($p["nombre_completo"]) ?></td>
                <td><?= h($p["fecha_prestamo"]) ?></td>
                <td><?= h($p["fecha_devolucion"]) ?></td>
                <td><?= h($p["fecha_dev_real"]) ?></td>
                <td><?= h($p["estado_prestamo"]) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>