<?php
session_start();
require "backend/configdatabase.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario  = $_POST["usuario"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuario_sistema WHERE user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        // Inicio de sesión correcto
        $_SESSION["usuario_id"]     = $user["ID_User"];
        $_SESSION["usuario_nombre"] = $user["nombre"];
        $_SESSION["usuario_rol"]    = $user["rol"];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        input { display: block; margin-bottom: 10px; width: 200px; padding: 8px; }
        button { padding: 8px 12px; }
    </style>
</head>
<body>
    <form action="" method="POST">
        <h2>Iniciar sesión</h2>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    </form>
</body>
</html>