<?php
// admin_estadisticas.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); require "backend/configdatabase.php";
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"]!=='admin') { header("Location: login.php"); exit(); }

// top libros
$top = $conn->query("SELECT l.titulo, COUNT(*) as veces FROM prestamos p INNER JOIN libros l ON p.ID_Libro = l.ID GROUP BY p.ID_Libro ORDER BY veces DESC LIMIT 10");
$top_books = []; while($r=$top->fetch_assoc()) $top_books[]=$r;

// top usuarios
$tu = $conn->query("SELECT u.nombre_completo, COUNT(*) as veces FROM prestamos p INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario GROUP BY p.ID_Usuario ORDER BY veces DESC LIMIT 10");
$top_users = []; while($r=$tu->fetch_assoc()) $top_users[]=$r;

// mensual
$mon = $conn->query("SELECT DATE_FORMAT(fecha_prestamo,'%Y-%m') AS ym, COUNT(*) AS total FROM prestamos GROUP BY ym ORDER BY ym DESC LIMIT 12");
$monthly=[]; while($r=$mon->fetch_assoc()) $monthly[]=$r;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Estadísticas</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>body{font-family:Arial;margin:20px;background:#f5f5f5}.card{background:#fff;padding:12px;border-radius:6px;margin-bottom:12px}</style>
</head><body>
<div class="card"><h3>Top libros</h3><canvas id="chartBooks" width="600" height="200"></canvas></div>
<div class="card"><h3>Top usuarios</h3><canvas id="chartUsers" width="600" height="200"></canvas></div>
<div class="card"><h3>Préstamos por mes (últimos 12)</h3><canvas id="chartMonths" width="800" height="200"></canvas></div>

<script>
const topBooks = <?= json_encode($top_books) ?>;
const topUsers = <?= json_encode($top_users) ?>;
const monthly = <?= json_encode(array_reverse($monthly)) ?>; // invertimos para chronological

// Chart Books
const ctxB = document.getElementById('chartBooks').getContext('2d');
new Chart(ctxB, { type:'bar', data:{
 labels: topBooks.map(r=>r.titulo),
 datasets:[{ label:'Veces prestado', data: topBooks.map(r=>parseInt(r.veces)) }]
}, options:{responsive:true,maintainAspectRatio:false} });

// Chart Users
const ctxU = document.getElementById('chartUsers').getContext('2d');
new Chart(ctxU, { type:'bar', data:{
 labels: topUsers.map(r=>r.nombre_completo),
 datasets:[{ label:'Préstamos', data: topUsers.map(r=>parseInt(r.veces)) }]
}, options:{responsive:true,maintainAspectRatio:false} });

// Chart Months
const ctxM = document.getElementById('chartMonths').getContext('2d');
new Chart(ctxM, { type:'line', data:{
 labels: monthly.map(r=>r.ym),
 datasets:[{ label:'Préstamos', data: monthly.map(r=>parseInt(r.total)), fill:false }]
}, options:{responsive:true,maintainAspectRatio:false} });
</script>
</body></html>
