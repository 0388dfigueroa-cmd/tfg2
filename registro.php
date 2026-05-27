<?php

session_start();
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
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);

    if ($username && $email && $password && $password2) {
        if ($password !== $password2) {
            $error = "Las contraseñas no coinciden.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hash);
            if ($stmt->execute()) {
                $success = "Cuenta creada correctamente. <a href='login.php'>Inicia sesión</a>";
            } else {
                $error = "Error: El usuario o email ya existe.";
            }
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pasca y Pesca | Registro</title>
<link rel="stylesheet" href="styles.css">
<style>
.main-content { flex:1; display:flex; justify-content:center; align-items:center; padding:3rem 1rem; }
.register-container { background:#fff; padding:2.2rem 2.5rem; border-radius:var(--radius-md); box-shadow:var(--shadow-md); width:100%; max-width:440px; border:1px solid var(--border); }
.register-container h2 { text-align:center; color:var(--primary); font-family:'Montserrat',sans-serif; margin-bottom:1.5rem; font-size:1.4rem; }
.register-container input { width:100%; padding:.75rem 1rem; margin-bottom:1rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); font-size:.95rem; outline:none; transition:.2s; background:#fafbfc; box-sizing:border-box; }
.register-container input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(30,136,229,.1); }
.btn-submit { width:100%; padding:.75rem; background:var(--accent); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer; font-weight:700; font-size:1rem; transition:.2s; margin-top:.3rem; }
.btn-submit:hover { background:var(--accent-dark); }
.msg-error { color:var(--danger); text-align:center; margin-bottom:1rem; font-size:.9rem; background:rgba(231,76,60,.08); padding:.6rem 1rem; border-radius:var(--radius-sm); }
.msg-success { color:var(--success); text-align:center; margin-bottom:1rem; font-size:.9rem; background:rgba(39,174,96,.08); padding:.6rem 1rem; border-radius:var(--radius-sm); }
.login-link { text-align:center; margin-top:1rem; font-size:.9rem; color:var(--muted); }
.login-link a { color:var(--accent); font-weight:600; }
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
        <li><a href="tecnicas.php">Técnicas</a></li>
        <li><a href="zonasCalientes.php">Zonas</a></li>
        <li><a href="foro.php">Foro</a></li>
        <li><a href="login.php" class="btn btn-primary" style="padding:.42rem 1rem;font-size:.82rem;">Entrar</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="main-content">
  <div class="register-container">
    <h2><i class="fa-solid fa-user-plus"></i> Crear Cuenta</h2>
    <?php if ($error): ?><div class="msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="text" name="username" placeholder="Nombre de usuario" required>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <input type="password" name="password2" placeholder="Repetir contraseña" required>
      <button type="submit" class="btn-submit">Registrarse</button>
    </form>
    <div class="login-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></div>
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
    <div class="footer-bottom"><?= date("Y") ?> Pasca y Pesca. Todos los derechos reservados.</div>
  </div>
</footer>

<script src="global.js"></script>
</body>
</html>
    }
    h2 {
        text-align: center;
        color: var(--primary-blue);
        margin-bottom: 20px;
    }
    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }
    .btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-blue);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        font-weight: 600;
    }
    .btn:hover {
        background: var(--accent-blue);
    }
    .error { color: red; margin-bottom: 10px; text-align: center; }
    .success { color: green; margin-bottom: 10px; text-align: center; }
    .login-link { text-align: center; margin-top: 10px; }
    .login-link a { color: var(--accent-blue); text-decoration: none; }
</style>
</head>
<body>

<div class="register-container">
    <h2>Crear Cuenta</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<script src="global.js"></script>
</body>
</html>