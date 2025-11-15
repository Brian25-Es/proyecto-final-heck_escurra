<?php
// admin.php - Panel completo de administración
// Guardar en la raíz del proyecto.
// Requisitos: backend/configdatabase.php (mysqli $conn), sesión con $_SESSION["usuario_id"] y $_SESSION["usuario_rol"]=='admin'

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "backend/configdatabase.php";

// --- Seguridad: solo admin ---
if (!isset($_SESSION["usuario_id"]) || !isset($_SESSION["usuario_rol"]) || $_SESSION["usuario_rol"] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Helpers para campos con nombres distintos entre implementaciones ---
function f($row, $candidates, $default = '') {
    foreach ($candidates as $k) {
        if (isset($row[$k])) return $row[$k];
    }
    return $default;
}
function post_int($key) {
    return isset($_POST[$key]) ? intval($_POST[$key]) : null;
}
function esc($s) { return htmlspecialchars($s); }

// --- Procesamiento de acciones (CRUD) ---
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
$action = isset($_GET['action']) ? $_GET['action'] : null;
$redirectTo = 'admin.php?tab=' . urlencode($tab);

// --- BIBLIOTECARIOS (usuarios_sistema) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity']) && $_POST['entity'] === 'bibliotecario') {
    // Crear
    if (isset($_POST['create'])) {
        $usuario = $_POST['usuario'] ?? '';
        $nombre  = $_POST['nombre'] ?? '';
        $email   = $_POST['email'] ?? '';
        $rol     = $_POST['rol'] ?? 'bibliotecario';
        $password_plain = $_POST['password'] ?? '';
        $hash = password_hash($password_plain, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios_sistema (usuario, password, nombre, email, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $usuario, $hash, $nombre, $email, $rol);
        $stmt->execute();
        $stmt->close();
    }

    // Editar
    if (isset($_POST['update']) && isset($_POST['ID_User'])) {
        $id = intval($_POST['ID_User']);
        $usuario = $_POST['usuario'] ?? '';
        $nombre  = $_POST['nombre'] ?? '';
        $email   = $_POST['email'] ?? '';
        $rol     = $_POST['rol'] ?? 'bibliotecario';

        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios_sistema SET usuario=?, password=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("sssssi", $usuario, $hash, $nombre, $email, $rol, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios_sistema SET usuario=?, nombre=?, email=?, rol=? WHERE ID_User=?");
            $stmt->bind_param("ssssi", $usuario, $nombre, $email, $rol, $id);
        }
        $stmt->execute();
        $stmt->close();
    }

    // Eliminar
    if (isset($_POST['delete']) && isset($_POST['ID_User'])) {
        $id = intval($_POST['ID_User']);
        $stmt = $conn->prepare("DELETE FROM usuarios_sistema WHERE ID_User = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: $redirectTo");
    exit();
}

// --- SOCIOS (usuarios) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity']) && $_POST['entity'] === 'socio') {
    // Crear socio
    if (isset($_POST['create'])) {
        $nombre = $_POST['nombre'] ?? '';
        $email  = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $dni = $_POST['dni'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, email, telefono, direccion, dni, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $email, $telefono, $direccion, $dni, $estado);
        $stmt->execute();
        $stmt->close();
    }

    // Editar socio
    if (isset($_POST['update']) && isset($_POST['ID_Usuario'])) {
        $id = intval($_POST['ID_Usuario']);
        $nombre = $_POST['nombre'] ?? '';
        $email  = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $dni = $_POST['dni'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, email=?, telefono=?, direccion=?, dni=?, estado=? WHERE ID_Usuario=?");
        $stmt->bind_param("ssssssi", $nombre, $email, $telefono, $direccion, $dni, $estado, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Eliminar socio
    if (isset($_POST['delete']) && isset($_POST['ID_Usuario'])) {
        $id = intval($_POST['ID_Usuario']);
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE ID_Usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: $redirectTo");
    exit();
}

// --- LIBROS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity']) && $_POST['entity'] === 'libro') {
    // Crear libro
    if (isset($_POST['create'])) {
        $titulo = $_POST['titulo'] ?? '';
        $autor  = $_POST['autor'] ?? '';
        $editorial = $_POST['editorial'] ?? '';
        $anio = $_POST['anio'] ?? null;
        $categoria = $_POST['categoria'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $isbn = $_POST['isbn'] ?? null;

        $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, editorial, anio, categoria, descripcion, isbn, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Disponible')");
        $stmt->bind_param("ssissss", $titulo, $autor, $editorial, $anio, $categoria, $descripcion, $isbn);
        $stmt->execute();
        $stmt->close();
    }

    // Editar libro
    if (isset($_POST['update']) && isset($_POST['ID'])) {
        $id = intval($_POST['ID']);
        $titulo = $_POST['titulo'] ?? '';
        $autor  = $_POST['autor'] ?? '';
        $editorial = $_POST['editorial'] ?? '';
        $anio = $_POST['anio'] ?? null;
        $categoria = $_POST['categoria'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $isbn = $_POST['isbn'] ?? null;
        $estado = $_POST['estado'] ?? 'Disponible';

        $stmt = $conn->prepare("UPDATE libros SET titulo=?, autor=?, editorial=?, anio=?, categoria=?, descripcion=?, isbn=?, estado=? WHERE ID=?");
        $stmt->bind_param("ssisssssi", $titulo, $autor, $editorial, $anio, $categoria, $descripcion, $isbn, $estado, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Eliminar libro
    if (isset($_POST['delete']) && isset($_POST['ID'])) {
        $id = intval($_POST['ID']);
        $stmt = $conn->prepare("DELETE FROM libros WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: $redirectTo");
    exit();
}

// --- PRÉSTAMOS: devolver libro (admin puede marcar devolución) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity']) && $_POST['entity'] === 'devolucion') {
    if (isset($_POST['ID_Prestamo'])) {
        $id = intval($_POST['ID_Prestamo']);

        // obtener datos del préstamo
        $stmt = $conn->prepare("SELECT ID_Libro FROM prestamos WHERE ID_Prestamo = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $libro_id = $row ? intval($row['ID_Libro']) : null;
        $stmt->close();

        // actualizar préstamo
        $stmt = $conn->prepare("UPDATE prestamos SET fecha_dev_real = NOW(), estado_prestamo = 'Devuelto' WHERE ID_Prestamo = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // actualizar libro
        if ($libro_id) {
            $stmt = $conn->prepare("UPDATE libros SET estado = 'Disponible' WHERE ID = ?");
            $stmt->bind_param("i", $libro_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: $redirectTo");
    exit();
}

// --- PRÉSTAMOS: filtros y consulta para visualización ---
$filter = $_GET['filter'] ?? 'activo'; // activo | vencido | devuelto | todos
switch ($filter) {
    case 'vencido':
        $where_pr = "p.estado_prestamo = 'Vencido'";
        break;
    case 'devuelto':
        $where_pr = "p.estado_prestamo = 'Devuelto'";
        break;
    case 'todos':
        $where_pr = "1=1";
        break;
    case 'activo':
    default:
        $where_pr = "p.estado_prestamo = 'Activo'";
        break;
}

// --- ESTADÍSTICAS ---
$stats_top_books = $conn->query("
    SELECT l.ID AS ID, l.titulo, COUNT(*) AS veces
    FROM prestamos p
    INNER JOIN libros l ON p.ID_Libro = l.ID
    GROUP BY p.ID_Libro
    ORDER BY veces DESC
    LIMIT 10
");

$stats_top_users = $conn->query("
    SELECT u.ID_Usuario AS ID, u.nombre_completo, COUNT(*) AS veces
    FROM prestamos p
    INNER JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    GROUP BY p.ID_Usuario
    ORDER BY veces DESC
    LIMIT 10
");

$stats_monthly = $conn->query("
    SELECT DATE_FORMAT(fecha_prestamo, '%Y-%m') AS ym, COUNT(*) AS total
    FROM prestamos
    GROUP BY ym
    ORDER BY ym DESC
    LIMIT 12
");

// --- Obtener listas para formularios ---
$all_biblios = $conn->query("SELECT ID_User, usuario, nombre, email, rol FROM usuarios_sistema ORDER BY ID_User DESC");
$all_socios = $conn->query("SELECT ID_Usuario, nombre_completo, email FROM usuarios ORDER BY ID_Usuario DESC");
$all_libros  = $conn->query("SELECT ID, titulo, autor, estado FROM libros ORDER BY ID DESC");

// --- Obtener préstamos según filtro ---
$prestamos_q = $conn->query("
    SELECT p.*, l.titulo, u.nombre_completo
    FROM prestamos p
    LEFT JOIN libros l ON p.ID_Libro = l.ID
    LEFT JOIN usuarios u ON p.ID_Usuario = u.ID_Usuario
    WHERE $where_pr
    ORDER BY p.created_at DESC
");


// ---------- SALIDA HTML ----------
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Panel</title>
<style>
body{font-family: Arial; margin:0; background:#f5f5f5}
header{background:#222;color:#fff;padding:10px 20px}
.container{padding:20px}
.nav{display:flex;gap:10px;margin-bottom:15px}
.card{background:#fff;padding:15px;border-radius:6px;box-shadow:0 2px 4px rgba(0,0,0,.08);margin-bottom:15px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #eee;padding:8px;text-align:left}
th{background:#007bff;color:#fff}
.form-row{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}
input,select,textarea{padding:6px;border:1px solid #ccc;border-radius:4px}
.btn{padding:6px 10px;border:none;border-radius:4px;background:#28a745;color:#fff;cursor:pointer}
.btn-danger{background:#dc3545}
.small{font-size:0.9em;color:#666}
</style>
</head>
<body>
<header>
    <strong>Panel de administración</strong>
    <span style="float:right">Usuario: <?= esc($_SESSION["usuario_nombre"]) ?> (<?= esc($_SESSION["usuario_rol"]) ?>)</span>
</header>
<div class="container">
    <div class="nav">
        <a href="admin.php?tab=bibliotecarios" class="btn">Bibliotecarios</a>
        <a href="admin.php?tab=socios" class="btn">Socios</a>
        <a href="admin.php?tab=libros" class="btn">Libros</a>
        <a href="admin.php?tab=prestamos" class="btn">Préstamos</a>
        <a href="admin.php?tab=estadisticas" class="btn">Estadísticas</a>
        <a href="admin.php" class="btn">Inicio</a>
    </div>

    <?php if ($tab === 'bibliotecarios'): ?>
    <div class="card">
        <h3>Gestión de bibliotecarios (usuarios del sistema)</h3>
        <form method="POST" style="margin-bottom:10px">
            <input type="hidden" name="entity" value="bibliotecario">
            <div class="form-row">
                <input name="usuario" placeholder="usuario" required>
                <input name="password" placeholder="password (si es nuevo)" >
                <input name="nombre" placeholder="Nombre completo" required>
                <input name="email" placeholder="Email" required>
                <select name="rol">
                    <option value="bibliotecario">bibliotecario</option>
                    <option value="admin">admin</option>
                </select>
                <button class="btn" name="create">Crear</button>
            </div>
        </form>

        <table>
            <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php while($b = $all_biblios->fetch_assoc()): ?>
                <tr>
                    <td><?= esc(f($b, ['ID_User','ID_USER','ID'])) ?></td>
                    <td><?= esc(f($b, ['usuario','user'])) ?></td>
                    <td><?= esc(f($b, ['nombre'])) ?></td>
                    <td><?= esc(f($b, ['email'])) ?></td>
                    <td><?= esc(f($b, ['rol'])) ?></td>
                    <td>
                        <!-- EDIT FORM -->
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="entity" value="bibliotecario">
                            <input type="hidden" name="ID_User" value="<?= esc(f($b, ['ID_User','ID'])) ?>">
                            <input name="usuario" value="<?= esc(f($b, ['usuario'])) ?>">
                            <input name="nombre" value="<?= esc(f($b, ['nombre'])) ?>">
                            <input name="email" value="<?= esc(f($b, ['email'])) ?>">
                            <select name="rol">
                                <option value="bibliotecario" <?= (f($b,['rol'])=='bibliotecario')?'selected':'' ?>>bibliotecario</option>
                                <option value="admin" <?= (f($b,['rol'])=='admin')?'selected':'' ?>>admin</option>
                            </select>
                            <input name="password" placeholder="Nueva password (opcional)">
                            <button class="btn" name="update">Guardar</button>
                        </form>

                        <!-- DELETE -->
                        <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar bibliotecario?')">
                            <input type="hidden" name="entity" value="bibliotecario">
                            <input type="hidden" name="ID_User" value="<?= esc(f($b, ['ID_User','ID'])) ?>">
                            <button class="btn btn-danger" name="delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'socios'): ?>
    <div class="card">
        <h3>Gestión de socios (usuarios)</h3>

        <form method="POST" style="margin-bottom:10px">
            <input type="hidden" name="entity" value="socio">
            <div class="form-row">
                <input name="nombre" placeholder="Nombre completo" required>
                <input name="email" placeholder="Email">
                <input name="telefono" placeholder="Teléfono">
                <input name="direccion" placeholder="Dirección">
                <input name="dni" placeholder="DNI" required>
                <select name="estado">
                    <option value="activo">activo</option>
                    <option value="suspendido">suspendido</option>
                </select>
                <button class="btn" name="create">Crear socio</button>
            </div>
        </form>

        <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>DNI</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php while($s = $all_socios->fetch_assoc()): ?>
                <tr>
                    <td><?= esc(f($s, ['ID_Usuario','ID'])) ?></td>
                    <td><?= esc(f($s, ['nombre_completo','nombre'])) ?></td>
                    <td><?= esc(f($s, ['email'])) ?></td>
                    <td><?= esc(f($s, ['dni'])) ?></td>
                    <td><?= esc(f($s, ['estado'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="entity" value="socio">
                            <input type="hidden" name="ID_Usuario" value="<?= esc(f($s, ['ID_Usuario','ID'])) ?>">
                            <input name="nombre" value="<?= esc(f($s, ['nombre_completo'])) ?>">
                            <input name="email" value="<?= esc(f($s, ['email'])) ?>">
                            <input name="telefono" value="<?= esc(f($s, ['telefono'])) ?>">
                            <input name="direccion" value="<?= esc(f($s, ['direccion'])) ?>">
                            <input name="dni" value="<?= esc(f($s, ['dni'])) ?>">
                            <select name="estado">
                                <option value="activo" <?= (f($s,['estado'])=='activo')?'selected':'' ?>>activo</option>
                                <option value="suspendido" <?= (f($s,['estado'])=='suspendido')?'selected':'' ?>>suspendido</option>
                            </select>
                            <button class="btn" name="update">Guardar</button>
                        </form>

                        <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar socio?')">
                            <input type="hidden" name="entity" value="socio">
                            <input type="hidden" name="ID_Usuario" value="<?= esc(f($s, ['ID_Usuario','ID'])) ?>">
                            <button class="btn btn-danger" name="delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'libros'): ?>
    <div class="card">
        <h3>Gestión de libros</h3>

        <form method="POST" style="margin-bottom:10px">
            <input type="hidden" name="entity" value="libro">
            <div class="form-row">
                <input name="titulo" placeholder="Título" required>
                <input name="autor" placeholder="Autor">
                <input name="editorial" placeholder="Editorial">
                <input name="anio" placeholder="Año (YYYY)">
                <input name="categoria" placeholder="Categoría">
                <input name="isbn" placeholder="ISBN">
                <button class="btn" name="create">Crear libro</button>
            </div>
            <div>
                <textarea name="descripcion" placeholder="Descripción" style="width:100%;height:60px"></textarea>
            </div>
        </form>

        <table>
            <thead><tr><th>ID</th><th>Título</th><th>Autor</th><th>Año</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php while($b = $all_libros->fetch_assoc()): ?>
                <tr>
                    <td><?= esc(f($b, ['ID','id'])) ?></td>
                    <td><?= esc(f($b, ['titulo'])) ?></td>
                    <td><?= esc(f($b, ['autor'])) ?></td>
                    <td><?= esc(f($b, ['anio','año'])) ?></td>
                    <td><?= esc(f($b, ['estado'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="entity" value="libro">
                            <input type="hidden" name="ID" value="<?= esc(f($b, ['ID','id'])) ?>">
                            <input name="titulo" value="<?= esc(f($b, ['titulo'])) ?>">
                            <input name="autor" value="<?= esc(f($b, ['autor'])) ?>">
                            <input name="anio" value="<?= esc(f($b, ['anio','año'])) ?>">
                            <input name="categoria" value="<?= esc(f($b, ['categoria'])) ?>">
                            <input name="isbn" value="<?= esc(f($b, ['isbn'])) ?>">
                            <select name="estado">
                                <option value="Disponible" <?= (f($b,['estado'])=='Disponible')?'selected':'' ?>>Disponible</option>
                                <option value="Prestado" <?= (f($b,['estado'])=='Prestado')?'selected':'' ?>>Prestado</option>
                            </select>
                            <button class="btn" name="update">Guardar</button>
                        </form>

                        <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar libro?')">
                            <input type="hidden" name="entity" value="libro">
                            <input type="hidden" name="ID" value="<?= esc(f($b, ['ID','id'])) ?>">
                            <button class="btn btn-danger" name="delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'prestamos'): ?>
    <div class="card">
        <h3>Préstamos</h3>
        <div class="small">
            <a href="admin.php?tab=prestamos&filter=activo">Activos</a> |
            <a href="admin.php?tab=prestamos&filter=vencido">Vencidos</a> |
            <a href="admin.php?tab=prestamos&filter=devuelto">Devueltos</a> |
            <a href="admin.php?tab=prestamos&filter=todos">Todos</a>
        </div>
        <table>
            <thead><tr><th>ID</th><th>Libro</th><th>Usuario</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php while($pr = $prestamos_q->fetch_assoc()): ?>
                <tr>
                    <td><?= esc(f($pr, ['ID_Prestamo','ID'])) ?></td>
                    <td><?= esc(f($pr, ['titulo'])) ?></td>
                    <td><?= esc(f($pr, ['nombre_completo'])) ?></td>
                    <td><?= esc(f($pr, ['fecha_prestamo'])) ?></td>
                    <td><?= esc(f($pr, ['fecha_devolucion'])) ?></td>
                    <td><?= esc(f($pr, ['estado_prestamo'])) ?></td>
                    <td>
                        <?php if (f($pr,['estado_prestamo']) !== 'Devuelto'): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Marcar devolución?')">
                            <input type="hidden" name="entity" value="devolucion">
                            <input type="hidden" name="ID_Prestamo" value="<?= esc(f($pr, ['ID_Prestamo','ID'])) ?>">
                            <button class="btn" name="devolver">Marcar devuelto</button>
                        </form>
                        <?php else: ?>
                            <span class="small">Devuelto</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'estadisticas'): ?>
    <div class="card">
        <h3>Estadísticas</h3>

        <h4>Top 10 libros más prestados</h4>
        <table>
            <thead><tr><th>Título</th><th>Veces prestado</th></tr></thead>
            <tbody>
            <?php while($row = $stats_top_books->fetch_assoc()): ?>
                <tr><td><?= esc($row['titulo']) ?></td><td><?= esc($row['veces']) ?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <h4>Top 10 usuarios con más préstamos</h4>
        <table>
            <thead><tr><th>Usuario</th><th>Veces</th></tr></thead>
            <tbody>
            <?php while($row = $stats_top_users->fetch_assoc()): ?>
                <tr><td><?= esc($row['nombre_completo']) ?></td><td><?= esc($row['veces']) ?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <h4>Préstamos por mes (últimos 12)</h4>
        <table>
            <thead><tr><th>Mes (YYYY-MM)</th><th>Total</th></tr></thead>
            <tbody>
            <?php while($row = $stats_monthly->fetch_assoc()): ?>
                <tr><td><?= esc($row['ym']) ?></td><td><?= esc($row['total']) ?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="card">
        <h3>Inicio</h3>
        <p>Bienvenido al panel de administración. Use los botones de arriba para gestionar Bibliotecarios, Socios, Libros, Préstamos y ver Estadísticas.</p>
    </div>
    <?php endif; ?>

</div>
</body>
</html>