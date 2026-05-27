<?php
session_start();

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) { putenv("{$key}={$value}"); }
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

if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = []; }



$productos = [];
$error_message = '';
$resultado = $conn->query("SELECT id, nombre, precio, categoria, imagen, descripcion FROM productos ORDER BY categoria, nombre");
if (!$resultado) {
    $error_message = 'Error cargando productos: ' . htmlspecialchars($conn->error);
} else {
    while ($row = $resultado->fetch_assoc()) {
        $imagen = $row['imagen'] ? $row['imagen'] : "img/placeholder.webp";
        $productos[] = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'precio' => (float)$row['precio'],
            'categoria' => $row['categoria'],
            'imagen' => $imagen,
            'descripcion' => $row['descripcion']
        ];
    }
    $resultado->free();
}

$mensaje_alerta = '';
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $prod_id = (int)$_POST['producto_id'];
    // Buscar el producto en el array de productos
    $producto_a_agregar = null;
    foreach ($productos as $p) {
        if ($p['id'] === $prod_id) {
            $producto_a_agregar = $p;
            break;
        }
    }
    if ($producto_a_agregar) {
        if (!isset($_SESSION['carrito'][$prod_id])) {
            $_SESSION['carrito'][$prod_id] = [
                'nombre' => $producto_a_agregar['nombre'],
                'precio' => $producto_a_agregar['precio'],
                'cantidad' => 1
            ];
        } else {
            $_SESSION['carrito'][$prod_id]['cantidad']++;
        }
        $_SESSION['mensaje_carrito'] = 'Producto añadido al carrito.';
    }
    header('Location: tienda.php');
    exit;
}
if (isset($_SESSION['mensaje_carrito'])) {
    $mensaje_alerta = $_SESSION['mensaje_carrito'];
    unset($_SESSION['mensaje_carrito']);
}

$busqueda = isset($_GET['busqueda']) ? strtolower(trim($_GET['busqueda'])) : '';
$categoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$precioMin = isset($_GET['precio_min']) && is_numeric($_GET['precio_min']) ? (float)$_GET['precio_min'] : 0;
$precioMax = isset($_GET['precio_max']) && is_numeric($_GET['precio_max']) ? (float)$_GET['precio_max'] : 999999;
$precioMinValue = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? $_GET['precio_min'] : '';
$precioMaxValue = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? $_GET['precio_max'] : '';
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" />
<title>Pasca y Pesca | Tienda</title>
<style>
/* ===== SHOP HERO ===== */
.shop-hero {
    background: linear-gradient(135deg, rgba(7,45,73,.94) 0%, rgba(10,61,98,.88) 60%, rgba(21,101,192,.82) 100%);
    padding: 3.5rem 0 2.5rem;
    color: #fff;
    text-align: center;
}
.shop-hero h1 { font-family:'Montserrat',sans-serif; font-size:2.2rem; font-weight:800; margin-bottom:.5rem; }
.shop-hero p { opacity:.85; font-size:1rem; }

/* ===== FILTROS ===== */
.shop-controls {
    background: #fff;
    border-bottom: 1px solid var(--border);
    padding: 1.2rem 0;
    position: sticky;
    top: 60px;
    z-index: 100;
    box-shadow: var(--shadow-sm);
}
.controls-inner { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between; }
.search-wrap { display:flex; flex:1; max-width:380px; }
.search-wrap input[type=text] {
    flex:1; padding:.6rem 1rem;
    border:1.5px solid var(--border);
    border-right:none;
    border-radius: 8px 0 0 8px;
    font-size:.9rem; outline:none; background:#fafbfc;
}
.search-wrap input[type=text]:focus { border-color:var(--accent); }
.search-wrap button {
    padding:.6rem 1rem;
    background:var(--accent); color:#fff;
    border:none; border-radius:0 8px 8px 0;
    cursor:pointer; font-size:.9rem; transition:.2s;
}
.search-wrap button:hover { background:var(--accent-dark); }

.cat-pills { display:flex; gap:.5rem; flex-wrap:wrap; }
.cat-pill {
    padding:.4rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: 99px;
    background:#fff;
    color:var(--muted);
    font-size:.82rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
    text-decoration: none;
    white-space: nowrap;
}
.cat-pill:hover, .cat-pill.active {
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}
.price-wrap { display:flex; align-items:center; gap:.5rem; }
.price-wrap input[type=number] {
    width:90px; padding:.5rem .7rem;
    border:1.5px solid var(--border);
    border-radius:8px; font-size:.85rem;
    outline:none;
}
.price-wrap input[type=number]:focus { border-color:var(--accent); }
.price-wrap button {
    padding:.5rem 1rem;
    background:var(--primary); color:#fff;
    border:none; border-radius:8px;
    font-weight:700; cursor:pointer; font-size:.85rem; transition:.2s;
}
.price-wrap button:hover { background:var(--accent); }

/* ===== MAIN SHOP ===== */
.shop-main { padding: 2rem 0 4rem; flex:1; }
.results-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:.5rem; }
.results-count { font-size:.88rem; color:var(--muted); }
.results-count strong { color:var(--primary); }

/* ===== PRODUCT GRID ===== */
.product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.8rem; }
.product-item {
    background:#fff;
    border:1px solid var(--border);
    border-radius:16px;
    overflow:hidden;
    transition:transform .25s,box-shadow .25s;
    display:flex; flex-direction:column;
    position:relative;
}
.product-item:hover { transform:translateY(-5px); box-shadow:0 12px 36px rgba(10,61,98,.13); }
.product-cat-badge {
    position:absolute; top:10px; left:10px;
    background: rgba(10,61,98,.82);
    color:#fff;
    font-size:.68rem; font-weight:700;
    letter-spacing:.06em; text-transform:uppercase;
    padding:.22rem .65rem; border-radius:99px;
    z-index:1;
}
.product-img-wrap {
    background: linear-gradient(135deg, #e3f0fd 0%, #faf8f4 100%);
    box-shadow: 0 6px 24px rgba(30,136,229,.10);
    border-radius: 18px;
    overflow: hidden;
    height: 250px;
    display: flex; align-items: center; justify-content: center;
    transition: background .3s, box-shadow .3s;
    position: relative;
}

.product-img-wrap img {
    max-height: 210px;
    max-width: 90%;
    object-fit: contain;
    padding: 1.2rem;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(30,136,229,.08);
    background: rgba(255,255,255,0.7);
    transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .3s;
}

.product-item:hover .product-img-wrap img {
    transform: scale(1.08) rotate(-1.5deg);
    box-shadow: 0 8px 32px rgba(30,136,229,.18);
}
.product-img-wrap img { max-height:220px; max-width:100%; object-fit:contain; padding:.8rem; transition:transform .3s; }
.product-item:hover .product-img-wrap img { transform:scale(1.07); }
.product-item-body { padding:1.4rem; flex:1; display:flex; flex-direction:column; }
.product-name { font-weight:700; font-size:1.05rem; color:var(--dark); margin-bottom:.5rem; line-height:1.35; flex:1; }
.product-price { font-size:1.35rem; font-weight:800; color:var(--accent); margin-bottom:1.1rem; }
.add-to-cart {
    width:100%; padding:.65rem;
    background:var(--primary); color:#fff;
    border:none; border-radius:10px;
    font-weight:700; cursor:pointer;
    font-size:.88rem;
    display:flex; align-items:center; justify-content:center; gap:.45rem;
    transition:.2s;
    margin-top:auto;
}
.add-to-cart:hover { background:var(--accent); }

/* ===== EMPTY STATE ===== */
.empty-state { text-align:center; padding:5rem 1rem; }
.empty-state i { font-size:3.5rem; color:var(--border); margin-bottom:1rem; display:block; }
.empty-state p { color:var(--muted); font-size:1rem; }

/* ===== SHOW MORE ===== */
.show-more-wrap { text-align:center; margin-top:2.5rem; }
#show-more-btn {
    padding:.75rem 2.5rem;
    background:#fff;
    border:2px solid var(--primary);
    color:var(--primary);
    border-radius:99px;
    font-weight:700;
    font-size:.95rem;
    cursor:pointer;
    transition:.2s;
}
#show-more-btn:hover { background:var(--primary); color:#fff; }

/* ===== TOAST ===== */
.toast {
    position:fixed; bottom:2rem; right:2rem;
    background:var(--primary); color:#fff;
    padding:1rem 1.5rem;
    border-radius:12px;
    box-shadow:var(--shadow-lg);
    font-weight:600; font-size:.95rem;
    z-index:9999;
    display:flex; align-items:center; gap:.6rem;
    animation:slideIn .3s ease;
}
@keyframes slideIn { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
</style>
</head>
<body>

<header>
<div class="container">
<nav>
    <a href="home.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
    <ul class="nav-links">
        <li><a href="home.php">Inicio</a></li>
        <li><a href="tienda.php" class="active">Tienda</a></li>
        <li><a href="foro.php">Foro</a></li>
        <li><a href="tecnicas.php">Técnicas</a></li>
        <li><a href="zonasCalientes.php">Zonas</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="carrito.php"><i class="fa-solid fa-cart-shopping"></i> Carrito</a></li>
        <li><a class="profile-link" href="editarPerfil.php" title="Mi perfil"><i class="fa-solid fa-user"></i></a></li>
        <?php else: ?>
        <li><a href="login.php" class="btn btn-primary" style="padding:.42rem 1rem;font-size:.82rem;">Entrar</a></li>
        <?php endif; ?>
    </ul>
</nav>
</div>
</header>

<!-- SHOP HERO -->
<div class="shop-hero">
    <div class="container">
        <h1><i class="fa-solid fa-store"></i> Nuestra Tienda</h1>
        <p>Productos de las mejores marcas de la pesca deportiva</p>
    </div>
</div>

<!-- CONTROLES STICKY -->
<div class="shop-controls">
    <div class="container">
        <div class="controls-inner">
            <!-- Búsqueda -->
            <form method="get" style="display:contents">
                <div class="search-wrap">
                    <input type="text" name="busqueda" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>

            <!-- Categorías pill -->
            <div class="cat-pills">
                <?php
                $allParams = http_build_query(array_filter(['busqueda'=>$busqueda,'precio_min'=>$precioMinValue,'precio_max'=>$precioMaxValue]));
                $activeAll = ($categoriaSeleccionada==='' || $categoriaSeleccionada==='Todos');
                ?>
                <a href="tienda.php?<?php echo $allParams; ?>" class="cat-pill <?php echo $activeAll ? 'active' : ''; ?>">Todos</a>
                <?php
                $categorias = array_unique(array_column($productos,'categoria'));
                foreach($categorias as $cat):
                    $p = http_build_query(array_filter(['busqueda'=>$busqueda,'precio_min'=>$precioMinValue,'precio_max'=>$precioMaxValue,'categoria'=>$cat]));
                    $isActive = ($categoriaSeleccionada===$cat);
                ?>
                <a href="tienda.php?<?php echo $p; ?>" class="cat-pill <?php echo $isActive ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat); ?></a>
                <?php endforeach; ?>
            </div>

            <!-- Precio -->
            <form method="get" style="display:contents">
                <div class="price-wrap">
                    <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                    <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoriaSeleccionada); ?>">
                    <input type="number" name="precio_min" placeholder="€ min" value="<?php echo htmlspecialchars($precioMinValue); ?>" min="0" step="0.01">
                    <span style="color:var(--muted)">—</span>
                    <input type="number" name="precio_max" placeholder="€ max" value="<?php echo htmlspecialchars($precioMaxValue); ?>" min="0" step="0.01">
                    <button type="submit"><i class="fa-solid fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PRODUCTOS -->
<main class="shop-main">
<div class="container">

<?php if ($error_message): ?>
<div style="background:#ffe6e6;color:#900;border:1px solid #f5c2c2;padding:16px;border-radius:12px;margin:0 0 20px;">
    <strong>Error:</strong> <?php echo $error_message; ?>
</div>
<?php endif; ?>

<?php
$productosFiltrados = [];
foreach($productos as $prod){
    $nombreLower = strtolower($prod['nombre']);
    $catMatch = ($categoriaSeleccionada=='' || $categoriaSeleccionada=='Todos' || $prod['categoria']==$categoriaSeleccionada);
    $busqMatch = ($busqueda=='' || strpos($nombreLower,$busqueda)!==false);
    $precioMatch = ($prod['precio'] >= $precioMin && $prod['precio'] <= $precioMax);
    if($catMatch && $busqMatch && $precioMatch) $productosFiltrados[] = $prod;
}
$total = count($productosFiltrados);
?>

<div class="results-bar">
    <div class="results-count">
        Mostrando <strong><?php echo $total; ?></strong> producto<?php echo $total!==1?'s':''; ?>
        <?php if($categoriaSeleccionada && $categoriaSeleccionada!=='Todos'): ?>
        en <strong><?php echo htmlspecialchars($categoriaSeleccionada); ?></strong>
        <?php endif; ?>
        <?php if($busqueda): ?>
        para <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
        <?php endif; ?>
    </div>
    <?php if($busqueda || ($categoriaSeleccionada && $categoriaSeleccionada!=='Todos') || $precioMinValue || $precioMaxValue): ?>
    <a href="tienda.php" style="font-size:.82rem;color:var(--accent);font-weight:600;"><i class="fa-solid fa-xmark"></i> Limpiar filtros</a>
    <?php endif; ?>
</div>

<?php if($total === 0): ?>
<div class="empty-state">
    <i class="fa-solid fa-fish-fins"></i>
    <p>No se encontraron productos con esos filtros.<br><a href="tienda.php" style="color:var(--accent);font-weight:700;">Ver todos los productos</a></p>
</div>
<?php else: ?>
<div class="product-grid" id="product-grid">
<?php $visibleCount=0; foreach($productosFiltrados as $prod): $visibleCount++;
    $hiddenStyle = $visibleCount > 9 ? 'style="display:none"' : '';
?>
<div class="product-item" <?php echo $hiddenStyle; ?>>
    <span class="product-cat-badge"><?php echo htmlspecialchars($prod['categoria']); ?></span>
    <div class="product-img-wrap">
        <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" loading="lazy">
    </div>
    <div class="product-item-body">
        <p class="product-name"><?php echo htmlspecialchars($prod['nombre']); ?></p>
        <p class="product-price">€<?php echo number_format($prod['precio'],2); ?></p>
        <form method="post" onsubmit="return validarCarritoTienda(event)">
            <input type="hidden" name="producto_id" value="<?php echo $prod['id']; ?>">
            <button type="submit" name="add_to_cart" class="add-to-cart">
                <i class="fa-solid fa-cart-plus"></i> Añadir al carrito
            </button>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php if($total > 9): ?>
<div class="show-more-wrap">
    <button id="show-more-btn"><i class="fa-solid fa-chevron-down"></i> Mostrar más productos</button>
</div>
<?php endif; ?>
<?php endif; ?>

</div>
</main>

<footer>
  <div class="container">
    <div class="footer-content">
      <div class="footer-column">
        <h3><i class="fa-solid fa-fish"></i> Pasca y Pesca</h3>
        <p>Tu tienda de pesca deportiva de confianza.</p>
      </div>
      <div class="footer-column">
        <h3>Navegación</h3>
        <a href="home.php">Inicio</a>
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

<?php if ($mensaje_alerta): ?>
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($mensaje_alerta) ?></div>
<script>setTimeout(()=>{const t=document.getElementById('toast');if(t)t.remove();},3500);</script>
<?php endif; ?>

<script>
const usuarioLogueado = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
function validarCarritoTienda(event) {
    if (!usuarioLogueado) {
        event.preventDefault();
        window.location.href = 'login.php';
        return false;
    }
    return true;
}
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('show-more-btn');
    if (btn) {
        btn.addEventListener('click', function() {
            const items = document.querySelectorAll('.product-item');
            let shown = 0;
            for (let i = 0; i < items.length && shown < 6; i++) {
                if (items[i].style.display === 'none') {
                    items[i].style.display = 'flex';
                    shown++;
                }
            }
            if (!Array.from(items).some(it => it.style.display === 'none')) {
                btn.parentElement.style.display = 'none';
            }
        });
    }
});
</script>
<script src="global.js"></script>
</body>
</html>
