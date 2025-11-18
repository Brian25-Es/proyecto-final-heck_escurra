<?php
// admin_usuarios_sistema.php
// Panel + endpoints AJAX para gestionar usuarios del sistema (usuarios_sistema)
// Requisitos: backend/configdatabase.php con $conn (mysqli) y sesión iniciada

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Cache-Control: no-store");

session_start();
require "backend/configdatabase.php";

// Seguridad: solo admin puede entrar y usar endpoints
if (!isset($_SESSION["usuario_id"]) || !isset($_SESSION["usuario_rol"]) || $_SESSION["usuario_rol"] !== 'admin') {
    // Si es AJAX pedir JSON, sino redirigir
    if (isset($_GET['action']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado - solo admin']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// --- Manejo AJAX (JSON) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    header('Content-Type: application/json; charset=utf-8');

    // LISTAR
    if ($action === 'list') {
        $res = $conn->query("SELECT ID_User, user, nombre, email, rol, created_at FROM usuario_sistema ORDER BY ID_User DESC");
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['ok' => true, 'data' => $rows]);
        exit();
    }

    // OBTENER UNO
    if ($action === 'get' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT ID_User, user, nombre, email, rol, created_at FROM usuario_sistema WHERE ID_User = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        echo json_encode(['ok' => true, 'data' => $res]);
        exit();
    }

    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    exit();
}

// Manejo POST (create / update / delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $act = $_POST['action'];

    // CREAR
    if ($act === 'create') {
        $user   = trim($_POST['user'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $rol    = ($_POST['rol'] ?? 'bibliotecario') === 'admin' ? 'admin' : 'bibliotecario';

        if ($user === '' || $pass === '' || $nombre === '') {
            echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
            exit();
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuario_sistema (user, password, nombre, email, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $user, $hash, $nombre, $email, $rol);
        if ($stmt->execute()) {
            echo json_encode(['ok' => true, 'message' => 'Usuario creado']);
        } else {
            // si duplicado en user/email
            echo json_encode(['ok' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        exit();
    }

    // ACTUALIZAR
    if ($act === 'update' && isset($_POST['ID_User'])) {
        $id     = intval($_POST['ID_User']);
        $user   = trim($_POST['user'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $rol    = ($_POST['rol'] ?? 'bibliotecario') === 'admin' ? 'admin' : 'bibliotecario';
        $password = $_POST['password'] ?? '';

        if ($user === '' || $nombre === '') {
            echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
            exit();
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuario_sistema SET user=?, password=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("sssssi", $user, $hash, $nombre, $email, $rol, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuario_sistema SET user=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("ssssi", $user, $nombre, $email, $rol, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['ok' => true, 'message' => 'Usuario actualizado']);
        } else {
            echo json_encode(['ok' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        exit();
    }

    // ELIMINAR
    if ($act === 'delete' && isset($_POST['ID_User'])) {
        $id = intval($_POST['ID_User']);

        // Evitar eliminarse a sí mismo
        if ($id === intval($_SESSION['usuario_id'])) {
            echo json_encode(['ok' => false, 'error' => 'No puedes eliminar el usuario con sesión activa']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM usuario_sistema WHERE ID_User = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['ok' => true, 'message' => 'Usuario eliminado']);
        } else {
            echo json_encode(['ok' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        exit();
    }

    echo json_encode(['ok' => false, 'error' => 'Acción POST no válida']);
    exit();
}

// Si llegamos hasta acá, mostramos la UI HTML (no es AJAX)
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Usuarios del sistema</title>
<style>
body{font-family:Arial;margin:0;padding:20px;background:#f5f5f5}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px}
.card{background:#fff;padding:15px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.06)}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #eee;padding:8px;text-align:left}
th{background:#007bff;color:#fff}
.form-row{display:flex;gap:8px;flex-wrap:wrap}
input,select{padding:6px;border:1px solid #ccc;border-radius:4px}
.btn{padding:6px 10px;border:none;border-radius:4px;background:#28a745;color:#fff;cursor:pointer}
.btn-danger{background:#dc3545}
.small{font-size:0.9em;color:#666}
.modal{position:fixed;inset:0;display:none;justify-content:center;align-items:center;background:rgba(0,0,0,.4)}
.modal .inner{background:#fff;padding:15px;border-radius:6px;min-width:320px}
.close-btn{background:#ccc;padding:4px 8px;border-radius:4px;border:none;cursor:pointer}
</style>
</head>
<body>
<div class="header">
    <h2>Usuarios del sistema (bibliotecarios / admins)</h2>
    <div>
        <button id="btnNew" class="btn">+ Nuevo</button>
        <a href="dashboard_admin.php" class="btn" style="background:#007bff">Volver Admin</a>
    </div>
</div>

<div class="card">
    <input id="search" placeholder="Buscar por usuario o nombre..." style="width:300px;padding:6px">
    <div id="listArea" style="margin-top:10px"></div>
</div>

<!-- Modal (crear / editar) -->
<div id="modal" class="modal" role="dialog" aria-hidden="true">
    <div class="inner">
        <h3 id="modalTitle">Nuevo usuario</h3>
        <form id="formUser">
            <input type="hidden" name="ID_User" id="ID_User">
            <div class="form-row" style="margin-bottom:8px">
                <input name="user" id="user" placeholder="Usuario (login)" required>
                <input type="password" name="password" id="password" placeholder="Contraseña (solo si desea cambiar)">
            </div>
            <div class="form-row" style="margin-bottom:8px">
                <input name="nombre" id="nombre" placeholder="Nombre completo" required style="flex:1">
                <input name="email" id="email" placeholder="Email" style="width:250px">
                <select name="rol" id="rol">
                    <option value="bibliotecario">bibliotecario</option>
                    <option value="admin">admin</option>
                </select>
            </div>
            <div style="text-align:right">
                <button type="button" id="btnCancel" class="close-btn">Cancelar</button>
                <button type="submit" id="btnSave" class="btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// ---------- helpers ----------
const apiUrl = 'admin_usuarios_sistema.php'; // este mismo archivo maneja AJAX

function qs(sel) { return document.querySelector(sel); }
function qsa(sel) { return Array.from(document.querySelectorAll(sel)); }

function showModal(edit=false) {
    qs('#modal').style.display = 'flex';
    qs('#modal').setAttribute('aria-hidden','false');
    if (!edit) {
        qs('#modalTitle').textContent = 'Nuevo usuario';
        qs('#formUser').reset();
        qs('#ID_User').value = '';
    } else {
        qs('#modalTitle').textContent = 'Editar usuario';
    }
}
function hideModal() {
    qs('#modal').style.display = 'none';
    qs('#modal').setAttribute('aria-hidden','true');
}

// ---------- cargar listado ----------
async function loadList() {
    const res = await fetch(apiUrl + '?action=list');
    const json = await res.json();
    if (!json.ok) return alert('Error al obtener usuarios');
    const data = json.data;
    renderList(data);
}

function renderList(data) {
    let html = '<table><thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Creado</th><th>Acciones</th></tr></thead><tbody>';
    for (const r of data) {
        html += `<tr>
            <td>${escapeHtml(r.ID_User)}</td>
            <td>${escapeHtml(r.user)}</td>
            <td>${escapeHtml(r.nombre)}</td>
            <td>${escapeHtml(r.email)}</td>
            <td>${escapeHtml(r.rol)}</td>
            <td>${escapeHtml(r.created_at)}</td>
            <td>
                <button class="btn" data-id="${r.ID_User}" onclick="onEdit(${r.ID_User})">Editar</button>
                <button class="btn btn-danger" data-id="${r.ID_User}" onclick="onDelete(${r.ID_User})">Eliminar</button>
            </td>
        </tr>`;
    }
    html += '</tbody></table>';
    qs('#listArea').innerHTML = html;
}

// ---------- crear / editar ----------
qs('#btnNew').addEventListener('click', () => showModal(false));
qs('#btnCancel').addEventListener('click', hideModal);

qs('#formUser').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = qs('#ID_User').value;
    const form = new FormData(qs('#formUser'));
    if (!form.get('user') || !form.get('nombre')) return alert('Completa usuario y nombre');

    if (!id) {
        // create
        form.append('action','create');
        const res = await fetch(apiUrl, { method: 'POST', body: form });
        const json = await res.json();
        if (json.ok) {
            hideModal(); loadList(); alert(json.message || 'Creado');
        } else {
            alert('Error: ' + (json.error || 'no info'));
        }
    } else {
        // update
        form.append('action','update');
        form.append('ID_User', id);
        const res = await fetch(apiUrl, { method: 'POST', body: form });
        const json = await res.json();
        if (json.ok) {
            hideModal(); loadList(); alert(json.message || 'Actualizado');
        } else {
            alert('Error: ' + (json.error || 'no info'));
        }
    }
});

// ---------- editar (cargar en modal) ----------
async function onEdit(id) {
    const res = await fetch(apiUrl + '?action=get&id=' + encodeURIComponent(id));
    const json = await res.json();
    if (!json.ok) return alert('Error al obtener usuario');
    const u = json.data;
    qs('#ID_User').value = u.ID_User;
    qs('#user').value = u.user;
    qs('#nombre').value = u.nombre;
    qs('#email').value = u.email;
    qs('#rol').value = u.rol;
    qs('#password').value = '';
    showModal(true);
}

// ---------- eliminar ----------
async function onDelete(id) {
    if (!confirm('Eliminar usuario? Esta acción es irreversible.')) return;
    const form = new FormData();
    form.append('action','delete');
    form.append('ID_User', id);
    const res = await fetch(apiUrl, { method: 'POST', body: form });
    const json = await res.json();
    if (json.ok) {
        loadList(); alert(json.message || 'Eliminado');
    } else {
        alert('Error: ' + (json.error || 'no info'));
    }
}

// ---------- búsqueda local simple ----------
qs('#search').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    const rows = qs('#listArea table tbody');
    if (!rows) return;
    for (const tr of rows.querySelectorAll('tr')) {
        const text = tr.textContent.toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
    }
});

// util
function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }

// cargar inicialmente
loadList();
</script>
</body>
</html>