<?php
session_start();
$usuario_logueado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="styles.css" />
<title>Pasca y Pesca | Técnicas</title>
<style>
.zona-hero { background:linear-gradient(135deg,rgba(7,45,73,.95) 0%,rgba(10,61,98,.88) 55%,rgba(21,101,192,.82) 100%); padding:4rem 0 3.5rem; text-align:center; color:#fff; }
.zona-hero .hero-badge { display:inline-block; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); color:#fff; font-size:.78rem; letter-spacing:.1em; text-transform:uppercase; font-weight:700; padding:.35rem 1rem; border-radius:99px; margin-bottom:1.2rem; backdrop-filter:blur(4px); }
.zona-hero h1 { font-family:'Montserrat',sans-serif; font-size:clamp(1.8rem,4vw,2.8rem); font-weight:800; margin-bottom:.6rem; text-shadow:0 2px 12px rgba(0,0,0,.3); }
.zona-hero p { font-size:1.05rem; opacity:.88; max-width:500px; margin:0 auto; }
main.container { padding:2rem 0 3rem; flex:1; }
.tecnica { background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:1.5rem; margin-bottom:1.4rem; display:flex; flex-wrap:wrap; align-items:center; gap:1.5rem; transition:box-shadow .2s,transform .2s; box-shadow:var(--shadow-sm); }
.tecnica:hover { box-shadow:var(--shadow-md); transform:translateY(-2px); }
.tecnica .contenido { flex:1 1 320px; }
.tecnica .contenido h2 { font-family:'Montserrat',sans-serif; font-size:1.1rem; color:var(--primary); margin-bottom:.5rem; }
.tecnica .contenido p { color:var(--muted); font-size:.95rem; }
.tip { background:rgba(30,136,229,.07); padding:.7rem 1rem; border-left:4px solid var(--accent); margin-top:.8rem; border-radius:0 var(--radius-sm) var(--radius-sm) 0; font-size:.88rem; color:var(--dark); display:flex; align-items:center; gap:.5rem; }
.tip i { color:var(--accent); }
.tecnica .miniatura { cursor:pointer; flex:0 0 200px; border-radius:var(--radius-sm); overflow:hidden; position:relative; box-shadow:var(--shadow-sm); }
.tecnica .miniatura img { width:200px; height:120px; object-fit:cover; display:block; transition:transform .3s; }
.tecnica .miniatura:hover img { transform:scale(1.05); }
.play-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(10,61,98,.35); transition:background .2s; }
.play-overlay i { color:#fff; font-size:2.2rem; filter:drop-shadow(0 2px 6px rgba(0,0,0,.4)); }
.tecnica .miniatura:hover .play-overlay { background:rgba(10,61,98,.55); }
.modal { display:none; position:fixed; z-index:2000; inset:0; background:rgba(0,0,0,.75); align-items:center; justify-content:center; }
.modal-content { background:#000; border-radius:var(--radius-md); width:90%; max-width:820px; position:relative; }
.modal-close { position:absolute; top:10px; right:15px; color:#fff; font-size:2rem; cursor:pointer; line-height:1; }
iframe { width:100%; height:460px; border:none; border-radius:var(--radius-sm); display:block; }
@media(max-width:800px){ iframe{height:280px;} .tecnica{flex-direction:column;} .tecnica .miniatura{flex:0 0 auto;width:100%;} .tecnica .miniatura img{width:100%;height:180px;} }
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
        <li><a href="tecnicas.php" class="active">Técnicas</a></li>
        <li><a href="zonasCalientes.php">Zonas</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <?php if($usuario_logueado): ?>
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
    <span class="hero-badge"><i class="fa-solid fa-video"></i> &nbsp;Guías en vídeo</span>
    <h1>Técnicas de Pesca</h1>
    <p>Aprende con vídeos reales seleccionados para todos los niveles</p>
  </div>
</div>

<main class="container" style="padding-top:2.5rem;">

    <div class="tecnica">
        <div class="contenido">
            <h2>1. Fundamentos de la pesca</h2>
            <p>Conoce el equipo básico y los primeros pasos para comenzar a pescar.</p>
            <div class="tip"><i class="fa-solid fa-lightbulb"></i> Ideal para principiantes.</div>
        </div>
        <div class="miniatura" data-video="https://www.youtube.com/embed/wNNYNNXhao8">
            <img src="https://img.youtube.com/vi/wNNYNNXhao8/0.jpg" alt="Pesca para principiantes">
            <div class="play-overlay"><i class="fa-solid fa-circle-play"></i></div>
        </div>
    </div>

    <div class="tecnica">
        <div class="contenido">
            <h2>2. Montaje de caña y carrete</h2>
            <p>Aprende a montar tu caña y preparar la línea para cualquier tipo de pesca.</p>
            <div class="tip"><i class="fa-solid fa-fish"></i> Paso a paso práctico.</div>
        </div>
        <div class="miniatura" data-video="https://www.youtube.com/embed/KQ0oVLZ44s0">
            <img src="https://img.youtube.com/vi/KQ0oVLZ44s0/0.jpg" alt="Setup de caña y carrete">
            <div class="play-overlay"><i class="fa-solid fa-circle-play"></i></div>
        </div>
    </div>

    <div class="tecnica">
        <div class="contenido">
            <h2>3. Lanzamiento preciso</h2>
            <p>Domina la distancia y precisión para mejorar tus capturas en cualquier río o lago.</p>
            <div class="tip"><i class="fa-solid fa-hand-pointer"></i> Técnica correcta de casting.</div>
        </div>
        <div class="miniatura" data-video="https://www.youtube.com/embed/xzaPaaw0hk8">
            <img src="https://img.youtube.com/vi/xzaPaaw0hk8/0.jpg" alt="Tutorial de casting">
            <div class="play-overlay"><i class="fa-solid fa-circle-play"></i></div>
        </div>
    </div>

    <div class="tecnica">
        <div class="contenido">
            <h2>4. Nudos básicos</h2>
            <p>Aprende los nudos más útiles para asegurar anzuelos y líneas sin complicaciones.</p>
            <div class="tip"><i class="fa-solid fa-book"></i> Nudos rápidos y confiables.</div>
        </div>
        <div class="miniatura" data-video="https://www.youtube.com/embed/msjHI3Ybf8E">
            <img src="https://img.youtube.com/vi/msjHI3Ybf8E/0.jpg" alt="Los 3 mejores nudos de pesca">
            <div class="play-overlay"><i class="fa-solid fa-circle-play"></i></div>
        </div>
    </div>

    <div class="tecnica">
        <div class="contenido">
            <h2>5. Seguridad y cuidado</h2>
            <p>Tips para pescar con seguridad y liberar los peces sin dañarlos.</p>
            <div class="tip"><i class="fa-solid fa-shield"></i> Pesca responsable y segura.</div>
        </div>
        <div class="miniatura" data-video="https://www.youtube.com/embed/wNNYNNXhao8">
            <img src="https://img.youtube.com/vi/wNNYNNXhao8/0.jpg" alt="Seguridad y pesca responsable">
            <div class="play-overlay"><i class="fa-solid fa-circle-play"></i></div>
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

<div class="modal" id="videoModal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <iframe id="modalIframe" src="" allowfullscreen></iframe>
    </div>
</div>

<script>
// Modal video logic
const modal = document.getElementById('videoModal');
const modalIframe = document.getElementById('modalIframe');
const closeBtn = document.querySelector('.modal-close');

document.querySelectorAll('.miniatura').forEach(mini => {
    mini.addEventListener('click', () => {
        const videoURL = mini.getAttribute('data-video') + "?autoplay=1";
        modalIframe.src = videoURL;
        modal.style.display = "flex";
    });
});

closeBtn.addEventListener('click', () => {
    modal.style.display = "none";
    modalIframe.src = "";
});

window.addEventListener('click', e => {
    if (e.target == modal) {
        modal.style.display = "none";
        modalIframe.src = "";
    }
});
</script>

<script src="global.js"></script>
</body>
</html>