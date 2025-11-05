<?php
require "../includes/auth.php";
require "../config/database.php";

$sql = "SELECT * FROM usuarios ORDER BY id DESC";
$result = $conn->query($sql);
?>

<h2>Usuarios</h2>
<a href="crear.php">➕ Nuevo usuario</a>

<table border="1">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>
    <th>DNI</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php while ($u = $result->fetch_assoc()): ?>
<tr>
    <td><?= $u["id"] ?></td>
    <td><?= $u["nombre_completo"] ?></td>
    <td><?= $u["email"] ?></td>
    <td><?= $u["dni"] ?></td>
    <td><?= $u["estado"] ?></td>
    <td>
        <a href="detalle.php?id=<?= $u["id"] ?>">Ver</a> |
        <a href="editar.php?id=<?= $u["id"] ?>">Editar</a>
    </td>
</tr>
<?php endwhile; ?>

</table>