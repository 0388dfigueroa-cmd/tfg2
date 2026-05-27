<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$productos = [
    ["id" => 1, "nombre" => "Caña de Pescar Pro X", "precio" => 89.99, "img" => "img/cañaProX.webp", "badge" => "Más vendido"],
    ["id" => 2, "nombre" => "Carrete Shimano 5000", "precio" => 129.99, "img" => "img/carreteShimano5000.webp", "badge" => "Premium"],
    ["id" => 3, "nombre" => "Kit Señuelos Premium", "precio" => 39.99, "img" => "img/señuelosPremium.webp", "badge" => "Oferta"]
];

$mensaje_alerta = '';
if (isset($_POST['add_to_cart']) && isset($_POST['producto_id']) && isset($_SESSION['user_id'])) {
    $prodID = (int)$_POST['producto_id'];
    foreach ($productos as $p) {
        if ($p['id'] === $prodID) {
            if (isset($_SESSION['carrito'][$prodID])) {
                $_SESSION['carrito'][$prodID]['cantidad']++;
            } else {
                $_SESSION['carrito'][$prodID] = ["nombre" => $p['nombre'], "precio" => $p['precio'], "cantidad" => 1];
            }
            $_SESSION['mensaje_carrito'] = "✔ " . htmlspecialchars($p['nombre']) . " añadido al carrito";
            break;
        }
    }
    header('Location: index.php');
    exit;
}
if (isset($_SESSION['mensaje_carrito'])) {
    $mensaje_alerta = $_SESSION['mensaje_carrito'];
    unset($_SESSION['mensaje_carrito']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pasca y Pesca | Inicio</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
    /* ===== HERO ===== */
    .hero {
        position: relative;
        background: linear-gradient(135deg, rgba(7,45,73,.95) 0%, rgba(10,61,98,.88) 55%, rgba(21,101,192,.82) 100%),
            url("img/logo.png") center/cover no-repeat;
        padding: 7rem 0 5rem;
        text-align: center;
        color: #fff;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        bottom: -2px; left: 0; right: 0;
        height: 80px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 80'%3E%3Cpath fill='%23f4f6f9' fill-opacity='1' d='M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z'/%3E%3C/svg%3E") center bottom/cover no-repeat;
    }
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.35);
        color: #fff;
        font-size: .78rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        font-weight: 700;
        padding: .35rem 1rem;
        border-radius: 99px;
        margin-bottom: 1.4rem;
        backdrop-filter: blur(4px);
    }
    .hero h1 { font-family:'Montserrat',sans-serif; font-size: clamp(2rem,5vw,3.4rem); font-weight:800; margin-bottom:.7rem; text-shadow: 0 2px 12px rgba(0,0,0,.3); }
    .hero p { font-size:1.15rem; opacity:.9; margin-bottom:2.2rem; max-width:560px; margin-left:auto; margin-right:auto; }
    .hero-btns { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-bottom: 3.5rem; }
    .hero-btn-primary {
        background: var(--accent);
        color: #fff;
        padding: .85rem 2rem;
        border-radius: 99px;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: .2s;
        box-shadow: 0 4px 18px rgba(30,136,229,.45);
    }
    .hero-btn-primary:hover { background: var(--accent-dark); transform: translateY(-2px); }
    .hero-btn-outline {
        background: rgba(255,255,255,.12);
        border: 1.5px solid rgba(255,255,255,.5);
        color: #fff;
        padding: .85rem 2rem;
        border-radius: 99px;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: .2s;
        backdrop-filter: blur(4px);
    }
    .hero-btn-outline:hover { background: rgba(255,255,255,.22); transform: translateY(-2px); }

    /* ===== STATS ===== */
    .stats-bar {
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: 1.4rem 0;
        box-shadow: var(--shadow-sm);
    }
    .stats-inner { display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap; }
    .stat-item { text-align: center; }
    .stat-num { font-family: 'Montserrat', sans-serif; font-size: 1.7rem; font-weight: 800; color: var(--primary); line-height: 1; }
    .stat-label { font-size: .8rem; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-top: .2rem; }

    /* ===== SECCIÓN CATEGORÍAS ===== */
    .categories-section { padding: 4.5rem 0 3rem; }
    .section-header { text-align: center; margin-bottom: 2.5rem; }
    .section-tag { display: inline-block; background: #dbeeff; color: var(--accent); font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: .3rem .9rem; border-radius: 99px; margin-bottom: .7rem; }
    .section-header h2 { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: .5rem; }
    .section-header p { color: var(--muted); font-size: .97rem; max-width: 500px; margin: 0 auto; }
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.2rem; }
    .cat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 2rem 1.2rem;
        text-align: center;
        text-decoration: none;
        transition: .2s;
        display: block;
    }
    .cat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-4px); border-color: var(--accent); }
    .cat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; }
    .cat-card h3 { font-family: 'Montserrat', sans-serif; font-size: .95rem; font-weight: 700; color: var(--primary); margin-bottom: .3rem; }
    .cat-card p { font-size: .78rem; color: var(--muted); }

    /* ===== PRODUCTOS DESTACADOS ===== */
    .products-section { padding: 4rem 0; background: linear-gradient(180deg, #f4f6f9 0%, #eef3fa 100%); }
    .product-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.8rem; margin-top:2rem; max-width:1000px; margin-left:auto; margin-right:auto; }
    @media(max-width:860px){ .product-grid { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:560px){ .product-grid { grid-template-columns:1fr; } }
    .product-item {
        background:#fff;
        border-radius:16px;
        border:1px solid var(--border);
        overflow:hidden;
        transition:box-shadow .25s,transform .25s;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .product-item:hover { box-shadow: 0 12px 36px rgba(10,61,98,.14); transform:translateY(-5px); }
    .product-badge {
        display: inline-block;
        background: var(--accent);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        padding: .25rem .7rem;
        border-radius: 99px;
        z-index: 1;
    }
    .product-img-wrap { position: relative; background: var(--sand-light); overflow: hidden; }
    .product-img-wrap img { width:100%; height:220px; object-fit:contain; padding:1.2rem; transition: transform .3s; }
    .product-item:hover .product-img-wrap img { transform: scale(1.06); }
    .product-item-body { padding:1.3rem; flex: 1; display: flex; flex-direction: column; }
    .product-cat-tag { font-size: .72rem; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: .07em; margin-bottom: .3rem; }
    .product-name { font-weight:700; font-size:1.05rem; color:var(--primary); margin-bottom:.5rem; line-height: 1.3; }
    .product-stars { color: #f39c12; font-size: .85rem; margin-bottom: .5rem; }
    .product-price { font-size:1.25rem; font-weight:800; color:var(--accent); margin-bottom:1rem; }
    .add-to-cart {
        margin-top: auto;
        width:100%; padding:.7rem;
        background:var(--primary);
        color:#fff;
        border:none;
        border-radius:10px;
        font-weight:700;
        cursor:pointer;
        transition: .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        font-size: .95rem;
    }
    .add-to-cart:hover { background:var(--accent); }

    /* ===== BANNER CTA ===== */
    .cta-banner {
        background: linear-gradient(135deg, var(--primary) 0%, #1565c0 100%);
        color: #fff;
        padding: 4rem 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .cta-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .cta-banner h2 { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: .8rem; }
    .cta-banner p { font-size: 1.05rem; opacity: .88; margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto; }
    .cta-banner .btn-cta {
        background: #fff;
        color: var(--primary);
        padding: .9rem 2.4rem;
        border-radius: 99px;
        font-weight: 800;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: .2s;
    }
    .cta-banner .btn-cta:hover { background: #e8f0ff; transform: translateY(-2px); }

    /* ===== ¿POR QUÉ ELEGIRNOS? ===== */
    .why-section { padding: 5rem 0; background: #fff; }
    .features { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:2rem; margin-top:2rem; }
    .feature-box {
        border-radius:16px;
        padding:2.2rem 1.5rem;
        text-align:center;
        background: linear-gradient(145deg, #f8fbff, #eef4ff);
        border: 1px solid #d8e8ff;
        transition: .2s;
    }
    .feature-box:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .icon-circle {
        width:72px; height:72px;
        background:linear-gradient(135deg,#1e88e5,#1565c0);
        border-radius:50%;
        margin:0 auto 1.2rem;
        display:flex; align-items:center; justify-content:center;
        box-shadow: 0 4px 16px rgba(30,136,229,.3);
    }
    .icon-circle i { font-size:1.6rem; color:#fff; }
    .feature-box h3 { font-weight:700; font-size:1rem; color:var(--primary); margin-bottom:.5rem; font-family:'Montserrat',sans-serif; }
    .feature-box p { font-size:.88rem; color:var(--muted); line-height:1.6; }

    /* ===== TESTIMONIOS ===== */
    .testimonials-section { padding: 5rem 0; background: var(--bg); }
    .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
    .testimonial-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.8rem;
        position: relative;
    }
    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 1rem; right: 1.4rem;
        font-size: 4rem;
        color: var(--accent);
        opacity: .12;
        font-family: Georgia, serif;
        line-height: 1;
    }
    .testimonial-stars { color: #f39c12; font-size: .9rem; margin-bottom: .8rem; }
    .testimonial-text { font-size: .95rem; color: var(--dark); line-height: 1.65; margin-bottom: 1.2rem; font-style: italic; }
    .testimonial-author { display: flex; align-items: center; gap: .8rem; }
    .author-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--primary));
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: .9rem;
    }
    .author-name { font-weight: 700; font-size: .9rem; color: var(--primary); }
    .author-loc { font-size: .78rem; color: var(--muted); }

    /* ===== TOAST ===== */
    .toast {
        position: fixed;
        bottom: 2rem; right: 2rem;
        background: var(--primary);
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        font-weight: 600;
        font-size: .95rem;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: .6rem;
        animation: slideIn .3s ease;
        max-width: 340px;
    }
    @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    /* ===== UTILITIES ===== */
    .section-title { font-family:'Montserrat',sans-serif; font-size:2rem; font-weight:800; color:var(--primary); margin-bottom:.4rem; }
    .section-subtitle { color:var(--muted); font-size:.97rem; margin-bottom:2rem; }
    </style>
</head>
<body>

<header>
    <div class="container">
        <nav>
            <a href="index.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="tienda.php">Tienda</a></li>
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

<!-- HERO -->
<section class="hero">
    <div class="container" style="position:relative;z-index:1;">
        <span class="hero-badge"><i class="fa-solid fa-star" style="color:#f1c40f"></i> &nbsp;La tienda n.º 1 de pesca deportiva</span>
        <h1>Tu pasión por el mar<br>empieza aquí</h1>
        <p>Equipo profesional, marcas líderes y la mayor comunidad de pescadores de España. Todo en un solo lugar.</p>
        <div class="hero-btns">
            <a href="tienda.php" class="hero-btn-primary"><i class="fa-solid fa-store"></i> Ver tienda</a>
            <a href="zonasCalientes.php" class="hero-btn-outline"><i class="fa-solid fa-map-location-dot"></i> Zonas de pesca</a>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="container">
        <div class="stats-inner">
            <div class="stat-item">
                <div class="stat-num">+100</div>
                <div class="stat-label">Productos</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">+2.000</div>
                <div class="stat-label">Clientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">15</div>
                <div class="stat-label">Marcas premium</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">48h</div>
                <div class="stat-label">Envío exprés</div>
            </div>
        </div>
    </div>
</div>

<!-- CATEGORÍAS -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Explora</span>
            <h2>Todo lo que necesitas</h2>
            <p>Desde cañas de competición hasta accesorios esenciales, lo tenemos todo.</p>
        </div>
        <div class="cat-grid">
            <a href="tienda.php?categoria=Cañas" class="cat-card">
                <div class="cat-icon" style="background:#dbeeff;"><i class="fa-solid fa-wand-magic-sparkles" style="color:#1e88e5;font-size:1.5rem;"></i></div>
                <h3>Cañas</h3>
                <p>Pro y recreativas</p>
            </a>
            <a href="tienda.php?categoria=Carretes" class="cat-card">
                <div class="cat-icon" style="background:#e8f5e9;"><i class="fa-solid fa-circle-nodes" style="color:#27ae60;font-size:1.4rem;"></i></div>
                <h3>Carretes</h3>
                <p>Todas las tallas</p>
            </a>
            <a href="tienda.php?categoria=Señuelos" class="cat-card">
                <div class="cat-icon" style="background:#fff3e0;"><i class="fa-solid fa-shrimp" style="color:#f39c12;font-size:1.4rem;"></i></div>
                <h3>Señuelos</h3>
                <p>Artificiales y vivos</p>
            </a>
            <a href="tienda.php?categoria=Accesorios" class="cat-card">
                <div class="cat-icon" style="background:#fce4ec;"><i class="fa-solid fa-toolbox" style="color:#e74c3c;font-size:1.4rem;"></i></div>
                <h3>Accesorios</h3>
                <p>Complementos y kits</p>
            </a>
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Destacados</span>
            <h2>Los favoritos de la comunidad</h2>
            <p>Los productos más valorados por nuestros clientes expertos.</p>
        </div>
        <div class="product-grid">
            <?php foreach ($productos as $p): ?>
            <div class="product-item">
                <div class="product-img-wrap">
                    <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                </div>
                <div class="product-item-body">
                    <span class="product-badge" style="margin-bottom:.6rem;"><?= htmlspecialchars($p['badge']) ?></span>
                    <div class="product-stars" style="margin-top:.4rem;"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i> <span style="color:var(--muted);font-size:.8rem;">(4.0)</span></div>
                    <p class="product-name"><?= htmlspecialchars($p['nombre']) ?></p>
                    <p class="product-price">€<?= number_format($p['precio'], 2) ?></p>
                    <form method="POST">
                        <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="add_to_cart" value="1">
                        <button type="submit" class="add-to-cart" onclick="return validarCarrito(event)">
                            <i class="fa-solid fa-cart-plus"></i> Añadir al carrito
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2.5rem;">
            <a href="tienda.php" class="hero-btn-primary"><i class="fa-solid fa-arrow-right"></i> Ver todo el catálogo</a>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<section class="cta-banner">
    <div class="container" style="position:relative;z-index:1;">
        <h2><i class="fa-solid fa-fish"></i> ¿Listo para tu próxima aventura?</h2>
        <p>Únete a más de 2.000 pescadores que ya confían en nosotros. Regístrate gratis y consigue acceso anticipado a ofertas exclusivas.</p>
        <a href="registro.php" class="btn-cta"><i class="fa-solid fa-user-plus"></i> Crear cuenta gratis</a>
    </div>
</section>

<!-- ¿POR QUÉ ELEGIRNOS? -->
<section class="why-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Ventajas</span>
            <h2>¿Por qué elegirnos?</h2>
            <p>La excelencia en el mar comienza con el equipo adecuado.</p>
        </div>
        <div class="features">
            <div class="feature-box">
                <div class="icon-circle"><i class="fa-solid fa-medal"></i></div>
                <h3>Calidad Premium</h3>
                <p>Solo trabajamos con las marcas líderes del mercado profesional, garantizando el mejor rendimiento.</p>
            </div>
            <div class="feature-box">
                <div class="icon-circle"><i class="fa-solid fa-truck-fast"></i></div>
                <h3>Envío Exprés 48h</h3>
                <p>Tus pedidos en casa en menos de 48 horas para que no pierdas ni un lance.</p>
            </div>
            <div class="feature-box">
                <div class="icon-circle"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Garantía Total</h3>
                <p>Devoluciones sin preguntas en 30 días. Tu satisfacción es nuestra prioridad.</p>
            </div>
            <div class="feature-box">
                <div class="icon-circle"><i class="fa-solid fa-headset"></i></div>
                <h3>Asesoría Pro</h3>
                <p>Nuestro equipo de expertos pescadores te ayudará a elegir el equipo perfecto.</p>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIOS -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Opiniones</span>
            <h2>Lo que dicen nuestros clientes</h2>
            <p>Más de 2.000 pescadores satisfechos avalan nuestra calidad.</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                <p class="testimonial-text">La Caña Pro X es increíble. La mejor compra que he hecho en años. Llegó perfectamente embalada en menos de 24 horas.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">JM</div>
                    <div>
                        <div class="author-name">José María R.</div>
                        <div class="author-loc"><i class="fa-solid fa-location-dot"></i> Málaga</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                <p class="testimonial-text">Llevo 3 años comprando aquí y nunca me han fallado. El Kit de Señuelos Premium es una pasada, relación calidad-precio imbatible.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">AL</div>
                    <div>
                        <div class="author-name">Ana López</div>
                        <div class="author-loc"><i class="fa-solid fa-location-dot"></i> Valencia</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div>
                <p class="testimonial-text">El foro de la comunidad es muy activo. Siempre encuentro consejos de otros pescadores y ofertas exclusivas antes que nadie.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">CR</div>
                    <div>
                        <div class="author-name">Carlos Ruiz</div>
                        <div class="author-loc"><i class="fa-solid fa-location-dot"></i> Bilbao</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<?php if ($mensaje_alerta): ?>
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($mensaje_alerta) ?></div>
<script>setTimeout(() => { const t = document.getElementById('toast'); if(t) t.remove(); }, 3500);</script>
<?php endif; ?>

<script>
const usuarioLogueado = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
function validarCarrito(event) {
    if (!usuarioLogueado) {
        event.preventDefault();
        window.location.href = 'login.php';
        return false;
    }
    return true;
}
</script>
<script src="global.js"></script>
</body>
</html>
