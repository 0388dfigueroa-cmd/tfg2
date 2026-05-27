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

$userId = $_SESSION['user_id'] ?? null;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = [
        'y' => 'año',
        'm' => 'mes',
        'w' => 'semana',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    ];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'Hace ' . implode(', ', $string) : 'Justo ahora';
}

// Eliminar comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }
    
    $commentId = intval($_POST['comment_id']);
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    $stmt->close();
    
    if ($comment && $comment['user_id'] == $userId) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $stmt->close();
    }
}

// Eliminar post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    if (!$userId && !$isAdmin) {
        header('Location: login.php');
        exit;
    }
    
    $postId = intval($_POST['post_id']);
    $stmt = $conn->prepare("SELECT user_id, image FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    
    if ($post && ($post['user_id'] == $userId || $isAdmin)) {
        if ($post['image'] && file_exists($post['image'])) {
            unlink($post['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
        
        header('Location: foro.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_submit'])) {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }
    $content = trim($_POST['content']);
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;
        
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0755, true)) {
                error_log("No se pudo crear la carpeta uploads");
            }
        }

        if (is_dir($targetDir) && is_writable($targetDir)) {
            $fileName = basename($_FILES["image"]["name"]);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxSize = 5242880;

            if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] > 0 && $_FILES["image"]["size"] <= $maxSize) {
                $uniqueName = preg_replace('/[^a-z0-9]/i', '_', pathinfo($fileName, PATHINFO_FILENAME)) . "_" . uniqid() . "." . $fileType;
                $targetFilePath = $targetDir . $uniqueName;
                
                if (@move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    @chmod($targetFilePath, 0644);
                    $imagePath = "uploads/" . $uniqueName;
                } else {
                    error_log("Error al mover archivo: " . $_FILES["image"]["error"]);
                }
            }
        }
    }

    if ($content) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $content, $imagePath);
        $stmt->execute();
        $stmt->close();
        
        header('Location: foro.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }
    $commentContent = trim($_POST['comment_content']);
    $postId = intval($_POST['post_id']);

    if ($commentContent && $postId) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $postId, $userId, $commentContent);
        $stmt->execute();
        $stmt->close();
    }
}

$postsResult = $conn->query("
    SELECT p.*, u.username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pasca y Pesca | Foro</title>
<link rel="stylesheet" href="styles.css" />
<style>
.zona-hero { background:linear-gradient(135deg,rgba(7,45,73,.95) 0%,rgba(10,61,98,.88) 55%,rgba(21,101,192,.82) 100%); padding:4rem 0 3.5rem; text-align:center; color:#fff; }
.zona-hero .hero-badge { display:inline-block; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); color:#fff; font-size:.78rem; letter-spacing:.1em; text-transform:uppercase; font-weight:700; padding:.35rem 1rem; border-radius:99px; margin-bottom:1.2rem; backdrop-filter:blur(4px); }
.zona-hero h1 { font-family:'Montserrat',sans-serif; font-size:clamp(1.8rem,4vw,2.8rem); font-weight:800; margin-bottom:.6rem; text-shadow:0 2px 12px rgba(0,0,0,.3); }
.zona-hero p { font-size:1.05rem; opacity:.88; max-width:500px; margin:0 auto; }
main {
    flex: 1;
    width: 90%;
    max-width: 800px;
    margin: 30px auto 40px;
}
h1 { text-align:center; font-family:'Montserrat',sans-serif; font-size:1.9rem; color:var(--primary); margin-bottom:.2rem; }
.zona-hero h1 { color:#fff; }
p.subtitle { text-align:center; color:var(--muted); margin-bottom:1.5rem; }
.post-form { background:#fff; border-radius:16px; box-shadow:var(--shadow-sm); padding:1.5rem; margin-bottom:1.5rem; border:1px solid var(--border); }
textarea { width:100%; min-height:90px; border-radius:10px; border:1.5px solid var(--border); resize:vertical; padding:12px; font-size:.97rem; font-family:'Inter',sans-serif; color:var(--dark); background:#fff; transition:border-color .2s,box-shadow .2s; outline:none; }
textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(30,136,229,.12); }
.button-group { margin-top:1rem; display:flex; justify-content:space-between; gap:.8rem; flex-wrap:wrap; align-items:center; }
.btn { background:var(--accent); border:none; color:#fff; font-weight:600; padding:.6rem 1.4rem; border-radius:10px; cursor:pointer; transition:background .2s,transform .2s; font-family:'Inter',sans-serif; }
.btn:hover { background:var(--accent-dark); transform:translateY(-1px); }
.btn-small { padding:.35rem .9rem; font-size:.83rem; }
.btn-danger { background:var(--danger); }
.btn-danger:hover { background:#c0392b; }
.attach-label { background:rgba(30,136,229,.1); color:var(--accent); padding:.55rem 1rem; border-radius:10px; font-size:.92rem; cursor:pointer; user-select:none; transition:background .2s; }
.attach-label:hover { background:rgba(30,136,229,.18); }
input[type="file"] { display:none; }
.post { background:#fff; border-radius:16px; box-shadow:var(--shadow-sm); padding:1.5rem; margin-bottom:1.5rem; border:1px solid var(--border); }
.post:hover { box-shadow:var(--shadow-md); }
.post-header { display:flex; align-items:center; margin-bottom:1rem; justify-content:space-between; gap:1rem; }
.post-header-info { display:flex; align-items:center; gap:.85rem; }
.avatar { background:linear-gradient(135deg,var(--accent) 0%,var(--primary) 100%); color:#fff; border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.95rem; text-transform:uppercase; flex-shrink:0; }
.username { font-weight:700; color:var(--dark); }
.post-time { color:var(--muted); font-size:.82rem; }
.post-actions { display:flex; gap:.5rem; }
.post-content { font-size:.97rem; color:var(--dark); margin-bottom:.8rem; white-space:pre-wrap; }
.post-image { width:100%; max-height:400px; object-fit:cover; border-radius:12px; margin-top:1rem; }
.comments { margin-top:1.2rem; padding:1.2rem; background:var(--bg); border-radius:12px; border:1px solid var(--border); }
.comment { background:#fff; margin-bottom:.8rem; padding:1rem; border-radius:10px; font-size:.93rem; border:1px solid var(--border); }
.comment-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem; }
.comment-user-time { display:flex; align-items:center; gap:.6rem; }
.comment-username { font-weight:600; color:var(--primary); }
.comment-time { font-size:.78rem; color:var(--muted); }
.comment-actions { display:flex; gap:.4rem; }
.btn-responder { background:none; border:none; color:var(--accent); font-size:.82rem; cursor:pointer; padding:0; text-decoration:underline; font-weight:500; }
.btn-delete-comment { background:none; border:none; color:var(--muted); font-size:1.1rem; cursor:pointer; padding:4px 7px; border-radius:7px; transition:all .2s; }
.btn-delete-comment:hover { background:rgba(231,76,60,.13); color:var(--danger); }
.image-preview-container { margin-top:.8rem; background:#f8fbff; padding:.8rem 1rem; border-radius:10px; border:1px solid rgba(30,136,229,.15); display:flex; align-items:center; justify-content:space-between; gap:.7rem; }
.selected-file-info { display:flex; align-items:center; gap:.7rem; flex-wrap:wrap; }
.file-name { font-weight:600; color:var(--dark); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px; }
.view-image-btn { background:var(--accent); color:#fff; padding:6px 12px; border-radius:8px; font-size:.85rem; border:none; cursor:pointer; transition:background .2s; }
.view-image-btn:hover { background:var(--accent-dark); }
.image-preview-inline { display:block; max-width:100%; max-height:260px; margin-top:.8rem; border-radius:12px; object-fit:contain; border:1px solid var(--border); }
.clear-image-btn { background:var(--danger); color:#fff; border:none; padding:6px 14px; border-radius:8px; cursor:pointer; font-size:.85rem; transition:background .2s; }
.clear-image-btn:hover { background:#c0392b; }
.comment-text { white-space:pre-wrap; }
.comment-form { display:flex; flex-direction:column; margin-top:1rem; gap:.7rem; }
.comment-form textarea { min-height:55px; }
.comment-form button { align-self:flex-end; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const userLoggedIn = <?= (isset($_SESSION['user_id']) || isset($_SESSION['is_admin'])) ? 'true' : 'false' ?>;

    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let previewContainer = document.getElementById('image-preview-container');
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.id = 'image-preview-container';
                        previewContainer.className = 'image-preview-container';
                        imageInput.closest('.post-form').appendChild(previewContainer);
                    }
                    previewContainer.innerHTML = `
                        <div class="selected-file-info">
                            <span class="file-name">${file.name}</span>
                            <button type="button" class="view-image-btn" data-preview-src="${e.target.result}" onclick="mostrarImagenPreview(this)">Ver imagen</button>
                        </div>
                        <button type="button" class="clear-image-btn" onclick="limpiarImagen(event)">Quitar imagen</button>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                const previewContainer = document.getElementById('image-preview-container');
                if (previewContainer) previewContainer.remove();
            }
        });
    }

    if (!userLoggedIn) {
        const attachLabel = document.querySelector('.attach-label');
        if (attachLabel) {
            attachLabel.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = 'login.php';
            });
        }

        const postForm = document.querySelector('.post-form');
        if (postForm) {
            postForm.addEventListener('submit', (e) => {
                alert('Debes iniciar sesión para publicar.');
                e.preventDefault();
                window.location.href = 'login.php';
            });
        }

        document.querySelectorAll('.comment-form').forEach(form => {
            form.addEventListener('submit', e => {
                alert('Debes iniciar sesión para comentar.');
                e.preventDefault();
                window.location.href = 'login.php';
            });
        });
    }
});

function limpiarImagen(event) {
    event.preventDefault();
    document.getElementById('image').value = '';
    const previewContainer = document.getElementById('image-preview-container');
    if (previewContainer) {
        previewContainer.remove();
    }
}

function mostrarImagenPreview(button) {
    const previewSrc = button.dataset.previewSrc;
    const container = button.closest('.image-preview-container');
    if (!previewSrc || !container) return;

    let previewImage = container.querySelector('.image-preview-inline');
    if (!previewImage) {
        previewImage = document.createElement('img');
        previewImage.className = 'image-preview-inline';
        previewImage.src = previewSrc;
        previewImage.alt = 'Vista previa';
        container.appendChild(previewImage);
        button.textContent = 'Ocultar imagen';
    } else {
        if (previewImage.style.display === 'none' || previewImage.style.display === '') {
            previewImage.style.display = 'block';
            button.textContent = 'Ocultar imagen';
        } else {
            previewImage.style.display = 'none';
            button.textContent = 'Ver imagen';
        }
    }
}

function confirmarEliminarPost(postId) {
    if (confirm('¿Estás seguro de que deseas borrar esta publicación?')) {
        document.getElementById('delete-post-form-' + postId).submit();
    }
}

function responderA(usuario) {
    const textareas = document.querySelectorAll('.comment-form textarea');
    if (textareas.length > 0) {
        const textarea = textareas[textareas.length - 1];
        textarea.focus();
        if (!textarea.value.includes('@' + usuario)) {
            textarea.value = '@' + usuario + ' ' + textarea.value;
        }
    }
}
</script>
</head>
<body>

<header>
<div class="container">
<nav>
  <a href="index.php" class="logo"><i class="fa-solid fa-fish"></i> Pasca y Pesca</a>
  <ul class="nav-links">
    <li><a href="index.php">Inicio</a></li>
    <li><a href="tienda.php">Tienda</a></li>
    <li><a href="foro.php" class="active">Foro</a></li>
    <li><a href="tecnicas.php">Técnicas</a></li>
    <li><a href="zonasCalientes.php">Zonas</a></li>
        <li><a href="contacto.php">Contacto</a></li>
    <?php if ($isAdmin): ?>
      <li><a href="admin.php">Panel Admin</a></li>
    <?php elseif ($userId): ?>
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
    <span class="hero-badge"><i class="fa-solid fa-comments"></i> &nbsp;Comunidad de pescadores</span>
    <h1>Comunidad Pasca y Pesca</h1>
    <p>Comparte tus capturas y consejos con otros socios</p>
  </div>
</div>

<main>

    <!-- Formulario nueva publicación -->
    <form class="post-form" method="post" enctype="multipart/form-data" autocomplete="off">
        <textarea name="content" placeholder="¿Qué tal la pesca hoy? Comparte tu captura..." required></textarea>
        <div class="button-group">
            <label class="attach-label" for="image">📷 Adjuntar Captura</label>
            <input type="file" name="image" id="image" accept="image/*">
            <button type="submit" name="post_submit" class="btn">Publicar</button>
        </div>
    </form>

    <?php while ($post = $postsResult->fetch_assoc()):
        $initial = strtoupper($post['username'][0]);
        $stmtComments = $conn->prepare("
            SELECT c.*, u.username FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? ORDER BY c.created_at ASC
        ");
        $stmtComments->bind_param("i", $post['id']);
        $stmtComments->execute();
        $commentsResult = $stmtComments->get_result();
    ?>
    <div class="post">
        <div class="post-header">
            <div class="post-header-info">
                <div class="avatar"><?= htmlspecialchars($initial) ?></div>
                <div>
                    <div class="username"><?= htmlspecialchars($post['username']) ?></div>
                    <div class="post-time"><?= time_elapsed_string($post['created_at']) ?></div>
                </div>
            </div>
            <?php if (($userId && $userId === $post['user_id']) || $isAdmin): ?>
                <div class="post-actions">
                    <form method="post" id="delete-post-form-<?= $post['id'] ?>">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="button" class="btn-delete-comment" title="Eliminar publicación" onclick="confirmarEliminarPost(<?= $post['id'] ?>)">🗑️</button>
                        <input type="hidden" name="delete_post" value="1">
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
        <?php if ($post['image']): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Captura" class="post-image">
        <?php endif; ?>

        <div class="comments">
            <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-header">
                        <div class="comment-user-time">
                            <div class="comment-username"><?= htmlspecialchars($comment['username']) ?></div>
                            <div class="comment-time"><?= time_elapsed_string($comment['created_at']) ?></div>
                        </div>
                        <div class="comment-actions">
                            <?php if ($userId && $userId === $comment['user_id']): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" name="delete_comment" class="btn-delete-comment" title="Eliminar comentario" onclick="return confirm('¿Estás seguro de que deseas borrar este comentario?')">🗑️</button>
                                </form>
                            <?php elseif ($userId && $userId !== $comment['user_id']): ?>
                                <button type="button" class="btn-responder" onclick="responderA('<?= htmlspecialchars($comment['username']) ?>')">Responder</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="comment-text" id="comment-text-<?= $comment['id'] ?>"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                </div>
            <?php endwhile; ?>

            <!-- Formulario comentar -->
            <form method="post" class="comment-form" autocomplete="off">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <textarea name="comment_content" placeholder="Escribe un comentario..." required></textarea>
                <button type="submit" name="comment_submit" class="btn">Comentar</button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
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