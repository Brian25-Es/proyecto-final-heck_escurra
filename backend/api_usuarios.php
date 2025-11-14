<?php
session_start();
require "configdatabase.php";

$result = $conn->query("SELECT ID_Usuario, nombre_completo FROM usuarios ORDER BY nombre_completo ASC");

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>