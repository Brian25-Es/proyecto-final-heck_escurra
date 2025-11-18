<?php
// admin_prestamos.php
error_reporting(E_ALL); ini_set('display_errors',1); header("Cache-Control:no-store");
session_start(); require "backend/configdatabase.php";
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"]!=='admin') { header("Location: login.php"); exit(); }

$filter = $_GET['filter'] ?? 'activo';
$where = "p.estado_prestamo = 'Activo'";
if ($filter==='vencido') $where="p.estado_prestamo = 'Vencido'";
if ($filter==='devuelto') $where="p.estado_prestamo = 'Devuelto'";
if ($filter==='todos') $where="1=1";

$res = $conn->query("
  SELECT p.*, l.titulo, u.nombre_completo
  FROM prestamos p
  LEFT JOIN libros l ON p.ID_Libro = l.ID
  LEFT JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
  WHERE $where
  ORDER BY p.created_at DESC
");
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Admin - Préstamos</title>
<style>body{font-family:Arial;margin:20px;background:#f5f5f5}.card{background:#fff;padding:12px} table{width:100%;border-collapse:collapse}th,td{border:1px solid #eee;padding:6px}</style>
</head>
<body>
<div class="card">
  <h2>Préstamos - filtro: <?=htmlspecialchars($filter)?></h2>
  <div class="small"><a href="admin_prestamos.php?filter=activo">Activos</a> | <a href="admin_prestamos.php?filter=vencido">Vencidos</a> | <a href="admin_prestamos.php?filter=devuelto">Devueltos</a> | <a href="admin_prestamos.php?filter=todos">Todos</a> | <a href="dashboard_admin.php">Volver admin</a></div>
  <table style="margin-top:10px"><thead><tr><th>ID</th><th>Libro</th><th>Usuario</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Fecha real</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php while($r=$res->fetch_assoc()): ?>
  <tr>
    <td><?=htmlspecialchars($r['ID_Prestamo'])?></td>
    <td><?=htmlspecialchars($r['titulo'])?></td>
    <td><?=htmlspecialchars($r['nombre_completo'])?></td>
    <td><?=htmlspecialchars($r['fecha_prestamo'])?></td>
    <td><?=htmlspecialchars($r['fecha_devolucion'])?></td>
    <td><?=htmlspecialchars($r['fecha_dev_real'])?></td>
    <td><?=htmlspecialchars($r['estado_prestamo'])?></td>
    <td>
      <?php if($r['estado_prestamo'] !== 'Devuelto'): ?>
        <form method="POST" action="admin_devoluciones.php" style="display:inline">
            <input type="hidden" name="ID_Prestamo" value="<?=htmlspecialchars($r['ID_Prestamo'])?>">
            <button type="submit">Marcar devuelto</button>
        </form>
      <?php else: ?>
        Devuelto
      <?php endif; ?>
    </td>
  </tr>
<?php endwhile; ?>
  </tbody></table>
</div>
</body></html>