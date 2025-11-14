<?php
session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario actual
$usuario_id = $_SESSION["usuario_id"];
$nombre     = $_SESSION["usuario_nombre"];
$rol        = $_SESSION["usuario_rol"];

// Marcar vencidos
$conn->query("
    UPDATE prestamos
    SET estado_prestamo = 'Vencido'
    WHERE estado_prestamo = 'Activo'
    AND fecha_devolucion < CURDATE()
");

// Listar datos
$libros = $conn->query("SELECT * FROM libros WHERE estado = 'Disponible'");
$usuarios = $conn->query("SELECT ID_Usuario, nombre_completo FROM usuarios ORDER BY nombre_completo ASC");

$prestamosActivos = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo 
    FROM prestamos p
    JOIN libros l ON p.ID_Libro = l.ID
    JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE p.estado_prestamo IN ('Activo','Vencido')
");

$prestamosHistorial = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo
    FROM prestamos p
    JOIN libros l ON p.ID_Libro = l.ID
    JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE p.estado_prestamo = 'Devuelto'
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/styleDashboard.css">
    <meta charset="UTF-8">
    <title>Dashboard Biblioteca</title>

</head>
<body>

<header>
    Sistema Biblioteca — Bienvenido <?= $nombre ?>
</header>

<!-- MENU SUPERIOR DESLIZABLE -->
<div class="tab-menu">
    <button class="active" onclick="openTab('libros')">📚 Libros</button>
    <button onclick="openTab('activos')">📖 Préstamos Activos</button>
    <button onclick="openTab('historial')">🕘 Historial</button>
    <button onclick="openTab('usuario')">👤 Opciones Usuario</button>

    <button class="btn-danger" onclick="window.location='logout.php'" style="flex:0.6;">
        🚪 Salir
    </button>
</div>

<!-- ========================== -->
<!-- CONTENIDO DE CADA TAB -->
<!-- ========================== -->

<!-- TAB: LIBROS -->
<div id="libros" class="tab-content active">
    <h2>Libros disponibles</h2>

    <table>
        <thead>
            <tr>
                <th>Título</th><th>Autor</th><th>Editorial</th><th>Año</th><th>Usuario</th><th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($l = $libros->fetch_assoc()): ?>
            <tr id="row-<?= $l['ID'] ?>">
                <td><?= $l["titulo"] ?></td>
                <td><?= $l["autor"] ?></td>
                <td><?= $l["editorial"] ?></td>
                <td><?= $l["año"] ?></td>

                <td>
                    <select id="usuarioSelect-<?= $l['ID'] ?>">
                        <?php 
                        $usuarios->data_seek(0);
                        while ($u = $usuarios->fetch_assoc()): ?>
                            <option value="<?= $u['ID_Usuario'] ?>"><?= $u['nombre_completo'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>

                <td>
                    <button class="btn" onclick="alquilarLibro(<?= $l['ID'] ?>)">Alquilar</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- TAB: PRÉSTAMOS ACTIVOS -->
<div id="activos" class="tab-content">
    <h2>Préstamos activos</h2>

    <table>
        <thead>
            <tr>
                <th>Usuario</th><th>Libro</th><th>Fecha Préstamo</th><th>Fecha Devolución</th><th>Estado</th><th>Acción</th>
            </tr>
        </thead>
        <tbody id="tabla-prestamos">
            <?php while ($p = $prestamosActivos->fetch_assoc()): ?>
            <tr id="prestamo-<?= $p['ID_Prestamo'] ?>">
                <td><?= $p['nombre_completo'] ?></td>
                <td><?= $p['titulo'] ?></td>
                <td><?= $p['fecha_prestamo'] ?></td>
                <td><?= $p['fecha_devolucion'] ?></td>
                <td><?= $p['estado_prestamo'] ?></td>
                <td>
                    <button class="btn-warning" onclick="devolverPrestamo(<?= $p['ID_Prestamo'] ?>, <?= $p['ID_Libro'] ?>)">
                        Devolver
                    </button>
                </td>
            </tr>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- TAB: HISTORIAL -->
<div id="historial" class="tab-content">
    <h2>Historial de préstamos</h2>

    <table>
        <thead>
            <tr><th>Usuario</th><th>Libro</th><th>Prestado</th><th>Devuelto</th><th>Estado</th></tr>
        </thead>
        <tbody>
            <?php while ($h = $prestamosHistorial->fetch_assoc()): ?>
            <tr>
                <td><?= $h['nombre_completo'] ?></td>
                <td><?= $h['titulo'] ?></td>
                <td><?= $h['fecha_prestamo'] ?></td>
                <td><?= $h['fecha_dev_real'] ?></td>
                <td><?= $h['estado_prestamo'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- TAB: OPCIONES USUARIO -->
<div id="usuario" class="tab-content">
    <h2>Opciones de usuario</h2>

    <button class="btn" onclick="window.location='editar_usuario.php'">✏ Editar Perfil</button>
</div>

<!-- ====================================== -->
<!-- MODAL DE USUARIOS PARA BIBLIOTECARIO -->
<!-- ====================================== -->
<div id="modalUsuarios">
    <div style="background:white; padding:20px; width:350px; border-radius:8px;">
        <h3>Seleccionar usuario</h3>

        <select id="selectUsuario" style="width:100%; padding:8px;"></select>

        <br><br>

        <button onclick="confirmarPrestamo()" class="btn" style="width:100%;">Confirmar</button>
        <br><br>
        <button onclick="cerrarModal()" class="btn-danger" style="width:100%;">Cancelar</button>
    </div>
</div>

<script>
/* CAMBIAR TABS */
function openTab(tabName) {
    document.querySelectorAll(".tab-content").forEach(t => t.classList.remove("active"));
    document.getElementById(tabName).classList.add("active");

    document.querySelectorAll(".tab-menu button").forEach(b => b.classList.remove("active"));
    event.target.classList.add("active");
}

/* =============================== */
/* ALQUILAR LIBRO (YA TENÍAS ESTO) */
/* =============================== */
function alquilarLibro(idLibro) {
    let usuarioSelect = document.getElementById("usuarioSelect-" + idLibro);
    let usuario_id = usuarioSelect.value;

    fetch("prestar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `libro_id=${idLibro}&usuario_id=${usuario_id}`
    })
    .then(res => res.text())
    .then(res => {
        alert("Libro prestado correctamente");
        document.getElementById("row-" + idLibro).remove();
        actualizarPrestamos();
    });
}

/* DEVOLVER */
function devolverPrestamo(idPrestamo, idLibro) {
    if (!confirm("¿Dar de baja este préstamo?")) return;

    fetch("backend/devolver.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `prestamo_id=${idPrestamo}&libro_id=${idLibro}`
    })
    .then(res => res.text())
    .then(res => {
        alert("Préstamo devuelto correctamente");
        document.getElementById("prestamo-" + idPrestamo).remove();
        actualizarPrestamos();
    });
}

/* ACTUALIZAR TABLA */
function actualizarPrestamos() {
    fetch("backend/api_prestamos_usuario.php")
        .then(res => res.json())
        .then(data => {
            let tabla = document.getElementById("tabla-prestamos");
            tabla.innerHTML = "";

            data.forEach(p => {
                tabla.innerHTML += `
                    <tr id="prestamo-${p.ID_Prestamo}">
                        <td>${p.titulo}</td>
                        <td>${p.fecha_prestamo}</td>
                        <td>${p.fecha_devolucion}</td>
                        <td>${p.estado_prestamo}</td>
                        <td>
                            <button class="btn-warning"
                                onclick="devolverPrestamo(${p.ID_Prestamo}, ${p.ID_Libro})">
                                Devolver
                            </button>
                        </td>
                    </tr>`;
            });
        });
}
</script>

</body>
</html>

