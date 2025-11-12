<?php
session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario actual
$usuario_id = $_SESSION["usuario_id"];
$nombre     = $_SESSION["usuario_nombre"];
$rol        = $_SESSION["usuario_rol"];

// Listar libros disponibles
$libros = $conn->query("SELECT * FROM libros WHERE estado = 'Disponible'");

// Listar préstamos del usuario actual
$prestamos = $conn->query("
    SELECT p.*, l.titulo 
    FROM prestamos p 
    INNER JOIN libros l ON p.ID_Libro = l.ID
    WHERE p.ID_Usuario = $usuario_id AND p.estado_prestamo = 'Activo'
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Biblioteca</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        header { background: #007bff; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        main { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 6px 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .logout { background: #dc3545; }
        .logout:hover { background: #c82333; }
        section { margin-bottom: 30px; }
    </style>
</head>
<body>
<header>
    <h2>Bienvenido, <?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($rol) ?>)</h2>
    <form action="logout.php" method="POST">
        <button type="submit" class="btn logout">Cerrar sesión</button>
    </form>
</header>

<main>
    <section>
        <h3>📚 Libros disponibles</h3>
        <table>
            <thead>
                <tr><th>Título</th><th>Autor</th><th>Editorial</th><th>Año</th><th>Acción</th></tr>
            </thead>
            <tbody>
                <?php while ($l = $libros->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($l["titulo"]) ?></td>
                    <td><?= htmlspecialchars($l["autor"]) ?></td>
                    <td><?= htmlspecialchars($l["editorial"]) ?></td>
                    <td><?= htmlspecialchars($l["año"]) ?></td>
                    <td>
                        <form method="POST" action="prestar.php">
                            <input type="hidden" name="libro_id" value="<?= $l["ID"] ?>">
                            <button class="btn">Alquilar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h3>📖 Mis préstamos activos</h3>
        <table>
            <thead>
                <tr><th>Libro</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Estado</th></tr>
            </thead>
            <tbody>
                <?php while ($p = $prestamos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p["titulo"]) ?></td>
                    <td><?= htmlspecialchars($p["fecha_prestamo"]) ?></td>
                    <td><?= htmlspecialchars($p["fecha_devolucion"]) ?></td>
                    <td><?= htmlspecialchars($p["estado_prestamo"]) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
