<?php
require "includesauth.php";
require "configdatabase.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $sql = "INSERT INTO usuarios (nombre_completo, email, telefono, direccion, dni)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $_POST["nombre"], $_POST["email"], $_POST["telefono"], $_POST["direccion"], $_POST["dni"]);
    $stmt->execute();

    header("Location: usuarios.php");
    exit();
}
?>

<h2>Crear usuario</h2>

<form method="POST">
    <input name="nombre" placeholder="Nombre completo" required>
    <input name="email" placeholder="Email" required>
    <input name="telefono" placeholder="Teléfono">
    <input name="direccion" placeholder="Dirección">
    <input name="dni" placeholder="DNI" required>
    <button>Guardar usuario</button>
</form>
