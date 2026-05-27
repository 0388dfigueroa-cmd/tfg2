<?php
session_start();
// Destruye toda la sesión
session_unset();
session_destroy();

// Redirige al login
header('Location: login.php');
exit;
?>