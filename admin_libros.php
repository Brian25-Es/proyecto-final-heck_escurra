<?php
// admin_libros.php
error_reporting(E_ALL); ini_set('display_errors',1); header("Cache-Control:no-store");
session_start(); require "backend/configdatabase.php";
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"]!=='admin') {
    if ($_SERVER['REQUEST_METHOD']==='POST' || isset($_GET['action'])) { http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit(); } else { header("Location: login.php"); exit(); }
}

if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $a=$_GET['action'];
    if ($a==='list') { $r=$conn->query("SELECT ID,titulo,autor,editorial,anio,categoria,isbn,estado,created_at FROM libros ORDER BY ID DESC"); $out=[]; while($row=$r->fetch_assoc()) $out[]=$row; echo json_encode(['ok'=>true,'data'=>$out]); exit(); }
    if ($a==='get' && isset($_GET['id'])) { $id=intval($_GET['id']); $stmt=$conn->prepare("SELECT ID,titulo,autor,editorial,anio,categoria,descripcion,isbn,estado FROM libros WHERE ID=?"); $stmt->bind_param("i",$id); $stmt->execute(); echo json_encode(['ok'=>true,'data'=>$stmt->get_result()->fetch_assoc()]); exit(); }
    echo json_encode(['ok'=>false,'error'=>'Acción inválida']); exit();
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $act=$_POST['action'];
    if ($act==='create') {
        $titulo=$_POST['titulo']??''; $autor=$_POST['autor']??''; $editorial=$_POST['editorial']??''; $anio=$_POST['anio']?:null; $categoria=$_POST['categoria']?:null; $descripcion=$_POST['descripcion']?:null; $isbn=$_POST['isbn']?:null;
        $stmt=$conn->prepare("INSERT INTO libros (titulo,autor,editorial,anio,categoria,descripcion,isbn,estado) VALUES (?,?,?,?,?,?,?,'Disponible')");
        $stmt->bind_param("ssissss",$titulo,$autor,$editorial,$anio,$categoria,$descripcion,$isbn);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro creado']); else echo json_encode(['ok'=>false,'error'=>$conn->error]); exit();
    }
    if ($act==='update' && isset($_POST['ID'])) {
        $id=intval($_POST['ID']); $titulo=$_POST['titulo']??''; $autor=$_POST['autor']??''; $editorial=$_POST['editorial']??''; $anio=$_POST['anio']?:null; $categoria=$_POST['categoria']?:null; $descripcion=$_POST['descripcion']?:null; $isbn=$_POST['isbn']?:null; $estado=$_POST['estado']??'Disponible';
        $stmt=$conn->prepare("UPDATE libros SET titulo=?,autor=?,editorial=?,anio=?,categoria=?,descripcion=?,isbn=?,estado=? WHERE ID=?");
        $stmt->bind_param("ssisssssi",$titulo,$autor,$editorial,$anio,$categoria,$descripcion,$isbn,$estado,$id);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro actualizado']); else echo json_encode(['ok'=>false,'error'=>$conn->error]); exit();
    }
    if ($act==='delete' && isset($_POST['ID'])) {
        $id=intval($_POST['ID']); $stmt=$conn->prepare("DELETE FROM libros WHERE ID=?"); $stmt->bind_param("i",$id);
        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro eliminado']); else echo json_encode(['ok'=>false,'error'=>$conn->error]); exit();
    }
    echo json_encode(['ok'=>false,'error'=>'Acción POST inválida']); exit();
}

// UI
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Admin - Libros</title>
<style>body{font-family:Arial;margin:20px;background:#f5f5f5}.card{background:#fff;padding:12px;border-radius:6px} table{width:100%;border-collapse:collapse}th,td{border:1px solid #eee;padding:6px}</style>
</head>
<body>
<div class="card">
  <h2>Libros</h2>
  <div><button id="btnNew">+ Nuevo libro</button> <a href="dashboard_admin.php">Volver admin</a></div>
  <input id="search" placeholder="Buscar..." style="margin-top:8px;padding:6px;width:320px">
  <div id="listArea" style="margin-top:8px"></div>
</div>

<div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);display:flex;justify-content:center;align-items:center">
  <div style="background:#fff;padding:12px;border-radius:6px;min-width:520px">
    <h3 id="title">Nuevo libro</h3>
    <form id="form">
      <input type="hidden" id="ID" name="ID">
      <div><input name="titulo" id="titulo" placeholder="Titulo" required style="width:100%;padding:6px"></div>
      <div style="margin-top:6px"><input name="autor" id="autor" placeholder="Autor" style="width:49%;padding:6px"> <input name="editorial" id="editorial" placeholder="Editorial" style="width:49%;padding:6px"></div>
      <div style="margin-top:6px"><input name="anio" id="anio" placeholder="Año" style="width:49%;padding:6px"> <input name="categoria" id="categoria" placeholder="Categoría" style="width:49%;padding:6px"></div>
      <div style="margin-top:6px"><input name="isbn" id="isbn" placeholder="ISBN" style="width:49%;padding:6px"> <input name="estado" id="estado" placeholder="Estado" value="Disponible" style="width:49%;padding:6px"></div>
      <div style="margin-top:6px"><textarea name="descripcion" id="descripcion" placeholder="Descripción" style="width:100%;height:80px"></textarea></div>
      <div style="text-align:right;margin-top:8px"><button type="button" id="cancel">Cancelar</button> <button type="submit" id="save">Guardar</button></div>
    </form>
  </div>
</div>

<script>
const api='admin_libros.php';
async function load(){ const r=await fetch(api+'?action=list'); const j=await r.json(); render(j.data); }
function render(data){ let html='<table><thead><tr><th>ID</th><th>Título</th><th>Autor</th><th>Año</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>'; data.forEach(r=>html+=`<tr><td>${r.ID}</td><td>${escapeHtml(r.titulo)}</td><td>${escapeHtml(r.autor)}</td><td>${escapeHtml(r.anio)}</td><td>${escapeHtml(r.estado)}</td><td><button onclick="edit(${r.ID})">Editar</button> <button onclick="del(${r.ID})">Eliminar</button></td></tr>`); html+='</tbody></table>'; document.getElementById('listArea').innerHTML=html;}
document.getElementById('btnNew').addEventListener('click',()=>openModal());
function openModal(edit=false){ document.getElementById('modal').style.display='flex'; document.getElementById('form').reset(); document.getElementById('ID').value=''; document.getElementById('title').textContent = edit?'Editar libro':'Nuevo libro';}
document.getElementById('cancel').addEventListener('click',()=>document.getElementById('modal').style.display='none');
document.getElementById('form').addEventListener('submit',async e=>{ e.preventDefault(); const fd=new FormData(e.target); const id=fd.get('ID'); const action = id? 'update':'create'; fd.append('action', action); const r=await fetch(api,{method:'POST',body:fd}); const j=await r.json(); if(j.ok){ document.getElementById('modal').style.display='none'; load(); alert(j.message || 'OK'); } else alert(j.error || 'Error');});
async function edit(id){ const r=await fetch(api+'?action=get&id='+id); const j=await r.json(); openModal(true); const d=j.data; document.getElementById('ID').value=d.ID; document.getElementById('titulo').value=d.titulo; document.getElementById('autor').value=d.autor; document.getElementById('editorial').value=d.editorial; document.getElementById('anio').value=d.anio; document.getElementById('categoria').value=d.categoria; document.getElementById('isbn').value=d.isbn; document.getElementById('descripcion').value=d.descripcion; document.getElementById('estado').value=d.estado;}
async function del(id){ if(!confirm('Eliminar libro?')) return; const fd=new FormData(); fd.append('action','delete'); fd.append('ID',id); const r=await fetch(api,{method:'POST',body:fd}); const j=await r.json(); if(j.ok){ load(); alert(j.message || 'Eliminado'); } else alert(j.error || 'Error');}
document.getElementById('search').addEventListener('input',function(){ const q=this.value.toLowerCase(); const tbody=document.querySelector('#listArea table tbody'); if(!tbody) return; for(const tr of tbody.querySelectorAll('tr')) tr.style.display = tr.textContent.toLowerCase().includes(q)?'':'none';});
function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
load();
</script>
</body></html>
