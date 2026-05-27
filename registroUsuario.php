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
$conn->set_charset("utf8");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $passwordInput = $_POST['password'];

    // Validación básica
    if (empty($username) || empty($email) || empty($passwordInput)) {
        $error = "Todos los campos son obligatorios";
    } else {

        // Hashear contraseña
        $password = password_hash($passwordInput, PASSWORD_DEFAULT);

        // Comprobar si ya existe usuario o email
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "El usuario o email ya existe";
        } else {

            // Insertar usuario (IMPORTANTE: password_hash)
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Error al registrar";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" />
<title>Registro | Pasca y Pesca</title>
<style>
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #3c6382;
    --sand: #f8f9fa;
    --dark: #2f3542;
    --white: #ffffff;
    --or
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #3c6382;
    --sand: #f8f9fa;
    --dark: #2f3542;
    --white: #ffffff;
    --orange: #f39c12;
}

/* HEADER */

/* MAIN */
main {
    flex: 1;
}

/* FORMULARIO */
.register-container {
    width: 400px;
    margin: 60px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.register-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: var(--primary-blue);
}

.register-container input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.register-container button {
    width: 100%;
    padding: 12px;
    background: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.register-container button:hover {
    background: var(--accent-blue);
}

.error {
    color: red;
    text-align: center;
    margin-bottom: 10px;
}

.login-link {
    text-align: center;
    margin-top: 10px;
}

.login-link a {
    color: var(--accent-blue);
    text-decoration: none;
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
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="carrito.php"><i class="fa-solid fa-cart-shopping"></i> Carrito</a></li>
        <li><a href="logout.php" class="btn btn-outline" style="padding:.42rem 1rem;font-size:.82rem;">Salir</a></li>
        <?php else: ?>
        <li><a href="login.php" class="btn btn-primary" style="padding:.42rem 1rem;font-size:.82rem;">Entrar</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main>
<div class="register-container">
    <h2>Registro</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>

    <div class="login-link">
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
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

</body>
</html>