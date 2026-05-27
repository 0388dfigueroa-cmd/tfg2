<?php
session_start();

if (isset($_POST['producto_id']) && isset($_POST['accion'])) {
    $producto_id = (int)$_POST['producto_id'];
    $accion = $_POST['accion'];
    
    if (isset($_SESSION['carrito'][$producto_id])) {
        if ($accion === 'aumentar') {
            $_SESSION['carrito'][$producto_id]['cantidad']++;
        } elseif ($accion === 'disminuir') {
            if ($_SESSION['carrito'][$producto_id]['cantidad'] > 1) {
                $_SESSION['carrito'][$producto_id]['cantidad']--;
            }
        }
    }
}

header('Location: carrito.php');
exit;
