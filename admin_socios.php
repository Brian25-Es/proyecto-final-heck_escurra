<?php
// admin_socios.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Cache-Control: no-store");
session_start();
require "backend/configdatabase.php";
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"] !== 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
        http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit();
    } else { header("Location: login.php"); exit(); }
}

// AJAX endpoints
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];
    if ($action === 'list') {
        $res = $conn->query("SELECT ID_Usuario, nombre_completo, email, telefono, direccion, dni, estado, created_at FROM usuarios ORDER BY ID_Usuario DESC");
        $rows=[]; while($r=$res->fetch_assoc()) $rows[]=$r;
        echo json_encode(['ok'=>true,'data'=>$rows]); exit();
    }
    if ($action === 'get' && isset($_GET['id'])) {
        $id=intval($_GET['id']);
        $stmt=$conn->prepare("SELECT ID_Usuario, nombre_completo, email, telefono, direccion, dni, estado FROM usuarios WHERE ID_Usuario=?");
        $stmt->bind_param("i",$id); $stmt->execute(); echo json_encode(['ok'=>true,'data'=>$stmt->get_result()->fetch_assoc()]); exit();
    }
    echo json_encode(['ok'=>false,'error'=>'Acción no válida']); exit();
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $act = $_POST['action'];

    if ($act==='create') {
        $nombre = trim($_POST['nombre']??'');
        $email = trim($_POST['email']??'');
        $telefono = trim($_POST['telefono']??'');
        $direccion = trim($_POST['direccion']??'');
        $dni = trim($_POST['dni']??'');
        $estado = $_POST['estado'] ?? 'activo';
        if ($nombre==='') { echo json_encode(['ok'=>false,'error'=>'Nombre requerido']); exit(); }
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo,email,telefono,direccion,dni,estado) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss",$nombre,$email,$telefono,$direccion,$dni,$estado);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Socio creado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    if ($act==='update' && isset($_POST['ID_Usuario'])) {
        $id = intval($_POST['ID_Usuario']);
        $nombre = trim($_POST['nombre']??'');
        $email = trim($_POST['email']??'');
        $telefono = trim($_POST['telefono']??'');
        $direccion = trim($_POST['direccion']??'');
        $dni = trim($_POST['dni']??'');
        $estado = $_POST['estado'] ?? 'activo';
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, email=?, telefono=?, direccion=?, dni=?, estado=? WHERE ID_Usuario=?");
        $stmt->bind_param("ssssssi",$nombre,$email,$telefono,$direccion,$dni,$estado,$id);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Socio actualizado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    if ($act==='delete' && isset($_POST['ID_Usuario'])) {
        $id=intval($_POST['ID_Usuario']);
        $stmt=$conn->prepare("DELETE FROM usuarios WHERE ID_Usuario=?");
        $stmt->bind_param("i",$id);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Socio eliminado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    echo json_encode(['ok'=>false,'error'=>'Acción POST no válida']); exit();
}

// UI HTML
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Admin - Socios</title>
<style>/* simple styles */ body{font-family:Arial;margin:20px;background:#f5f5f5} .card{background:#fff;padding:12px;border-radius:6px} table{width:100%;border-collapse:collapse} th,td{border:1px solid #eee;padding:6px}</style>
</head>
<body>
<div class="card">
    <h2>Socios (usuarios)</h2>
    <div><button id="btnNew" class="btn">+ Nuevo socio</button> <a href="dashboard_admin.php">Volver admin</a></div>
    <input id="search" placeholder="Buscar..." style="margin-top:8px;padding:6px;width:320px">
    <div id="listArea" style="margin-top:10px"></div>
</div>

<!-- modal -->
<div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);justify-content:center;align-items:center">
  <div style="background:#fff;padding:12px;border-radius:6px;min-width:360px">
    <h3 id="title">Nuevo socio</h3>
    <form id="form">
      <input type="hidden" id="ID_Usuario" name="ID_Usuario">
      <div><input name="nombre" id="nombre" placeholder="Nombre completo" required style="width:100%;padding:6px"></div>
      <div style="margin-top:6px"><input name="email" id="email" placeholder="Email" style="width:49%;padding:6px"> <input name="telefono" id="telefono" placeholder="Teléfono" style="width:49%;padding:6px"></div>
      <div style="margin-top:6px"><input name="direccion" id="direccion" placeholder="Dirección" style="width:100%;padding:6px"></div>
      <div style="margin-top:6px"><input name="dni" id="dni" placeholder="DNI" style="width:49%;padding:6px">
      <select name="estado" id="estado" style="width:49%;padding:6px"><option value="activo">activo</option><option value="suspendido">suspendido</option></select></div>
      <div style="text-align:right;margin-top:8px"><button type="button" id="cancel">Cancelar</button> <button type="submit" id="save">Guardar</button></div>
    </form>
  </div>
</div>

<script>
const api = 'admin_socios.php';
async function load(){ const r=await fetch(api+'?action=list'); const j=await r.json(); render(j.data); }
function render(data){
 let html='<table><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>DNI</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
 data.forEach(r=> html+=`<tr><td>${r.ID_Usuario}</td><td>${escapeHtml(r.nombre_completo)}</td><td>${escapeHtml(r.email)}</td><td>${escapeHtml(r.dni)}</td><td>${escapeHtml(r.estado)}</td><td><button onclick="edit(${r.ID_Usuario})">Editar</button> <button onclick="del(${r.ID_Usuario})">Eliminar</button></td></tr>`);
 html+='</tbody></table>'; document.getElementById('listArea').innerHTML=html;
}
document.getElementById('btnNew').addEventListener('click',()=>{openModal();});
function openModal(edit=false){ document.getElementById('modal').style.display='flex'; document.getElementById('form').reset(); document.getElementById('ID_Usuario').value=''; document.getElementById('title').textContent = edit?'Editar socio':'Nuevo socio'; }
document.getElementById('cancel').addEventListener('click',()=>document.getElementById('modal').style.display='none');

document.getElementById('form').addEventListener('submit', async (e)=>{
 e.preventDefault();
 const fd = new FormData(e.target);
 let action = fd.get('ID_Usuario') ? 'update' : 'create';
 fd.append('action', action);
 const res = await fetch(api,{method:'POST',body:fd});
 const j = await res.json();
 if(j.ok){ document.getElementById('modal').style.display='none'; load(); alert(j.message || 'Ok'); } else alert(j.error || 'Error');
});

async function edit(id){
 const r = await fetch(api+'?action=get&id='+encodeURIComponent(id));
 const j = await r.json();
 if(!j.ok) { alert('Error'); return; }
 const d = j.data;
 openModal(true);
 document.getElementById('ID_Usuario').value = d.ID_Usuario;
 document.getElementById('nombre').value = d.nombre_completo;
 document.getElementById('email').value = d.email;
 document.getElementById('telefono').value = d.telefono;
 document.getElementById('direccion').value = d.direccion;
 document.getElementById('dni').value = d.dni;
 document.getElementById('estado').value = d.estado;
}

async function del(id){
 if(!confirm('Eliminar socio?')) return;
 const fd = new FormData(); fd.append('action','delete'); fd.append('ID_Usuario',id);
 const r = await fetch(api,{method:'POST',body:fd}); const j=await r.json();
 if(j.ok){ load(); alert(j.message || 'Eliminado'); } else alert(j.error || 'Error');
}

document.getElementById('search').addEventListener('input', function(){
 const q=this.value.toLowerCase(); const tbody = document.querySelector('#listArea table tbody'); if(!tbody) return;
 for(const tr of tbody.querySelectorAll('tr')) tr.style.display = tr.textContent.toLowerCase().includes(q)?'':'none';
});

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

load();
</script>
</body>
</html>
