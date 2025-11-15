<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario actual (bibliotecario)
$usuario_id = $_SESSION["usuario_id"];
$nombre     = $_SESSION["usuario_nombre"];
$rol        = $_SESSION["usuario_rol"];

// Listar libros disponibles
$libros = $conn->query("SELECT * FROM libros WHERE estado = 'Disponible'");

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
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Editorial</th>
                    <th>Año</th>
                    <th>Asignar préstamo</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($l = $libros->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($l["titulo"]) ?></td>
                    <td><?= htmlspecialchars($l["autor"]) ?></td>
                    <td><?= htmlspecialchars($l["editorial"]) ?></td>
                    <td><?= htmlspecialchars($l["año"]) ?></td>
                    <td>

                        <!-- FORMULARIO CORRECTO PARA ASIGNAR PRÉSTAMO -->
                        <form method="POST" action="prestar.php">
                            <input type="hidden" name="libro_id" value="<?= $l["ID"] ?>">

                            <!-- SELECT CORREGIDO -->
                            <select name="usuario_id" required>
                                <option value="">-- Seleccionar usuario --</option>
                                <?php
                                $usuarios = $conn->query("SELECT ID_Usuario, nombre_completo FROM usuarios");
                                while ($u = $usuarios->fetch_assoc()):
                                ?>
                                    <option value="<?= $u['ID_Usuario'] ?>">
                                        <?= $u['nombre_completo'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <button class="btn">Asignar préstamo</button>
                        </form>

                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

</main>
</body>
</html>