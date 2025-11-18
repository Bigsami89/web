<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config.inc.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoteles Aeropuerto</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS["raiz_sitio"]; ?>css/estilos.css">
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/validacion.js"></script>
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/carrito.js"></script>
</head>
<body>
<?php include __DIR__ . "/navbar.php"; ?>
<div class="contenedor">
