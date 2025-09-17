<?php 
session_start(); 

// Si no hay sesión → Login
if (!isset($_SESSION['usuario_id']) || !$_SESSION['usuario_id']) { 
    header('Location: views/login.php'); 
    exit; 
} 

// Si hay sesión → Redirigir según tipo
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin']) { 
    header('Location: views/admin.php'); 
    exit;
} else { 
    header('Location: views/usuario.php'); 
    exit;
} 
?>