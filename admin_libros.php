<?php
// admin_libros.php CORREGIDO
error_reporting(E_ALL); 
ini_set('display_errors',1); 
header("Cache-Control:no-store");

session_start(); 
require "backend/configdatabase.php";

// Solo admin puede acceder
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_rol"] !== 'admin') {
    if ($_SERVER['REQUEST_METHOD']==='POST' || isset($_GET['action'])) {
        http_response_code(403);
        echo json_encode(['error'=>'Acceso denegado']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

/* ===============================
   AJAX ENDPOINTS
   =============================== */

if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // Listar libros
    if ($action === 'list') {
        $res = $conn->query("SELECT ID, titulo, autor, isbn, editorial, `año`, categoria, estado FROM libros ORDER BY ID DESC");

        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;

        echo json_encode(['ok'=>true, 'data'=>$rows]);
        exit();
    }

    // Obtener libro por ID
    if ($action === 'get' && isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $stmt = $conn->prepare("
            SELECT ID, titulo, autor, isbn, editorial, `año`, categoria, descripcion, estado 
            FROM libros 
            WHERE ID = ?
        ");
        $stmt->bind_param("i",$id);
        $stmt->execute();

        echo json_encode(['ok'=>true,'data'=>$stmt->get_result()->fetch_assoc()]);
        exit();
    }

    echo json_encode(['ok'=>false,'error'=>'Acción inválida']);
    exit();
}

/* ===============================
   POST: CREATE / UPDATE / DELETE
   =============================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $act = $_POST['action'];

    /* Crear libro */
    if ($act === 'create') {
        $stmt = $conn->prepare("
            INSERT INTO libros (titulo, autor, isbn, editorial, `año`, categoria, descripcion, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Disponible')
        ");

        $stmt->bind_param(
            "ssssiss",
            $_POST["titulo"],
            $_POST["autor"],
            $_POST["isbn"],
            $_POST["editorial"],
            $_POST["año"],
            $_POST["categoria"],
            $_POST["descripcion"]
        );

        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro creado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    /* Actualizar libro */
    if ($act === 'update' && isset($_POST['ID'])) {
        $stmt = $conn->prepare("
            UPDATE libros 
            SET titulo=?, autor=?, isbn=?, editorial=?, `año`=?, categoria=?, descripcion=?, estado=?
            WHERE ID=?
        ");

        $stmt->bind_param(
            "ssssisssi",
            $_POST["titulo"],
            $_POST["autor"],
            $_POST["isbn"],
            $_POST["editorial"],
            $_POST["año"],
            $_POST["categoria"],
            $_POST["descripcion"],
            $_POST["estado"],
            $_POST["ID"]
        );

        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro actualizado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    /* Eliminar libro */
    if ($act === 'delete' && isset($_POST['ID'])) {
        $stmt = $conn->prepare("DELETE FROM libros WHERE ID=?");
        $stmt->bind_param("i", $_POST['ID']);

        if ($stmt->execute()) echo json_encode(['ok'=>true,'message'=>'Libro eliminado']);
        else echo json_encode(['ok'=>false,'error'=>$conn->error]);
        exit();
    }

    echo json_encode(['ok'=>false,'error'=>'Acción POST inválida']);
    exit();
}

/* ===============================
   INTERFAZ HTML
   =============================== */
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Libros</title>

<style>
body { font-family: Arial; margin: 20px; background: #f5f5f5; }
.card { background: white; padding: 12px; border-radius: 6px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ddd; padding: 6px; }
</style>

</head>
<body>

<div class="card">
    <h2>Libros</h2>

    <button id="btnNew">+ Nuevo libro</button>
    <a href="dashboard_admin.php">Volver admin</a>

    <input id="search" placeholder="Buscar..." style="margin-top:8px;padding:6px;width:320px">

    <div id="listArea" style="margin-top:8px"></div>
</div>


<!-- MODAL -->
<div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     justify-content:center;align-items:center">

    <div style="background:white;padding:14px;min-width:500px;border-radius:6px">
        <h3 id="title">Nuevo libro</h3>

        <form id="form">
            <input type="hidden" name="ID" id="ID">

            <input name="titulo" id="titulo" placeholder="Título" required style="width:100%;padding:6px"><br><br>

            <input name="autor" id="autor" placeholder="Autor" style="width:49%;padding:6px">
            <input name="editorial" id="editorial" placeholder="Editorial" style="width:49%;padding:6px"><br><br>

            <input name="isbn" id="isbn" placeholder="ISBN" style="width:49%;padding:6px">
            <input name="año" id="año" placeholder="Año" style="width:49%;padding:6px"><br><br>

            <input name="categoria" id="categoria" placeholder="Categoría" style="width:100%;padding:6px"><br><br>

            <textarea name="descripcion" id="descripcion" placeholder="Descripción" style="width:100%;height:60px"></textarea><br><br>

            <select name="estado" id="estado" style="padding:6px;width:100%">
                <option value="Disponible">Disponible</option>
                <option value="Prestado">Prestado</option>
            </select><br><br>

            <div style="text-align:right">
                <button type="button" id="cancel">Cancelar</button>
                <button type="submit">Guardar</button>
            </div>
        </form>
    </div>

</div>


<script>
const api = "admin_libros.php";

function escapeHtml(t){
    if(!t) return "";
    return t.replace(/[&<>"']/g, m=>({
        "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"
    }[m]));
}

async function load(){
    const r = await fetch(api+"?action=list");
    const j = await r.json();
    render(j.data);
}

function render(data){
    let html = `
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Título</th><th>Autor</th><th>Año</th><th>Estado</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
    `;

    data.forEach(r=>{
        html += `
        <tr>
            <td>${r.ID}</td>
            <td>${escapeHtml(r.titulo)}</td>
            <td>${escapeHtml(r.autor)}</td>
            <td>${escapeHtml(r["año"])}</td>
            <td>${escapeHtml(r.estado)}</td>
            <td>
                <button onclick="edit(${r.ID})">Editar</button>
                <button onclick="del(${r.ID})">Eliminar</button>
            </td>
        </tr>`;
    });

    html += "</tbody></table>";
    document.getElementById("listArea").innerHTML = html;
}

document.getElementById("btnNew").onclick = ()=> openModal();

function openModal(edit=false){
    document.getElementById("modal").style.display="flex";
    document.getElementById("form").reset();
    document.getElementById("ID").value="";
    document.getElementById("title").innerText = edit ? "Editar libro" : "Nuevo libro";
}

document.getElementById("cancel").onclick = ()=>{
    document.getElementById("modal").style.display="none";
};

document.getElementById("form").onsubmit = async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append("action", fd.get("ID") ? "update" : "create");

    const r = await fetch(api, {method:"POST", body:fd});
    const j = await r.json();

    if(j.ok){
        alert(j.message);
        document.getElementById("modal").style.display="none";
        load();
    } else alert(j.error);
};

async function edit(id){
    const r = await fetch(api+"?action=get&id="+id);
    const j = await r.json();
    const d = j.data;

    openModal(true);

    document.getElementById("ID").value = d.ID;
    document.getElementById("titulo").value = d.titulo;
    document.getElementById("autor").value = d.autor;
    document.getElementById("editorial").value = d.editorial;
    document.getElementById("isbn").value = d.isbn;
    document.getElementById("año").value = d["año"];
    document.getElementById("categoria").value = d.categoria;
    document.getElementById("descripcion").value = d.descripcion;
    document.getElementById("estado").value = d.estado;
}

async function del(id){
    if(!confirm("¿Eliminar libro?")) return;

    const fd = new FormData();
    fd.append("action","delete");
    fd.append("ID", id);

    const r = await fetch(api,{method:"POST",body:fd});
    const j = await r.json();

    if(j.ok){
        alert("Eliminado");
        load();
    } else alert(j.error);
}

document.getElementById("search").oninput = function(){
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll("#listArea table tbody tr");
    rows.forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none";
    });
};

load();
</script>

</body>
</html>
