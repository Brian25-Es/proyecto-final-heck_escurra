<?php
$host = "localhost";
$user = "root";     // o el usuario que uses
$pass = "";         // tu contraseña si tenés una
$db   = "proyecto_biblioteca";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}
?>