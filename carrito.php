<?php
session_start();
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
$usuario_logueado = isset($_SESSION['user_id']);

$imagenes_default = [
    "Cañas" => "img/cañas.webp",
    "Carretes" => "img/carretes.webp",
    "Señuelos" => "img/señuelos.webp",
    "Accesorios" => "img/accesorios.webp"
];

$imagenes_por_producto = [
    "Caña de Pescar Pro X" => "img/cañaProX.webp",
    "Carrete Shimano 5000" => "img/carreteShimano5000.webp",
    "Kit Señuelos Premium" => "img/señuelosPremium.webp",
    "Caña de Pescar Básica" => "img/cañaBásica.webp",
    "Carrete Daiwa 3000" => "img/carreteDaiwa3000.webp",
    "Señuelo Spinner Pro" => "img/señueloSpinnerPro.webp",
    "Caña Telescópica" => "img/cañaTelescópica.webp",
    "Carrete Penn 4000" => "img/carretePenn4000.webp",
    "Kit de Aparejos" => "img/kitAparejos.webp",
    "Caña Spinning" => "img/cañaSpinning.webp",
    "Carrete Abu Garcia" => "img/carreteAbuGarcia.webp",
    "Señuelo Topwater" => "img/señueloTopwater.webp",
    "Caja de Aparejos" => "img/cajaAparejos.webp",
    "Caña de Mosca" => "img/cañaDeMosca.webp",
    "Carrete Fly Fishing" => "img/carreteFlyFishing.webp"
];

$producto_imagenes = [];
if (!empty($carrito)) {
    $ids = array_map('intval', array_keys($carrito));
    if (!empty($ids)) {
        // Cargar variables de entorno desde .env si existe
        if (file_exists(__DIR__ . '/.env')) {
            $env = parse_ini_file(__DIR__ . '/.env');
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
        if (!$conn->connect_error) {
            $conn->set_charset("utf8");
            $in = implode(',', $ids);
            $resultado = $conn->query("SELECT id, nombre, categoria, imagen FROM productos WHERE id IN ($in)");
            if ($resultado) {
                while ($row = $resultado->fetch_assoc()) {
                    $producto_imagenes[(int)$row['id']] = $row['imagen'] ?: ($imagenes_por_producto[$row['nombre']] ?? ($imagenes_default[$row['categoria']] ?? "https://via.placeholder.com/60x60?text=IMG"));
                }
                $resultado->free();
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="styles.css" />
<title>Carrito | Pasca y Pesca</title>
<style>
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #2f80ed; /* azul claro */
    --accent-blue-dark: #1f5bb5;
    --sand: #f5f0e6;
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #2f80ed; /* azul claro */
    --accent-blue-dark: #1f5bb5;
    --sand: #f5f0e6;
    --dark: #2f3542;
    --white: #fff;
}

/* MAIN */
main {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 40px 0;
}

/* CARRITO */
.carrito-container {
    background: var(--white);
    border-radius: 15px;
    width: 600px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px 40px;
}

h1 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 1.9rem;
    margin-bottom: 5px;
    text-align: center;
    color: #000; /* texto negro */
}

p.subtitle {
    margin-top: 0;
    margin-bottom: 25px;
    color: #000; /* texto negro */
    font-weight: 400;
    text-align: center;
}

.item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #ddd;
    padding: 15px 0;
    gap: 15px;
}

.item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    background: #eee;
}

.item-info {
    flex-grow: 1;
    min-width: 200px;
}

.item-info strong {
    display: block;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--dark);
}

.item-info span {
    color: var(--primary-blue);
    font-weight: 700;
    margin-top: 4px;
    display: block;
    font-size: 0.95rem;
}

.item-cantidad {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0f0f0;
    padding: 5px 10px;
    border-radius: 5px;
}

.item-cantidad button {
    background: var(--accent-blue);
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-weight: bold;
    font-size: 1rem;
    transition: background 0.3s;
}

.item-cantidad button:hover {
    background: var(--accent-blue-dark);
}

.item-cantidad button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.item-cantidad span {
    min-width: 30px;
    text-align: center;
    font-weight: bold;
    color: var(--dark);
}

.item-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.item-remove {
    cursor: pointer;
    font-size: 1.2rem;
    color: #d63031;
    border: none;
    background: none;
    padding: 5px 10px;
}

.item-remove:hover {
    color: #b71c1c;
}

.item-remove:hover {
    color: #b71c1c;
}

.total-section {
    display: flex;
    justify-content: space-between;
    font-weight: 900;
    font-size: 1.3rem;
    margin-top: 25px;
    padding-top: 10px;
    border-top: 2px solid var(--accent-blue);
    color: var(--dark);
}

.btn-group {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn {
    flex: 1;
    padding: 12px 0;
    font-weight: 700;
    border-radius: 30px;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    color: var(--white);
    background-color: var(--accent-blue);
    border-color: var(--accent-blue);
}

.btn:hover {
    background-color: var(--accent-blue-dark);
    border-color: var(--accent-blue-dark);
}

.empty-msg {
    text-align: center;
    font-style: italic;
    padding: 50px 0;
    color: #000; /* texto negro */
    font-weight: 500;
}

}
</style>
</head>
<body>
<header>
  <div class="container">
    <nav>
      <a href="home.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
      <ul class="nav-links">
        <li><a href="home.php">Inicio</a></li>
        <li><a href="tienda.php">Tienda</a></li>
        <li><a href="foro.php">Foro</a></li>
        <li><a href="tecnicas.php">Técnicas</a></li>
        <li><a href="zonasCalientes.php">Zonas</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <?php if ($usuario_logueado): ?>
        <li><a href="carrito.php" class="active"><i class="fa-solid fa-cart-shopping"></i> Carrito</a></li>
        <li><a class="profile-link" href="editarPerfil.php" title="Mi perfil"><i class="fa-solid fa-user"></i></a></li>
        <?php else: ?>
        <li><a href="login.php" class="btn btn-primary" style="padding:.42rem 1rem;font-size:.82rem;">Entrar</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main>
<div class="carrito-container">
    <h1>Tu Carrito de Compra</h1>
    <p class="subtitle">Gestiona los artículos de tu próximo equipo</p>

    <?php if(empty($carrito)): ?>
        <p class="empty-msg">Tu carrito está vacío.</p>
    <?php else: ?>
        <?php foreach($carrito as $id => $item): 
            $total += $item['precio'] * $item['cantidad'];
        ?>
        <div class="item">
            <?php
                $imagenProducto = $producto_imagenes[$id] ?? ($imagenes_por_producto[$item['nombre']] ?? "https://via.placeholder.com/60x60?text=IMG");
            ?>
            <img src="<?= htmlspecialchars($imagenProducto); ?>" alt="<?= htmlspecialchars($item['nombre']); ?>" />
            <div class="item-info">
                <strong><?= htmlspecialchars($item['nombre']); ?></strong>
                <span>€<?= number_format($item['precio'], 2, ',', '.'); ?> c/u</span>
                <span style="color: #666; font-size: 0.9rem; margin-top: 2px;">Subtotal: €<?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.'); ?></span>
            </div>
            <div class="item-cantidad">
                <form method="post" action="actualizarCarrito.php" style="display:inline;">
                    <input type="hidden" name="producto_id" value="<?= $id; ?>">
                    <input type="hidden" name="accion" value="disminuir">
                    <button type="submit" <?php if($item['cantidad'] <= 1) echo 'disabled'; ?>>-</button>
                </form>
                <span><?= $item['cantidad']; ?></span>
                <form method="post" action="actualizarCarrito.php" style="display:inline;">
                    <input type="hidden" name="producto_id" value="<?= $id; ?>">
                    <input type="hidden" name="accion" value="aumentar">
                    <button type="submit">+</button>
                </form>
            </div>
            <form method="post" action="eliminarProducto.php" style="display:inline;">
                <input type="hidden" name="producto_id" value="<?= $id; ?>">
                <button class="item-remove" title="Eliminar producto" type="submit">🗑️</button>
            </form>
        </div>
        <?php endforeach; ?>

        <div class="total-section">
            <span>Total del Pedido:</span>
            <span><?= number_format($total, 2, ',', '.'); ?>€</span>
        </div>

        <div class="btn-group">
            <a href="tienda.php" class="btn">Seguir Comprando</a>
            <form method="post" style="display:inline; margin:0; padding:0;">
                <button type="submit" name="finalizar_pago" class="btn" id="finalizar-pago-btn">Finalizar y Pagar</button>
            </form>
        </div>
    </div>
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

<?php if (isset($_POST['finalizar_pago'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = '<i class="fa-solid fa-circle-check"></i> ¡Gracias por tu compra!';
        document.body.appendChild(toast);
        setTimeout(()=>{ window.location.href = 'home.php'; }, 2200);
    });
    </script>
<?php unset($_SESSION['carrito']); ?>
<?php endif; ?>
<style>
.toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: var(--primary-blue, #0a3d62);
    color: #fff;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(10,61,98,.13);
    font-weight: 600;
    font-size: .95rem;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: .6rem;
    animation: slideIn .3s ease;
}
@keyframes slideIn { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
</style>
<script src="global.js"></script>
</body>
</html>