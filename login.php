<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "backend/configdatabase.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario  = $_POST["usuario"];
    $password = $_POST["password"];

    // Consulta a usuario_sistema
    $sql = "SELECT * FROM usuario_sistema WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        // Guardar datos en sesión
        $_SESSION["usuario_id"]     = $user["id"];
        $_SESSION["usuario_nombre"] = $user["nombre"];
        $_SESSION["usuario_rol"]    = $user["rol"];

        // Redirección según rol
        if ($user["rol"] === "admin") {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } 
    else {
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
        body {
            font-family: Arial;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            width: 260px;
            text-align: center;
        }

        input {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
        }

        button {
            padding: 8px 14px;
            cursor: pointer;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>

</head>
<body>

<form action="" method="POST">
    <h2>Iniciar sesión</h2>

    <input type="text" name="usuario" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>

    <button type="submit">Ingresar</button>

    <?php if(isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
</form>

</body>
</html>
