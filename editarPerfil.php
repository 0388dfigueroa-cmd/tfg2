<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


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

$userId = (int)$_SESSION['user_id'];
$error = '';
$success = '';
$username = '';
$email = '';
$aficiones = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $aficiones = trim($_POST['aficiones'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '') {
        $error = 'Usuario y correo electrónico son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un correo electrónico válido.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?");
        $check->bind_param("ssi", $username, $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'El usuario o correo ya está en uso por otra cuenta.';
        } else {
            if ($newPassword !== '') {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, aficiones = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $passwordHash, $aficiones, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, aficiones = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $aficiones, $userId);
            }

            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                $success = 'Perfil actualizado correctamente.';
            } else {
                $error = 'No se pudo actualizar el perfil. Intenta de nuevo más tarde.';
            }

            $stmt->close();
        }

        $check->close();
    }
}

$stmt = $conn->prepare("SELECT username, email, aficiones FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($dbUsername, $dbEmail, $dbAficiones);
if ($stmt->fetch()) {
    if ($username === '') {
        $username = $dbUsername;
    }
    if ($email === '') {
        $email = $dbEmail;
    }
    if ($aficiones === '') {
        $aficiones = $dbAficiones;
    }
} else {
    session_destroy();
    header('Location: login.php');
    exit;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" />
<title>Editar perfil | Pasca y Pesca</title>
<style>
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #3c6382;
    --sand: #f8f9fa;
    --dark: #2f3542;
    --white: #ffffff;
    --
:root {
    --primary-blue: #0a3d62;
    --accent-blue: #3c6382;
    --sand: #f8f9fa;
    --dark: #2f3542;
    --white: #ffffff;
    --success-green: #16a34a;
    --danger-red: #dc2626;
}
.main-content{flex:1;padding:40px 0;}
.profile-card{
    background:#ffffff;
    border-radius:18px;
    padding:32px;
    box-shadow:0 20px 50px rgba(0,0,0,0.08);
    max-width:760px;
    margin:0 auto;
}
.profile-card h1{
    font-size:2rem;
    margin-bottom:10px;
    color: var(--primary-blue);
}
.profile-card p{
    color:#4b5563;
    margin-bottom:28px;
}
.form-group{margin-bottom:18px;}
.form-group label{display:block;margin-bottom:8px;font-weight:700;color:#1f2937;}
.form-group input{
    width:100%;padding:14px 16px;border:1px solid #d1d5db;border-radius:12px;
    font-size:1rem;background:#fafafa;color:#111827;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-group input:focus{
    outline:none;
    border-color: var(--accent-blue);
    box-shadow:0 0 0 4px rgba(47,128,237,0.12);
    background:white;
}
.actions{
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    flex-wrap:wrap;
}
.btn-primary{
    padding:14px 24px;
    background:var(--primary-blue);
    color:white;
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:700;
}
.btn-primary:hover{background:#37679b;}
.btn-secondary{
    padding:14px 24px;
    background:#e5e7eb;
    color:#1f2937;
    border:none;
    border-radius:12px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}
.message {
    padding:16px 20px;
    border-radius:14px;
    margin-bottom:24px;
    font-weight:500;
}
.message.success{background:rgba(22,163,74,0.12);color:var(--success-green);border:1px solid rgba(22,163,74,0.2);}
.message.error{background:rgba(220,38,38,0.12);color:var(--danger-red);border:1px solid rgba(220,38,38,0.2);}
}
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
        <li><a href="contacto.php">Contacto</a></li>
        <li><a href="carrito.php"><i class="fa-solid fa-cart-shopping"></i> Carrito</a></li>
        <li><a class="profile-link active" href="editarPerfil.php" title="Mi perfil"><i class="fa-solid fa-user"></i></a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="main-content">
    <div class="container">
        <div class="profile-card">
            <h1>Editar perfil</h1>
            <p>Actualiza tu información con seguridad. Mantén tu cuenta al día cambiando nombre de usuario, correo electrónico o contraseña.</p>

            <?php if (!empty($success)): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" type="text" value="<?= htmlspecialchars($username) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva contraseña</label>
                    <input id="new_password" name="new_password" type="password" placeholder="Dejar en blanco para mantener la contraseña actual">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input id="confirm_password" name="confirm_password" type="password" placeholder="Repite la nueva contraseña">
                </div>
                <div class="form-group">
                    <label for="aficiones">Aficiones</label>
                    <input id="aficiones" name="aficiones" type="text" value="<?= htmlspecialchars($aficiones) ?>" placeholder="Pesca, senderismo, fotografía...">
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="logout.php" class="btn-secondary">Cerrar sesión</a>
                    <a href="foro.php" class="btn-secondary">Volver al foro</a>
                </div>
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
