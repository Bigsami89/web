<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$esAdmin   = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
$esHuesped = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "huesped";
?>
<nav class="nav">
    <div class="nav-logo">Hoteles Aeropuerto</div>
    <ul class="nav-menu">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="admin/buscar.php">Búsqueda</a></li>
        <li><a href="admin/carrito.php">Carrito</a></li>

        <?php if ($esAdmin): ?>
            <li class="submenu">
                <a href="#">Administración</a>
                <ul>
                    <li><a href="admin/habitaciones_listar.php">Habitaciones</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if (!$esAdmin && !$esHuesped): ?>
            <li><a href="registro.php">Registrarse</a></li>
        <?php endif; ?>

        <?php if ($esAdmin || $esHuesped): ?>
            <li><a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION["usuario"]); ?>)</a></li>
        <?php else: ?>
            <li><a href="login.php">Iniciar sesión</a></li>
        <?php endif; ?>
    </ul>
</nav>
