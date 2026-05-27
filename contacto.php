<?php
session_start();
$usuario_logueado = isset($_SESSION['user_id']);

$enviado  = false;
$errores  = [];
$nombre   = $email = $asunto = $mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim(htmlspecialchars($_POST['nombre']  ?? ''));
    $email   = trim($_POST['email']   ?? '');
    $asunto  = trim(htmlspecialchars($_POST['asunto']  ?? ''));
    $mensaje = trim(htmlspecialchars($_POST['mensaje'] ?? ''));

    if ($nombre  === '') $errores[] = 'El nombre es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if ($asunto  === '') $errores[] = 'El asunto es obligatorio.';
    if (strlen($mensaje) < 10) $errores[] = 'El mensaje debe tener al menos 10 caracteres.';

    if (empty($errores)) {
        // En producción aquí iría mail() o un servicio SMTP
        $enviado = true;
        $nombre = $email = $asunto = $mensaje = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="styles.css" />
<title>Pasca y Pesca | Contacto</title>
<style>
/* HERO */
.zona-hero {
    background: linear-gradient(135deg, rgba(7,45,73,.95) 0%, rgba(10,61,98,.88) 55%, rgba(21,101,192,.82) 100%);
    padding: 4rem 0 3.5rem;
    text-align: center;
    color: #fff;
}
.zona-hero .hero-badge {
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
    margin-bottom: 1.2rem;
    backdrop-filter: blur(4px);
}
.zona-hero h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    margin-bottom: .6rem;
    text-shadow: 0 2px 12px rgba(0,0,0,.3);
}
.zona-hero p { font-size: 1.05rem; opacity: .88; max-width: 500px; margin: 0 auto; }

/* LAYOUT CONTACTO */
.contact-layout {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2.5rem;
    padding: 3rem 0 4rem;
}
@media (max-width: 800px) {
    .contact-layout { grid-template-columns: 1fr; }
}

/* INFO CARDS */
.info-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 1.5rem 1.4rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.1rem;
    transition: box-shadow .2s, transform .2s;
}
.info-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.info-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.info-icon.blue  { background: #dbeeff; color: var(--accent); }
.info-icon.green { background: #e8f5e9; color: #27ae60; }
.info-icon.orange{ background: #fff3e0; color: #e67e22; }
.info-card h4 { font-weight: 700; color: var(--primary); font-size: .95rem; margin-bottom: .25rem; }
.info-card p  { color: var(--muted); font-size: .88rem; line-height: 1.5; }
.info-card a  { color: var(--accent); font-size: .88rem; }

/* FORM */
.form-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 2rem 2.2rem;
    box-shadow: var(--shadow-sm);
}
.form-card h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.3rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }
.form-group { margin-bottom: 1.1rem; }
.form-group label {
    display: block;
    font-size: .85rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: .4rem;
}
.form-group input,
.form-group textarea {
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .72rem 1rem;
    font-size: .95rem;
    color: var(--dark);
    background: #fafbfc;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(30,136,229,.1);
    background: #fff;
}
.form-group textarea { resize: vertical; min-height: 130px; }
.btn-submit {
    width: 100%;
    padding: .85rem;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: background .2s, transform .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    margin-top: .5rem;
}
.btn-submit:hover { background: var(--accent); transform: translateY(-1px); }

/* MENSAJES */
.alert-list { list-style: none; padding: 0; }
.alert-list li::before { content: "• "; }
.success-box {
    background: #e8f5e9;
    border-left: 4px solid #43a047;
    color: #2e7d32;
    border-radius: var(--radius-sm);
    padding: 1rem 1.2rem;
    margin-bottom: 1.2rem;
    display: flex;
    gap: .6rem;
    align-items: flex-start;
}

/* MAPA IFRAME */
.map-wrap {
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    margin-top: 2.5rem;
}
.map-wrap iframe { width: 100%; height: 300px; border: none; display: block; }
.map-wrap h3 {
    font-family: 'Montserrat', sans-serif;
    font-size: 1rem;
    color: var(--primary);
    padding: 1rem 1.2rem .6rem;
    background: #fff;
    border-bottom: 1px solid var(--border);
    display: flex;
    gap: .5rem;
    align-items: center;
}

/* HORARIO */
.horario-table { width: 100%; font-size: .88rem; border-collapse: collapse; margin-top: .5rem; }
.horario-table tr td { padding: .3rem .1rem; color: var(--muted); }
.horario-table tr td:last-child { text-align: right; color: var(--dark); font-weight: 600; }
.horario-table tr.today td { color: var(--accent); }
</style>
</head>
<body>

<header>
<div class="container">
<nav>
  <a href="index.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
  <ul class="nav-links">
    <li><a href="index.php">Inicio</a></li>
    <li><a href="tienda.php">Tienda</a></li>
    <li><a href="foro.php">Foro</a></li>
    <li><a href="tecnicas.php">Técnicas</a></li>
    <li><a href="zonasCalientes.php">Zonas</a></li>
    <li><a href="contacto.php" class="active">Contacto</a></li>
    <?php if ($usuario_logueado): ?>
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
<div class="zona-hero">
  <div class="container">
    <span class="hero-badge"><i class="fa-solid fa-envelope"></i> &nbsp;Estamos aquí para ayudarte</span>
    <h1>Contacto</h1>
    <p>Escríbenos con cualquier duda, sugerencia o consulta sobre nuestros productos</p>
  </div>
</div>

<main class="container">
<div class="contact-layout">

  <!-- COLUMNA IZQUIERDA: Información -->
  <div>
    <div class="info-card">
      <div class="info-icon blue"><i class="fa-solid fa-location-dot"></i></div>
      <div>
        <h4>Dirección</h4>
        <p>Calle del Río Ebro, 12<br>50001, Zaragoza, España</p>
      </div>
    </div>

    <div class="info-card">
      <div class="info-icon green"><i class="fa-solid fa-phone"></i></div>
      <div>
        <h4>Teléfono</h4>
        <p>+34 600 123 456</p>
        <a href="tel:+34600123456">Llamar ahora</a>
      </div>
    </div>

    <div class="info-card">
      <div class="info-icon orange"><i class="fa-solid fa-envelope"></i></div>
      <div>
        <h4>Email</h4>
        <p>info@pascaypesca.es</p>
        <a href="mailto:info@pascaypesca.es">Enviar email</a>
      </div>
    </div>

    <!-- Horario -->
    <div class="info-card" style="flex-direction:column;gap:.6rem;">
      <div style="display:flex;align-items:center;gap:.8rem;">
        <div class="info-icon blue"><i class="fa-solid fa-clock"></i></div>
        <h4 style="margin:0;">Horario de atención</h4>
      </div>
      <table class="horario-table">
        <tr><td>Lunes – Viernes</td><td>9:00 – 19:00</td></tr>
        <tr><td>Sábado</td><td>10:00 – 14:00</td></tr>
        <tr><td>Domingo</td><td>Cerrado</td></tr>
      </table>
    </div>

    <!-- Redes Sociales -->
    <div style="margin-top:1.2rem;display:flex;gap:.8rem;flex-wrap:wrap;">
      <a href="https://facebook.com/" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:.45rem;background:#1877f2;color:#fff;padding:.55rem 1.1rem;border-radius:99px;font-size:.82rem;font-weight:600;text-decoration:none;transition:opacity .2s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-brands fa-facebook-f"></i> Facebook
      </a>
      <a href="https://instagram.com/" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:.45rem;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff;padding:.55rem 1.1rem;border-radius:99px;font-size:.82rem;font-weight:600;text-decoration:none;transition:opacity .2s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-brands fa-instagram"></i> Instagram
      </a>
      <a href="https://youtube.com/" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:.45rem;background:#ff0000;color:#fff;padding:.55rem 1.1rem;border-radius:99px;font-size:.82rem;font-weight:600;text-decoration:none;transition:opacity .2s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-brands fa-youtube"></i> YouTube
      </a>
    </div>
  </div>

  <!-- COLUMNA DERECHA: Formulario -->
  <div>
    <div class="form-card">
      <h2><i class="fa-solid fa-paper-plane" style="color:var(--accent);margin-right:.4rem;"></i>Envíanos un mensaje</h2>

      <?php if ($enviado): ?>
      <div class="success-box">
        <i class="fa-solid fa-circle-check" style="font-size:1.3rem;margin-top:.1rem;"></i>
        <div>
          <strong>¡Mensaje enviado!</strong><br>
          <span style="font-size:.9rem;">Gracias por contactarnos. Te responderemos en menos de 24 horas.</span>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($errores)): ?>
      <div class="alert-error alert" style="margin-bottom:1rem;flex-direction:column;align-items:flex-start;">
        <strong><i class="fa-solid fa-circle-exclamation"></i> Por favor corrige los siguientes errores:</strong>
        <ul class="alert-list" style="margin-top:.4rem;padding-left:.5rem;">
          <?php foreach ($errores as $e): ?>
          <li><?= $e ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label for="nombre"><i class="fa-solid fa-user" style="color:var(--accent);margin-right:.3rem;"></i>Nombre *</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" value="<?= htmlspecialchars($nombre) ?>" required>
          </div>
          <div class="form-group">
            <label for="email"><i class="fa-solid fa-envelope" style="color:var(--accent);margin-right:.3rem;"></i>Email *</label>
            <input type="email" id="email" name="email" placeholder="tu@email.com" value="<?= htmlspecialchars($email) ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label for="asunto"><i class="fa-solid fa-tag" style="color:var(--accent);margin-right:.3rem;"></i>Asunto *</label>
          <input type="text" id="asunto" name="asunto" placeholder="¿En qué podemos ayudarte?" value="<?= htmlspecialchars($asunto) ?>" required>
        </div>
        <div class="form-group">
          <label for="mensaje"><i class="fa-solid fa-message" style="color:var(--accent);margin-right:.3rem;"></i>Mensaje *</label>
          <textarea id="mensaje" name="mensaje" placeholder="Escribe aquí tu consulta o mensaje..."><?= htmlspecialchars($mensaje) ?></textarea>
        </div>
        <button type="submit" class="btn-submit">
          <i class="fa-solid fa-paper-plane"></i> Enviar mensaje
        </button>
      </form>
    </div>

    <!-- Mapa -->
    <div class="map-wrap">
      <h3><i class="fa-solid fa-map-location-dot" style="color:var(--accent);"></i> Nuestra ubicación</h3>
      <iframe
        src="https://www.openstreetmap.org/export/embed.html?bbox=-0.9400,41.6200,-0.8600,41.6700&layer=mapnik&marker=41.648,−0.889"
        allowfullscreen
        loading="lazy"
        title="Mapa de ubicación">
      </iframe>
    </div>
  </div>

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
    <div class="footer-bottom">&copy; <?= date('Y') ?> Pasca y Pesca. Todos los derechos reservados.</div>
  </div>
</footer>

<script src="global.js"></script>
</body>
</html>
