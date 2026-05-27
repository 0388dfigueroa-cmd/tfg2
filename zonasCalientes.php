<?php
session_start();
$usuario_logueado = isset($_SESSION['user_id']);

// Medias por zona (simuladas)
$valoraciones = [
  1=>4.2, 2=>3.8, 3=>4.5, 4=>3.9, 5=>4.0,
  6=>3.5, 7=>4.1, 8=>3.7, 9=>4.3, 10=>3.6,
  11=>4.0, 12=>3.8, 13=>4.2, 14=>3.9, 15=>4.1,
  16=>3.7, 17=>3.9, 18=>4.3, 19=>4.0, 20=>4.1
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pasca y Pesca | Zonas Calientes</title>
<link rel="stylesheet" href="styles.css" />
<style>
/* ===== HERO ZONA ===== */
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
.zona-hero p {
    font-size: 1.05rem;
    opacity: .88;
    max-width: 500px;
    margin: 0 auto;
}

/* ===== ESTRELLAS ===== */
.estrellas { margin: .6rem 0; }
.estrellas i { cursor: pointer; font-size: 1.1rem; transition: transform .15s; }
.estrellas i:hover { transform: scale(1.2); }
.no-login { color: var(--danger); font-size: .85rem; margin-top: .5rem; }
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
    <li><a href="zonasCalientes.php" class="active">Zonas</a></li>
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
    <span class="hero-badge"><i class="fa-solid fa-map-location-dot"></i> &nbsp;Mapa de pesca de España</span>
    <h1>Zonas Calientes en España</h1>
    <p>Descubre los mejores puntos de pesca por toda la península</p>
  </div>
</div>

<main class="container" style="padding-top:2.5rem;">

<div class="filter-bar">
  <span class="filter-label">Especie:</span>
  <span class="filter-pill active" data-filter="all">Todos</span>
  <span class="filter-pill" data-filter="Carpa"><i class="fa-solid fa-fish"></i> Carpa</span>
  <span class="filter-pill" data-filter="Siluro"><i class="fa-solid fa-fish"></i> Siluro</span>
  <span class="filter-pill" data-filter="Black Bass"><i class="fa-solid fa-fish"></i> Black Bass</span>
  <span class="filter-pill" data-filter="Lubina"><i class="fa-solid fa-fish"></i> Lubina</span>
  <span class="filter-pill" data-filter="Trucha"><i class="fa-solid fa-fish"></i> Trucha</span>
  <span class="filter-pill" data-filter="Dorada"><i class="fa-solid fa-fish"></i> Dorada</span>
</div>

<?php
// Conexión a la base de datos (igual que en tienda.php)
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

$zonas = [];
$sql = "SELECT id, nombre, ubicacion, especie_principal, dificultad, imagen FROM zonas_calientes ORDER BY id ASC";
$res = $conn->query($sql);
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $zonas[] = $row;
  }
  $res->free();
}
$conn->close();


$i = 1;





foreach($zonas as $z) {
  $media = $valoraciones[$i] ?? 4.0;
  // Icono según especie principal
  $icono = '<i class="fa-solid fa-fish"></i>';
  $especie = strtolower($z['especie_principal']);
  if(strpos($especie, 'black bass') !== false) $icono = '<i class="fa-solid fa-water"></i>';
  elseif(strpos($especie, 'trucha') !== false) $icono = '<i class="fa-solid fa-fish-fins"></i>';
  elseif(strpos($especie, 'lubina') !== false) $icono = '<i class="fa-solid fa-fish"></i>';
  elseif(strpos($especie, 'lucio') !== false) $icono = '<i class="fa-solid fa-water"></i>';
  elseif(strpos($especie, 'carpa') !== false) $icono = '<i class="fa-solid fa-fish"></i>';
  elseif(strpos($especie, 'atún') !== false) $icono = '<i class="fa-solid fa-water"></i>';
  elseif(strpos($especie, 'siluro') !== false) $icono = '<i class="fa-solid fa-fish-fins"></i>';

  // Badge de provincia/tipo (primer elemento de ubicacion)
  $provincia = explode(',', $z['ubicacion'])[0];
  $provincia = trim($provincia);

  // Mejor temporada (simulada)
  $temporadas = ['Primavera', 'Verano', 'Otoño', 'Invierno'];
  $temporada = $temporadas[($i-1)%count($temporadas)];

  // Contador de valoraciones (simulado)
  $valoraciones_count = rand(7, 38);


  echo '<div class="zona-card" data-peces="'.htmlspecialchars($z['especie_principal']).'" style="padding:1.1rem 1.1rem;gap:0.7rem;">';
  // Columna principal
  echo '<div style="display:flex;flex-direction:column;gap:.13rem;min-width:0;">';
  echo '<span class="zona-num" style="font-size:.74rem;padding:.13rem .55rem;top:-.5rem;left:.9rem;">#'.$i.'</span>';
  echo '<h3 style="display:flex;align-items:center;gap:.35rem;font-size:1rem;margin-bottom:.18rem;margin-top:.3rem;">'.$icono.' '.htmlspecialchars($z['nombre']).'
    <span class="badge" style="background:#e3f0fd;color:#1565c0;margin-left:.35rem;font-size:.68rem;padding:.18rem .6rem;line-height:1.1;">'.$provincia.'</span>
  </h3>';
  echo '<p class="zona-loc" style="font-size:.83rem;margin-bottom:.18rem;">'.
    '<i class="fa-solid fa-location-dot"></i> '.htmlspecialchars($z['ubicacion']).'</p>';

  echo '<div class="estrellas" data-zona="'.$i.'" data-media="'.$media.'" style="font-size:.97em;margin-bottom:.13rem;">';
  for($s=1;$s<=5;$s++){
    if($media >= $s){
      echo '<i class="fa-solid fa-star activa" data-star="'.$s.'"></i>';
    } elseif($media >= $s-0.5){
      echo '<i class="fa-solid fa-star-half-stroke activa" data-star="'.$s.'"></i>';
    } else {
      echo '<i class="fa-regular fa-star" data-star="'.$s.'"></i>';
    }
  }
  echo '<span style="color:var(--muted);font-size:.75em;margin-left:.38rem;">('.$valoraciones_count.')</span>';
  echo '</div>';

  echo '<div class="zona-tags" style="gap:.25rem;margin-bottom:.3rem;">';
  foreach(explode(",", $z['especie_principal']) as $tag){
    echo '<span class="badge" style="font-size:.68em;padding:.15rem .5rem;">'.trim($tag).'</span>';
  }
  echo '</div>';


  // Metadatos y botón en la misma fila
  echo '<div style="display:flex;align-items:center;gap:1.1rem;margin-top:.18rem;">';
  echo '<div class="zona-meta" style="gap:.7rem;font-size:.82em;">';
  echo '<span><i class="fa-solid fa-gauge"></i> Dificultad: <strong>'.htmlspecialchars($z['dificultad']).'</strong></span>';
  echo '<span><i class="fa-solid fa-calendar"></i> Mejor temporada: <strong>'.$temporada.'</strong></span>';
  echo '</div>';
  echo '<a href="'.htmlspecialchars($z['imagen']).'" target="_blank" class="btn-mapa" style="margin-left:auto;padding:.38rem .9rem;font-size:.82em;">'
    .'<i class="fa-solid fa-map"></i> Ver mapa</a>';
  echo '</div>';

  if(!$usuario_logueado){
    echo '<p class="no-login" style="margin-top:.3rem;">'
      .'<i class="fa-solid fa-lock"></i> Inicia sesión para valorar</p>';
  }
  echo '</div>';
  echo '</div>';
  $i++;
}
?>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Filtros con filter-pills
  document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.addEventListener('click', function() {
      document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      const tipo = this.dataset.filter;
      document.querySelectorAll('.zona-card').forEach(z => {
        z.style.display = (tipo === 'all' || z.dataset.peces.includes(tipo)) ? '' : 'none';
      });
    });
  });
});

<?php if($usuario_logueado): ?>
// Valoraciones usuario: votar y actualizar visual
document.querySelectorAll('.estrellas').forEach(container=>{
    container.querySelectorAll('i').forEach((star,index)=>{
        star.style.cursor = 'pointer';
        star.addEventListener('click',function(){
            const zona = container.dataset.zona;
            const valor = parseInt(this.dataset.star);
            alert('Has valorado la zona '+zona+' con '+valor+' estrellas!');

            // Actualizar visualmente: llenar hasta la estrella clicada
            container.querySelectorAll('i').forEach((s,i)=>{
                if(i < valor){
                    s.classList.remove('fa-regular','fa-star-half-stroke');
                    s.classList.add('fa-solid','activa');
                } else {
                    s.classList.remove('fa-solid','activa','fa-star-half-stroke');
                    s.classList.add('fa-regular');
                }
            });

            // añadir llamada ajax para guardar valoración en BD
            // $.post('guardar_valoracion.php', { zona: zona, valor: valor });
            
        });
    });
});
<?php endif; ?>
</script>

<script src="global.js"></script>
</body>
</html>