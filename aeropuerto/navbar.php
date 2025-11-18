<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config.inc.php";

$esAdmin   = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
$esHuesped = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "huesped";
$raiz = $GLOBALS["raiz_sitio"];
?>
<nav class="nav">
    <div class="nav-logo">Hoteles Aeropuerto</div>
    <ul class="nav-menu">
        <li><a href="<?php echo $raiz; ?>index.php">Inicio</a></li>
        <li><a href="<?php echo $raiz; ?>admin/buscar.php">Búsqueda</a></li>
        <li><a href="<?php echo $raiz; ?>admin/carrito.php">Carrito</a></li>

        <?php if ($esAdmin): ?>
            <li class="submenu">
                <a href="#">Administración</a>
                <ul>
                    <li><a href="<?php echo $raiz; ?>admin/habitaciones_listar.php">Habitaciones</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if (!$esAdmin && !$esHuesped): ?>
            <li><a href="<?php echo $raiz; ?>registro.php">Registrarse</a></li>
        <?php endif; ?>

        <?php if ($esAdmin || $esHuesped): ?>
            <li><a href="<?php echo $raiz; ?>logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION["usuario"]); ?>)</a></li>
        <?php else: ?>
            <li><a href="<?php echo $raiz; ?>login.php">Iniciar sesión</a></li>
        <?php endif; ?>
    </ul>
</nav>
