<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.inc.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoteles Aeropuerto</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS["raiz_sitio"]; ?>css/estilos.css">
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/validaciones.js"></script>
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/carrito.js"></script>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="contenedor">
