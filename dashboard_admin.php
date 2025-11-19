<?php
session_start();
require "backend/configdatabase.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["usuario_rol"] !== "admin") {
    header("Location: dashboard.php");
    exit();
}

$adminNombre = $_SESSION["usuario_nombre"];
$conn->query("
    UPDATE prestamos
    SET estado_prestamo = 'Vencido'
    WHERE estado_prestamo = 'Activo'
    AND fecha_devolucion < CURDATE()
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
    <meta charset="utf-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="css/styleDashboard.css">
    <style>
        /* Pequeñas utilidades para el admin (puedes moverlas a tu CSS) */
        .form-row { display:flex; gap:10px; margin-bottom:8px; }
        .form-row input, .form-row select, .form-row textarea { flex:1; padding:6px; }
        .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center; }
        .modal { background:white; padding:18px; border-radius:8px; width:720px; max-width:95%; box-shadow:0 6px 30px rgba(0,0,0,0.2); }
        .modal h3 { margin-top:0; }
        .actions { display:flex; gap:8px; }
    </style>
</head>
<body>
<header>Panel de Administración — Bienvenido <?= htmlspecialchars($adminNombre) ?></header>

<div class="tab-menu">
    <button class="active" onclick="openTab('libros')">📚 Libros</button>
    <button onclick="openTab('usuarios')">👤 Usuarios</button>
    <button onclick="openTab('prestamos')">📖 Préstamos</button>
    <button onclick="openTab('cuentas')">👥 Cuentas sistema</button>
    <button onclick="openTab('sistema')">⚙ Opciones</button>
    <button class="btn-danger" onclick="window.location='logout.php'" style="flex:0.6;">🚪 Salir</button>
</div>

<!-- TAB LIBROS -->
<div id="libros" class="tab-content active">
    <h2>Gestión de libros</h2>
    <div style="margin-bottom:12px;">
        <button class="btn" onclick="openModal('modal-libro', 'crear')">➕ Agregar libro</button>
        <button class="btn" onclick="loadLibros()">🔄 Recargar</button>
    </div>

    <table id="tabla-libros">
        <thead>
            <tr><th>ID</th><th>Título</th><th>Autor</th><th>Editorial</th><th>Año</th><th>Categoría</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- TAB USUARIOS -->
<div id="usuarios" class="tab-content">
    <h2>Gestión de usuarios</h2>
    <div style="margin-bottom:12px;">
        <button class="btn" onclick="openModal('modal-usuario', 'crear')">➕ Crear usuario</button>
        <button class="btn" onclick="loadUsuarios()">🔄 Recargar</button>
    </div>

    <table id="tabla-usuarios">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>DNI</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- TAB PRESTAMOS -->
<div id="prestamos" class="tab-content">
    <h2>Préstamos (Activos / Vencidos / Devueltos)</h2>
    <div style="margin-bottom:12px;">
        <button class="btn" onclick="loadPrestamos()">🔄 Recargar</button>
    </div>

    <table id="tabla-prestamos">
        <thead>
            <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Prestado</th><th>Devolución</th><th>Dev. real</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- TAB: CUENTAS DEL SISTEMA -->
<div id="cuentas" class="tab-content">
    <h2>Cuentas del Sistema (Administradores / Bibliotecarios)</h2>

    <div style="margin-bottom:12px;">
        <button class="btn" onclick="openModal('modal-cuenta', 'crear')">➕ Crear cuenta</button>
        <button class="btn" onclick="loadCuentas()">🔄 Recargar</button>
    </div>

    <table id="tabla-cuentas">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>


<!-- TAB SISTEMA -->
<div id="sistema" class="tab-content">
    <h2>Opciones del Administrador</h2>
    <p>Editar tu perfil:</p>
    <button class="btn" onclick="window.location='editar_usuario.php'">✏ Editar Perfil</button>
</div>

<!-- MODAL: Usuario del sistema -->
<div id="modal-cuenta-backdrop" class="modal-backdrop" role="dialog">
    <div class="modal" id="modal-cuenta">
        <h3 id="modal-cuenta-title">Crear cuenta</h3>

        <div>
            <div class="form-row">
                <input id="cuenta-user" placeholder="Usuario">
                <input id="cuenta-nombre" placeholder="Nombre">
            </div>

            <div class="form-row">
                <input id="cuenta-email" placeholder="Email">
                <select id="cuenta-rol">
                    <option value="admin">admin</option>
                    <option value="bibliotecario">bibliotecario</option>
                </select>
            </div>

            <div class="form-row">
                <input id="cuenta-password" type="password" placeholder="Contraseña (opcional al editar)">
            </div>

            <input type="hidden" id="cuenta-id">

            <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
                <button class="btn" onclick="saveCuenta()">Guardar</button>
                <button class="btn-danger" onclick="closeModal('modal-cuenta-backdrop')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Libro (crear / editar) -->
<div id="modal-libro-backdrop" class="modal-backdrop" role="dialog">
    <div class="modal" id="modal-libro">
        <h3 id="modal-libro-title">Agregar libro</h3>
        <div>
            <div class="form-row">
                <input id="libro-titulo" placeholder="Título">
                <input id="libro-autor" placeholder="Autor">
            </div>
            <div class="form-row">
                <input id="libro-isbn" placeholder="ISBN">
                <input id="libro-editorial" placeholder="Editorial">
                <input id="libro-ano" placeholder="Año">
            </div>
            <div class="form-row">
                <input id="libro-categoria" placeholder="Categoría">
                <select id="libro-estado"><option>Disponible</option><option>Prestado</option></select>
            </div>
            <div style="margin-top:8px;">
                <textarea id="libro-descripcion" rows="4" placeholder="Descripción" style="width:100%;"></textarea>
            </div>

            <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
                <button class="btn" onclick="saveLibro()">Guardar</button>
                <button class="btn-danger" onclick="closeModal('modal-libro-backdrop')">Cancelar</button>
            </div>
            <input type="hidden" id="libro-id">
        </div>
    </div>
</div>

<!-- MODAL: Usuario (crear / editar) -->
<div id="modal-usuario-backdrop" class="modal-backdrop" role="dialog">
    <div class="modal" id="modal-usuario">
        <h3 id="modal-usuario-title">Crear usuario</h3>
        <div>
            <div class="form-row">
                <input id="usuario-nombre" placeholder="Nombre completo">
                <input id="usuario-email" placeholder="Email">
            </div>
            <div class="form-row">
                <input id="usuario-telefono" placeholder="Teléfono">
                <input id="usuario-dni" placeholder="DNI">
            </div>
            <div class="form-row">
                <select id="usuario-estado"><option value="activo">activo</option><option value="suspendido">suspendido</option></select>
            </div>

            <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
                <button class="btn" onclick="saveUsuario()">Guardar</button>
                <button class="btn-danger" onclick="closeModal('modal-usuario-backdrop')">Cancelar</button>
            </div>
            <input type="hidden" id="usuario-id">
        </div>
    </div>
</div>

<script>
/* TAB SWITCH */
function openTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');

    document.querySelectorAll('.tab-menu button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    // carga la data correspondiente si necesario
    if (tabName === 'libros') loadLibros();
    if (tabName === 'usuarios') loadUsuarios();
    if (tabName === 'prestamos') loadPrestamos();
}

/* ---------- LIBROS (CRUD) ---------- */
async function loadLibros() {
    try {
        const res = await fetch('backend/libros.php?action=listar');
        const data = await res.json();
        const tbody = document.querySelector('#tabla-libros tbody');
        tbody.innerHTML = '';
        data.forEach(l => {
            tbody.innerHTML += `
                <tr>
                    <td>${l.ID}</td>
                    <td>${escapeHtml(l.titulo)}</td>
                    <td>${escapeHtml(l.autor)}</td>
                    <td>${escapeHtml(l.editorial)}</td>
                    <td>${escapeHtml(l['año'])}</td>
                    <td>${escapeHtml(l.categoria)}</td>
                    <td>${escapeHtml(l.estado)}</td>
                    <td class="actions">
                        <button class="btn-warning" onclick="openModal('modal-libro', 'editar', ${l.ID})">Editar</button>
                        <button class="btn-danger" onclick="confirmEliminarLibro(${l.ID})">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    } catch (err) {
        alert('Error al cargar libros: ' + err);
    }
}

function openModal(backdropId, mode, id = null) {
    if (backdropId === 'modal-libro') {
        document.getElementById('modal-libro-backdrop').style.display = 'flex';
        if (mode === 'crear') {
            document.getElementById('modal-libro-title').textContent = 'Agregar libro';
            document.getElementById('libro-id').value = '';
            document.getElementById('libro-titulo').value = '';
            document.getElementById('libro-autor').value = '';
            document.getElementById('libro-isbn').value = '';
            document.getElementById('libro-editorial').value = '';
            document.getElementById('libro-ano').value = '';
            document.getElementById('libro-categoria').value = '';
            document.getElementById('libro-descripcion').value = '';
            document.getElementById('libro-estado').value = 'Disponible';
        } else if (mode === 'editar') {
            // cargar datos del libro desde backend y llenar campos
            fetch('backend/libros.php?action=listar')
                .then(r => r.json())
                .then(list => {
                    const libro = list.find(x => parseInt(x.ID) === parseInt(id));
                    if (!libro) { alert('Libro no encontrado'); return; }
                    document.getElementById('modal-libro-title').textContent = 'Editar libro';
                    document.getElementById('libro-id').value = libro.ID;
                    document.getElementById('libro-titulo').value = libro.titulo;
                    document.getElementById('libro-autor').value = libro.autor;
                    document.getElementById('libro-isbn').value = libro.isbn;
                    document.getElementById('libro-editorial').value = libro.editorial;
                    document.getElementById('libro-ano').value = libro['año'];
                    document.getElementById('libro-categoria').value = libro.categoria;
                    document.getElementById('libro-descripcion').value = libro.descripcion || '';
                    document.getElementById('libro-estado').value = libro.estado;
                });
            document.getElementById('modal-libro-backdrop').style.display = 'flex';
        }
        return;
    }

    if (backdropId === 'modal-usuario') {
        document.getElementById('modal-usuario-backdrop').style.display = 'flex';
        if (mode === 'crear') {
            document.getElementById('modal-usuario-title').textContent = 'Crear usuario';
            document.getElementById('usuario-id').value = '';
            document.getElementById('usuario-nombre').value = '';
            document.getElementById('usuario-email').value = '';
            document.getElementById('usuario-telefono').value = '';
            document.getElementById('usuario-dni').value = '';
            document.getElementById('usuario-estado').value = 'activo';
        } else if (mode === 'editar') {
            fetch('backend/user.php?action=listar')
                .then(r => r.json())
                .then(list => {
                    const u = list.find(x => parseInt(x.ID_Usuario) === parseInt(id));
                    if (!u) { alert('Usuario no encontrado'); return; }
                    document.getElementById('modal-usuario-title').textContent = 'Editar usuario';
                    document.getElementById('usuario-id').value = u.ID_Usuario;
                    document.getElementById('usuario-nombre').value = u.nombre_completo;
                    document.getElementById('usuario-email').value = u.email;
                    document.getElementById('usuario-telefono').value = u.telefono;
                    document.getElementById('usuario-dni').value = u.dni;
                    document.getElementById('usuario-estado').value = u.estado;
                });
            document.getElementById('modal-usuario-backdrop').style.display = 'flex';
        }
        return;
    }

    if (backdropId === "modal-cuenta") {
        document.getElementById("modal-cuenta-backdrop").style.display = "flex";

        if (mode === "crear") {
            document.getElementById("modal-cuenta-title").textContent = "Crear cuenta";
            document.getElementById("cuenta-id").value = "";
            document.getElementById("cuenta-user").value = "";
            document.getElementById("cuenta-nombre").value = "";
            document.getElementById("cuenta-email").value = "";
            document.getElementById("cuenta-rol").value = "bibliotecario";
            document.getElementById("cuenta-password").value = "";
        } 
        else if (mode === "editar") {
            fetch("backend/usuario_sistema.php?action=listar")
                .then(r => r.json())
                .then(list => {
                    const u = list.find(x => x.ID_User == id);
                    if (!u) return alert("Usuario no encontrado");

                    document.getElementById("modal-cuenta-title").textContent = "Editar cuenta";

                    document.getElementById("cuenta-id").value = u.ID_User;
                    document.getElementById("cuenta-user").value = u.user;
                    document.getElementById("cuenta-nombre").value = u.nombre;
                    document.getElementById("cuenta-email").value = u.email;
                    document.getElementById("cuenta-rol").value = u.rol;
                    document.getElementById("cuenta-password").value = "";
                });
        }

        return;
    }
}

function closeModal(backdropId) {
    document.getElementById(backdropId).style.display = 'none';
}

async function loadCuentas() {
    try {
        const res = await fetch("backend/usuario_sistema.php?action=listar");
        const data = await res.json();

        const tbody = document.querySelector("#tabla-cuentas tbody");
        tbody.innerHTML = "";

        data.forEach(c => {
            tbody.innerHTML += `
                <tr>
                    <td>${c.ID_User}</td>
                    <td>${c.user}</td>
                    <td>${c.nombre}</td>
                    <td>${c.email}</td>
                    <td>${c.rol}</td>
                    <td>${c.created_at}</td>
                    <td>
                        <button class="btn-warning" onclick="openModal('modal-cuenta','editar',${c.ID_User})">Editar</button>
                        <button class="btn-danger" onclick="deleteCuenta(${c.ID_User})">Eliminar</button>
                    </td>
                </tr>`;
        });
    } catch (error) {
        alert("Error cargando cuentas: " + error);
    }
}

async function saveCuenta() {
    const id = document.getElementById("cuenta-id").value;

    const payload = {
        user: document.getElementById("cuenta-user").value.trim(),
        nombre: document.getElementById("cuenta-nombre").value.trim(),
        email: document.getElementById("cuenta-email").value.trim(),
        rol: document.getElementById("cuenta-rol").value,
        password: document.getElementById("cuenta-password").value.trim()
    };

    let url = "backend/usuario_sistema.php?action=insertar";

    if (id) {
        payload.ID_User = id;
        url = "backend/usuario_sistema.php?action=editar";
    }

    const res = await fetch(url, {
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body: JSON.stringify(payload)
    });

    const text = await res.text();

    if (text.trim() === "ok") {
        closeModal("modal-cuenta-backdrop");
        loadCuentas();
        alert("Cuenta guardada correctamente");
    } else {
        alert("Error: " + text);
    }
}

function deleteCuenta(id) {
    if (!confirm("¿Eliminar cuenta del sistema?")) return;

    fetch("backend/usuario_sistema.php?action=borrar&id=" + id)
        .then(r => r.text())
        .then(t => {
            if (t.trim() === "ok") {
                loadCuentas();
                alert("Cuenta eliminada");
            } else {
                alert("Error eliminando: " + t);
            }
        });
}

/* SAVE libro (crear o editar) */
async function saveLibro() {
    const id = document.getElementById('libro-id').value;
    const payload = {
        titulo: document.getElementById('libro-titulo').value.trim(),
        autor: document.getElementById('libro-autor').value.trim(),
        isbn: document.getElementById('libro-isbn').value.trim(),
        editorial: document.getElementById('libro-editorial').value.trim(),
        "año": document.getElementById('libro-ano').value.trim(),
        categoria: document.getElementById('libro-categoria').value.trim(),
        descripcion: document.getElementById('libro-descripcion').value.trim(),
        estado: document.getElementById('libro-estado').value
    };

    try {
        if (!payload.titulo) { alert('El título es obligatorio'); return; }

        let url = 'backend/libros.php?action=insertar';
        if (id) {
            payload.ID = id;
            url = 'backend/libros.php?action=editar';
        }

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const text = await res.text();
        if (text.trim() === 'ok') {
            closeModal('modal-libro-backdrop');
            loadLibros();
            alert('Libro guardado correctamente');
        } else {
            alert('Error guardando libro: ' + text);
        }
    } catch (err) {
        alert('Error: ' + err);
    }
}

function confirmEliminarLibro(id) {
    if (!confirm('¿Eliminar libro ID ' + id + '?')) return;
    fetch('backend/libros.php?action=borrar&id=' + id)
        .then(r => r.text())
        .then(t => {
            if (t.trim() === 'ok') {
                loadLibros();
                alert('Libro eliminado');
            } else {
                alert('Error al eliminar: ' + t);
            }
        });
}

/* ---------- USUARIOS (CRUD) ---------- */
async function loadUsuarios() {
    try {
        const res = await fetch('backend/user.php?action=listar');
        const data = await res.json();
        const tbody = document.querySelector('#tabla-usuarios tbody');
        tbody.innerHTML = '';
        data.forEach(u => {
            tbody.innerHTML += `
                <tr>
                    <td>${u.ID_Usuario}</td>
                    <td>${escapeHtml(u.nombre_completo)}</td>
                    <td>${escapeHtml(u.email)}</td>
                    <td>${escapeHtml(u.telefono)}</td>
                    <td>${escapeHtml(u.dni)}</td>
                    <td>${escapeHtml(u.estado)}</td>
                    <td class="actions">
                        <button class="btn-warning" onclick="openModal('modal-usuario', 'editar', ${u.ID_Usuario})">Editar</button>
                        <button class="btn-danger" onclick="confirmEliminarUsuario(${u.ID_Usuario})">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    } catch (err) {
        alert('Error al cargar usuarios: ' + err);
    }
}

async function saveUsuario() {
    const id = document.getElementById('usuario-id').value;
    const payload = {
        nombre_completo: document.getElementById('usuario-nombre').value.trim(),
        email: document.getElementById('usuario-email').value.trim(),
        telefono: document.getElementById('usuario-telefono').value.trim(),
        direccion: '', // opcional; no la editamos aquí
        dni: document.getElementById('usuario-dni').value.trim(),
        estado: document.getElementById('usuario-estado').value
    };

    try {
        if (!payload.nombre_completo) { alert('El nombre es obligatorio'); return; }

        let url = 'backend/user.php?action=insertar';
        if (id) {
            payload.ID_Usuario = parseInt(id);
            url = 'backend/user.php?action=editar';
        }

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const text = await res.text();
        if (text.trim() === 'ok') {
            closeModal('modal-usuario-backdrop');
            loadUsuarios();
            alert('Usuario guardado correctamente');
        } else {
            alert('Error guardando usuario: ' + text);
        }
    } catch (err) {
        alert('Error: ' + err);
    }
}

function confirmEliminarUsuario(id) {
    if (!confirm('¿Eliminar usuario ID ' + id + '?')) return;
    fetch('backend/user.php?action=borrar&id=' + id)
        .then(r => r.text())
        .then(t => {
            if (t.trim() === 'ok') {
                loadUsuarios();
                alert('Usuario eliminado');
            } else {
                alert('Error al eliminar usuario: ' + t);
            }
        });
}

/* ---------- PRESTAMOS / DEVOLVER ---------- */
async function loadPrestamos() {
    try {
        const res = await fetch('backend/prestamos.php?action=listar');
        const data = await res.json();
        const tbody = document.querySelector('#tabla-prestamos tbody');
        tbody.innerHTML = '';
        data.forEach(p => {
            tbody.innerHTML += `
                <tr>
                    <td>${p.ID_Prestamo}</td>
                    <td>${escapeHtml(p.usuario || p.nombre_completo || '')}</td>
                    <td>${escapeHtml(p.libro || p.titulo || '')}</td>
                    <td>${escapeHtml(p.fecha_prestamo)}</td>
                    <td>${escapeHtml(p.fecha_devolucion)}</td>
                    <td>${escapeHtml(p.fecha_dev_real || '')}</td>
                    <td>${escapeHtml(p.estado_prestamo)}</td>
                    <td>
                        ${p.estado_prestamo === 'Activo' ? `<button class="btn-warning" onclick="devolverPrestamo(${p.ID_Prestamo}, ${p.ID_Libro})">Devolver</button>` : ''}
                    </td>
                </tr>
            `;
        });
    } catch (err) {
        alert('Error al cargar préstamos: ' + err);
    }
}

function devolverPrestamo(prestamoId, libroId) {
    if (!confirm('Marcar préstamo como devuelto?')) return;
    const fd = new URLSearchParams();
    fd.append('prestamo_id', prestamoId);
    fd.append('libro_id', libroId);

    fetch('backend/devolver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: fd.toString()
    })
    .then(r => r.text())
    .then(t => {
        if (t.trim() === 'ok') {
            alert('Préstamo devuelto correctamente');
            loadPrestamos();
            // recargar tablas de libros/usuarios
            loadLibros();
        } else {
            alert('Error al devolver: ' + t);
        }
    });
}

/* ---------- UTILIDADES ---------- */
function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

/* Inicializar */
document.addEventListener('DOMContentLoaded', () => {
    loadLibros();
    loadUsuarios();
    loadPrestamos();
    loadCuentas();
});
</script>
</body>
</html>