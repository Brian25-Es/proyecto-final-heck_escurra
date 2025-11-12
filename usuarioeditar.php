<?php
require "includesauth.php";
require "configdatabase.php";

$id = $_GET["id"];

// OBTENER EL USUARIO
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sql = "UPDATE usuarios SET nombre_completo=?, email=?, telefono=?, direccion=?, dni=?, estado=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $_POST["nombre"], $_POST["email"], $_POST["telefono"], $_POST["direccion"], $_POST["dni"], $_POST["estado"], $id);
    $stmt->execute();

    header("Location: usuarios.php");
    exit();
}
?>

<h2>Editar usuario</h2>

<form method="POST">
    <input name="nombre" value="<?= $usuario['nombre_completo'] ?>" required>
    <input name="email" value="<?= $usuario['email'] ?>" required>
    <input name="telefono" value="<?= $usuario['telefono'] ?>">
    <input name="direccion" value="<?= $usuario['direccion'] ?>">
    <input name="dni" value="<?= $usuario['dni'] ?>" required>

    <select name="estado">
        <option value="activo" <?= $usuario['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="suspendido" <?= $usuario['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
    </select>

    <button>Actualizar</button>
</form>
