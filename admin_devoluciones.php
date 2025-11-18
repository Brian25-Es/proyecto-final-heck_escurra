<?php
// admin_devoluciones.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); require "backend/configdatabase.php";
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"]!=='admin') { header("Location: login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Prestamo'])) {
    $id = intval($_POST['ID_Prestamo']);
    // obtener libro
    $stmt = $conn->prepare("SELECT ID_Libro FROM prestamos WHERE ID_Prestamo = ?");
    $stmt->bind_param("i",$id); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $libro = $r['ID_Libro'] ?? null; $stmt->close();

    // actualizar prestamo
    $stmt = $conn->prepare("UPDATE prestamos SET fecha_dev_real = NOW(), estado_prestamo = 'Devuelto' WHERE ID_Prestamo = ?");
    $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();

    // actualizar libro
    if ($libro) { $stmt = $conn->prepare("UPDATE libros SET estado = 'Disponible' WHERE ID = ?"); $stmt->bind_param("i",$libro); $stmt->execute(); $stmt->close(); }

    header("Location: admin_prestamos.php?filter=activo");
    exit();
}

// si GET mostrar pequeña UI con formulario (opcional)
$id = $_GET['id'] ?? null;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Devolver</title></head><body>
<?php if($id): ?>
  <form method="POST"><input type="hidden" name="ID_Prestamo" value="<?=htmlspecialchars($id)?>"><p>Confirmar devolución del préstamo <?=htmlspecialchars($id)?></p><button type="submit">Confirmar</button></form>
<?php else: ?>
  <p>Acceso directo no permitido. Usa el listado de préstamos.</p>
<?php endif; ?>
</body></html>