<?php
$host = "localhost";
$user = "adminphp";
$pass = "TuContraseñaSegura";
$db   = "proyecto_biblioteca";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}
?>