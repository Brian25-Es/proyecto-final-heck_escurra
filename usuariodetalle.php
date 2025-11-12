<?php
require "includesauth.php";
require "configdatabase.php";

$id = $_GET["id"];

// usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// préstamos del usuario
$sql = "SELECT p.*, l.titulo FROM prestamos p
        JOIN libros l ON p.libro_id = l.id
        WHERE p.usuario_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamos = $stmt->get_result();
?>

<h2><?= $usuario["nombre_completo"] ?></h2>
<p>Email: <?= $usuario["email"] ?></p>
<p>DNI: <?= $usuario["dni"] ?></p>
<p>Estado: <?= $usuario["estado"] ?></p>

<h3>Préstamos del usuario</h3>
<table border="1">
<tr><th>Libro</th><th>Fecha devolución</th><th>Estado</th></tr>

<?php while($p = $prestamos->fetch_assoc()): ?>
<tr>
    <td><?= $p["titulo"] ?></td>
    <td><?= $p["fecha_devolucion"] ?></td>
    <td><?= $p["estado"] ?></td>
</tr>
<?php endwhile; ?>

</table>
