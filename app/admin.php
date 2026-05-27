<?php
session_start();

// Verificar si es administrador
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        putenv("{$key}={$value}");
    }
}
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'usuarioapp';
$db_pass = getenv('DB_PASSWORD') ?: '1234';
$db_name = getenv('DB_NAME') ?: 'pescaypesca';
$db_port = getenv('DB_PORT') ?: 3306;
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$message = '';
$messageType = '';

// Procesar la eliminación de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $userId = intval($_POST['user_id']);
    
    if ($userId > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $message = "Usuario eliminado correctamente.";
            $messageType = "success";
        } else {
            $message = "Error al eliminar el usuario.";
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Procesar actualización de precio de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_price') {
        $productoId = intval($_POST['producto_id']);
        $nuevoPrecio = floatval($_POST['nuevo_precio']);
        if ($productoId > 0 && $nuevoPrecio > 0) {
            $stmt = $conn->prepare("UPDATE productos SET precio = ? WHERE id = ?");
            $stmt->bind_param("di", $nuevoPrecio, $productoId);
            if ($stmt->execute()) {
                $message = "Precio actualizado correctamente.";
                $messageType = "success";
            } else {
                $message = "Error al actualizar el precio.";
                $messageType = "error";
            }
            $stmt->close();
        }
    }
    // Eliminar producto
    if ($_POST['action'] === 'delete_product') {
        $productoId = intval($_POST['producto_id']);
        if ($productoId > 0) {
            $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->bind_param("i", $productoId);
            if ($stmt->execute()) {
                $message = "Producto eliminado correctamente.";
                $messageType = "success";
            } else {
                $message = "Error al eliminar el producto.";
                $messageType = "error";
            }
            $stmt->close();
        }
    }
    // Añadir producto
    if ($_POST['action'] === 'add_product') {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $categoria = trim($_POST['categoria']);
        $stock = intval($_POST['stock']);
        $imagen = trim($_POST['imagen']);
        if ($nombre && $precio > 0 && $categoria && $stock >= 0 && $imagen) {
            $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, categoria, stock, imagen) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsss", $nombre, $precio, $categoria, $stock, $imagen);
            if ($stmt->execute()) {
                $message = "Producto añadido correctamente.";
                $messageType = "success";
            } else {
                $message = "Error al añadir el producto.";
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "Todos los campos son obligatorios.";
            $messageType = "error";
        }
    }
}

// Obtener todos los usuarios
$result = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");

// Obtener todos los productos
$productsResult = $conn->query("SELECT id, nombre, precio, categoria, stock FROM productos ORDER BY categoria, nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" />
    <!-- DataTables eliminado, solo estilos propios -->
    <title>Panel de Administración | Pasca y Pesca</title>
<style>
    :root {
        --primary-blue: #0a3d62;
        --accent-blue: #3c6382;
        --sand: #f8f9fa;
        --dark: #2f3542;
        --white: #ffffff;

    :root {
        --primary-blue: #0a3d62;
        --accent-blue: #3c6382;
        --sand: #f8f9fa;
        --dark: #2f3542;
        --white: #ffffff;
        --orange: #f39c12;
        --red: #e74c3c;
        --green: #27ae60;
    }

    /* HEADER */

    .main-content {
        flex: 1;
        padding: 30px 0;
    }

    /* ADMIN PANEL */
    .admin-panel {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 30px;
    }

    .admin-panel h1 {
        color: var(--primary-blue);
        margin-bottom: 10px;
    }

    .admin-info {
        color: var(--accent-blue);
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .message {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: bold;
    }

    .message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* TABLE */

    .users-table-container {
        overflow-x: auto;
        margin-bottom: 2.5rem;
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 18px;
        font-size: 1rem;
        background: var(--white);
        box-shadow: 0 2px 16px rgba(10,61,98,.07);
        border-radius: 12px;
        overflow: hidden;
    }
    thead th {
        background: linear-gradient(90deg, var(--primary-blue) 70%, var(--accent-blue) 100%);
        color: #fff;
        padding: 16px 18px;
        font-weight: 700;
        border-bottom: 3px solid var(--accent-blue);
        text-align: left;
        letter-spacing: .03em;
        font-size: 1.07em;
    }
    tbody td {
        padding: 14px 18px;
        border-bottom: 1.5px solid #e8edf3;
        color: var(--dark);
        vertical-align: middle;
        background: #fff;
        transition: background .2s;
    }
    tbody tr:last-child td {
        border-bottom: none;
    }
    tbody tr:hover td {
        background: #f3f8ff;
    }
    tbody tr:nth-child(even) td {
        background: #f8fbff;
    }
    tbody tr:nth-child(even):hover td {
        background: #eaf2fb;
    }
    td form {
        margin: 0;
    }
    td button, td .btn {
        margin: 0;
    }
    @media (max-width: 700px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }
        thead {
            display: none;
        }
        tr {
            margin-bottom: 1.2rem;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
            background: var(--white);
        }
        td {
            padding: 12px 15px;
            border: none;
            position: relative;
            text-align: left;
        }
        td:before {
            content: attr(data-label);
            font-weight: bold;
            color: var(--primary-blue);
            display: block;
            margin-bottom: 4px;
            font-size: .93em;
        }
    }

    div.dt-container {
        font-family: 'Inter', sans-serif;
    }

    div.dt-container .dt-search input {
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: .45rem .9rem;
        font-size: .88rem;
        outline: none;
        transition: border-color .2s;
    }

    div.dt-container .dt-search input:focus {
        border-color: #1e88e5;
    }

    div.dt-container select {
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: .35rem .6rem;
        font-size: .88rem;
    }

    div.dt-container .dt-paging .dt-paging-button {
        border: 1.5px solid var(--border);
        border-radius: 6px;
        color: var(--primary-blue) !important;
        padding: 4px 10px;
        margin: 2px;
        background: #fff;
        cursor: pointer;
        transition: .2s;
    }

    div.dt-container .dt-paging .dt-paging-button:hover {
        background: var(--primary-blue) !important;
        color: #fff !important;
        border-color: var(--primary-blue);
    }

    div.dt-container .dt-paging .dt-paging-button.current {
        background: var(--primary-blue) !important;
        color: #fff !important;
        border-color: var(--primary-blue);
    }

    div.dt-container .dt-info {
        font-size: .82rem;
        color: var(--muted);
    }

    .btn-deletes {
        background: red;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .btn-deletes:hover {
        background: #c0392b;
    }

    .btn-logout {
        background: var(--accent-blue);
        color: var(--white);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        margin-top: 20px;
        transition: background 0.3s ease;
    }

    .btn-logout:hover {
        background: var(--primary-blue);
    }

    .admin-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #ddd;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
        color: var(--white);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-box h3 {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .stat-box p {
        font-size: 0.9rem;
    }

    /* TABS */
    .admin-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #ddd;
    }


    .admin-tab-btn, button.admin-tab-btn {
        padding: 12px 20px;
        background: #e9f0fa !important;
        border: 1.5px solid #dde2ea !important;
        cursor: pointer;
        font-weight: bold;
        color: #0a3d62 !important;
        border-bottom: 3px solid transparent !important;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        box-shadow: 0 1px 4px rgba(10,61,98,.04);
        outline: none !important;
    }

    .admin-tab-btn.active, button.admin-tab-btn.active {
        color: #fff !important;
        background: #3c6382 !important;
        border-bottom-color: #f39c12 !important;
        box-shadow: 0 2px 8px rgba(30,136,229,.10);
    }

    .admin-tab-btn:hover, button.admin-tab-btn:hover {
        background: #3c6382 !important;
        color: #fff !important;
    }

    .admin-tab-content {
        display: none;
    }

    .admin-tab-content.active {
        display: block;
    }

    .price-input {
        width: 120px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .btn-update-price {
        background: var(--orange);
        color: var(--white);
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .btn-update-price:hover {
        background: #e67e22;
    }

    }

</style>
</head>
<body>
<header>
  <div class="container">
    <nav>
    <a href="index.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
      <ul class="nav-links">
        <li><a href="admin.php" class="active">Panel Admin</a></li>
        <li><a href="logout.php" class="btn btn-outline" style="padding:.42rem 1rem;font-size:.82rem;">Cerrar sesión</a></li>
      </ul>
    </nav>
  </div>
</header>


<div class="main-content">
        <div class="container">
                <div class="admin-panel">
                        <h1>Panel de Administración</h1>
                        <div class="admin-info">
                                Bienvenido, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
                        </div>

                        <?php if (!empty($message)): ?>
                                <div class="message <?= $messageType ?>">
                                        <?= htmlspecialchars($message) ?>
                                </div>
                        <?php endif; ?>

                        <!-- ESTADÍSTICAS -->
                        <div class="stats">
                                <div class="stat-box">
                                        <h3><?= $result->num_rows ?></h3>
                                        <p>Usuarios Registrados</p>
                                </div>
                                <div class="stat-box">
                                        <h3><?= $productsResult->num_rows ?></h3>
                                        <p>Productos en Tienda</p>
                                </div>
                        </div>

                        <!-- PESTAÑAS DE ADMINISTRACIÓN -->
                        <div class="admin-tabs">
                                <button class="admin-tab-btn active" onclick="switchAdminTab('usuarios', event)">Gestionar Usuarios</button>
                                <button class="admin-tab-btn" onclick="switchAdminTab('productos', event)">Gestionar Productos</button>
                                <button type="button" class="admin-tab-btn" onclick="window.location.href='foro.php'">Gestionar Foro</button>
                        </div>


                        <!-- TAB: USUARIOS -->
                        <div class="admin-tab-content active" id="usuarios-tab">
                            <h2 style="color: var(--primary-blue); margin-top: 30px; margin-bottom: 15px;">Usuarios Registrados</h2>
                            <?php if ($result->num_rows > 0): ?>
                                <div class="users-table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Fecha de Registro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at']))) ?></td>
                                                    <td>
                                                        <form method="post" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn-deletes">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-users">
                                    <p>No hay usuarios registrados aún.</p>
                                </div>
                            <?php endif; ?>
                        </div>


                        <!-- TAB: PRODUCTOS -->
                        <div class="admin-tab-content" id="productos-tab">
                            <h2 style="color: var(--primary-blue); margin-top: 30px; margin-bottom: 15px;">Gestionar Productos y Precios</h2>

                            <!-- Formulario para añadir producto -->
                            <form method="post" class="add-product-form" style="background:#f8f9fa;padding:18px 22px;border-radius:10px;margin-bottom:28px;box-shadow:0 2px 8px rgba(30,136,229,.07);display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;align-items:end;">
                                <input type="hidden" name="action" value="add_product">
                                <div>
                                    <label for="nombre" style="font-weight:600;">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" required style="width:100%;padding:.6rem .9rem;border-radius:7px;border:1.5px solid #dde2ea;">
                                </div>
                                <div>
                                    <label for="precio" style="font-weight:600;">Precio (€)</label>
                                    <input type="number" name="precio" id="precio" step="0.01" min="0" required style="width:100%;padding:.6rem .9rem;border-radius:7px;border:1.5px solid #dde2ea;">
                                </div>
                                <div>
                                    <label for="categoria" style="font-weight:600;">Categoría</label>
                                    <input type="text" name="categoria" id="categoria" required style="width:100%;padding:.6rem .9rem;border-radius:7px;border:1.5px solid #dde2ea;">
                                </div>
                                <div>
                                    <label for="stock" style="font-weight:600;">Stock</label>
                                    <input type="number" name="stock" id="stock" min="0" required style="width:100%;padding:.6rem .9rem;border-radius:7px;border:1.5px solid #dde2ea;">
                                </div>
                                <div>
                                    <label for="imagen" style="font-weight:600;">Imagen (ruta)</label>
                                    <input type="text" name="imagen" id="imagen" placeholder="img/archivo.jpg" required style="width:100%;padding:.6rem .9rem;border-radius:7px;border:1.5px solid #dde2ea;">
                                </div>
                                <div style="align-self:center;">
                                    <button type="submit" class="btn btn-primary" style="padding:.7rem 2.2rem;font-size:1rem;">Añadir producto</button>
                                </div>
                            </form>

                            <?php if ($productsResult->num_rows > 0): ?>
                                <div class="users-table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Categoría</th>
                                                <th>Precio Actual</th>
                                                <th>Nuevo Precio</th>
                                                <th>Stock</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($product = $productsResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($product['id']) ?></td>
                                                    <td><?= htmlspecialchars($product['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($product['categoria']) ?></td>
                                                    <td><strong>€<?= number_format($product['precio'], 2, ',', '.') ?></strong></td>
                                                    <td>
                                                        <form method="post" style="display: inline; margin: 0;">
                                                            <input type="hidden" name="action" value="update_price">
                                                            <input type="hidden" name="producto_id" value="<?= $product['id'] ?>">
                                                            <input type="number" name="nuevo_precio" class="price-input" placeholder="0.00" step="0.01" min="0" required>
                                                            <button type="submit" class="btn">Actualizar</button>
                                                        </form>
                                                    </td>
                                                    <td><?= htmlspecialchars($product['stock']) ?> unidades</td>
                                                    <td style="min-width:120px;">
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="action" value="delete_product">
                                                            <input type="hidden" name="producto_id" value="<?= $product['id'] ?>">
                                                            <button type="submit" class="btn-deletes" onclick="return confirm('¿Eliminar producto?');">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-users">
                                    <p>No hay productos en la tienda.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                                        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
                                    </div>
                                </div>
                            </div>

                            <footer>
                              <div class="container">
                                <div class="footer-content">
                                  <div class="footer-column">
                                <h3><i class="fa-solid fa-fish"></i> Pasca y Pesca</h3>
                                <p>Tu tienda de pesca deportiva de confianza.</p>
                                  </div>
                                  <div class="footer-column">
                                <h3>Navegación</h3>
                                <a href="index.php">Inicio</a>
                                <a href="tienda.php">Tienda</a>
                                <a href="tecnicas.php">Técnicas</a>
                                <a href="zonasCalientes.php">Zonas de Pesca</a>
                                <a href="foro.php">Foro</a>
                                  </div>
                                  <div class="footer-column">
                                <h3>Cuenta</h3>
                                <a href="login.php">Iniciar Sesión</a>
                                <a href="registro.php">Registrarse</a>
                                  </div>
                                  <div class="footer-column">
                                <h3>Contacto</h3>
                                <p>info@pascaypesca.es</p>
                                <p>+34 600 123 456</p>
                                      <a href="contacto.php">Formulario de contacto</a>
                                  </div>
                                </div>
                                <div class="footer-bottom"><?= date("Y") ?> Pasca y Pesca. Todos los derechos reservados.</div>
                              </div>
                            </footer>

                            <script>
                            // Tabs funcionalidad simple y robusta
                            function switchAdminTab(tab, event) {
                                event.preventDefault();
                                // Oculta todas las pestañas
                                document.querySelectorAll('.admin-tab-content').forEach(function(tabContent) {
                                    tabContent.classList.remove('active');
                                });
                                // Quita activo a todos los botones
                                document.querySelectorAll('.admin-tab-btn').forEach(function(btn) {
                                    btn.classList.remove('active');
                                });
                                // Activa la pestaña seleccionada
                                var tabDiv = document.getElementById(tab + '-tab');
                                if (tabDiv) tabDiv.classList.add('active');
                                event.target.classList.add('active');
                            }
                            </script>
la, 