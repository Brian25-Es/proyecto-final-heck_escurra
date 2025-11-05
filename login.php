<?php
session_start();
require "config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario  = $_POST["usuario"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios_sistema WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["usuario_id"]     = $user["id"];
        $_SESSION["usuario_nombre"] = $user["usuario"];
        $_SESSION["usuario_rol"]    = $user["rol"];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h2>Iniciar sesión</h2>

<form action="" method="POST">
    <input type="text" name="usuario" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Ingresar</button>
</form>

<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

</body>
</html>