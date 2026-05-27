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
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }
$conn->set_charset("utf8");

$error = '';
$activeTab = isset($_POST['tab']) ? $_POST['tab'] : 'user';

$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'admin1234';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $activeTab = trim($_POST['tab']);

    if (!empty($username) && !empty($password)) {
        if ($activeTab === 'admin') {
            if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: admin.php');
                exit;
            } else {
                $error = "Usuario o contraseña de administrador incorrectos.";
            }
        } else {
            $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userId, $hashedPassword);
                $stmt->fetch();
                if ($hashedPassword && password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    header('Location: foro.php');
                    exit;
                } else {
                    $error = "Usuario o contraseña incorrectos.";
                }
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
            $stmt->close();
        }
    } else {
        $error = "Por favor ingresa usuario y contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" />
<title>Pasca y Pesca | Login</title>
<style>
.main-content { flex:1; display:flex; justify-content:center; align-items:center; padding:3rem 1rem; }
.login-container { background:#fff; padding:2.2rem 2.5rem; border-radius:var(--radius-md); box-shadow:var(--shadow-md); width:100%; max-width:420px; border:1px solid var(--border); }
.login-container h2 { text-align:center; color:var(--primary); font-family:'Montserrat',sans-serif; margin-bottom:1.5rem; font-size:1.4rem; }
.login-container input[type=text],
.login-container input[type=password] { width:100%; padding:.75rem 1rem; margin-bottom:1rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); font-size:.95rem; outline:none; transition:.2s; background:#fafbfc; }
.login-container input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(30,136,229,.1); }
.login-container .btn-submit { width:100%; padding:.75rem; background:var(--accent); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer; font-weight:700; font-size:1rem; transition:.2s; margin-top:.3rem; }
.login-container .btn-submit:hover { background:var(--accent-dark); }
.error { color:var(--danger); text-align:center; margin-bottom:1rem; font-size:.9rem; background:rgba(231,76,60,.08); padding:.6rem 1rem; border-radius:var(--radius-sm); }
.register-link { text-align:center; margin-top:1rem; font-size:.9rem; color:var(--muted); }
.register-link a { color:var(--accent); font-weight:600; }
.tab-container { display:flex; margin-bottom:1.5rem; border-bottom:2px solid var(--border); }
.tab-button { flex:1; padding:.75rem; background:none; border:none; cursor:pointer; font-weight:700; color:var(--muted); border-bottom:3px solid transparent; transition:.2s; font-size:.92rem; }
.tab-button.active { color:var(--primary); border-bottom-color:var(--accent); }
.tab-button:hover { background:var(--bg); }
.tab-content { display:none; }
.tab-content.active { display:block; }
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
        <li><a href="tecnicas.php">Técnicas</a></li>
        <li><a href="zonasCalientes.php">Zonas de Pesca</a></li>
        <li><a href="foro.php">Foro</a></li>
        <li><a href="registro.php" class="btn btn-outline" style="padding:.4rem .9rem">Registrarse</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="main-content">
  <div class="login-container">
    <h2><i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión</h2>

    <div class="tab-container">
      <button class="tab-button <?= ($activeTab === 'user') ? 'active' : '' ?>" onclick="switchTab('user')">Usuario</button>
      <button class="tab-button <?= ($activeTab === 'admin') ? 'active' : '' ?>" onclick="switchTab('admin')">Administrador</button>
    </div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Tab Usuario -->
    <div id="tab-user" class="tab-content <?= ($activeTab === 'user') ? 'active' : '' ?>">
      <form method="POST">
        <input type="hidden" name="tab" value="user">
        <input type="text" name="username" placeholder="Nombre de usuario" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit" class="btn-submit">Entrar</button>
      </form>
      <div class="register-link">¿No tienes cuenta? <a href="registro.php">Regístrate</a></div>
    </div>

    <!-- Tab Admin -->
    <div id="tab-admin" class="tab-content <?= ($activeTab === 'admin') ? 'active' : '' ?>">
      <form method="POST">
        <input type="hidden" name="tab" value="admin">
        <input type="text" name="username" placeholder="Usuario administrador" required>
        <input type="password" name="password" placeholder="Contraseña admin" required>
        <button type="submit" class="btn-submit">Acceder como Admin</button>
      </form>
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

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.querySelector('.tab-button[onclick="switchTab(\'' + tab + '\')"]').classList.add('active');
}
</script>
<script src="global.js"></script>
</body>
</html>
