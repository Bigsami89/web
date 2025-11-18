<?php
require_once "header.php";
require_once "conexion.php";

$esAdmin   = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
$esHuesped = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "huesped";

$sql = "SELECT h.id, h.nombre, h.descripcion_corta, h.precio_noche,
               h.disponibles, c.nombre AS categoria, h.imagen
        FROM habitaciones h
        INNER JOIN categorias c ON h.id_categoria = c.id
        ORDER BY c.nombre, h.nombre";
$resultado = $conexion->query($sql);
?>

<h1>Bienvenido al sistema de reservaciones de hoteles del aeropuerto</h1>
<p>Los usuarios no registrados solo pueden consultar la información. Para reservar necesitas iniciar sesión.</p>

<?php
$categoriaActual = "";
while ($fila = $resultado->fetch_assoc()):
    if ($fila["categoria"] !== $categoriaActual) {
        if ($categoriaActual !== "") {
            echo "</div>";
        }
        $categoriaActual = $fila["categoria"];
        echo "<h2>Categoría: " . htmlspecialchars($categoriaActual) . "</h2>";
        echo '<div class="lista-habitaciones">';
    }
?>
    <div class="habitacion">
        <img src="imagenes/<?php echo htmlspecialchars($fila["imagen"]); ?>" alt="Imagen habitación">
        <h3><?php echo htmlspecialchars($fila["nombre"]); ?></h3>
        <p><?php echo htmlspecialchars($fila["descripcion_corta"]); ?></p>
        <p><strong>Precio por noche:</strong> $<?php echo number_format($fila["precio_noche"], 2); ?></p>
        <p><strong>Disponibles:</strong> <?php echo (int)$fila["disponibles"]; ?></p>

        <button type="button"
                onclick="mostrarDetallesHabitacion(
                    '<?php echo htmlspecialchars($fila["nombre"], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($fila["descripcion_corta"], ENT_QUOTES); ?>',
                    '<?php echo number_format($fila["precio_noche"], 2); ?>'
                );">
            Ver detalles
        </button>

        <?php if ($esAdmin || $esHuesped): ?>
            <button type="button"
                    onclick="agregarAlCarrito(
                        <?php echo (int)$fila['id']; ?>,
                        '<?php echo htmlspecialchars($fila["nombre"], ENT_QUOTES); ?>',
                        <?php echo (float)$fila['precio_noche']; ?>
                    );">
                Agregar al carrito
            </button>
        <?php else: ?>
            <button type="button" disabled title="Inicia sesión para reservar">
                Inicia sesión para reservar
            </button>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<?php if ($categoriaActual !== ""): ?>
    </div>
<?php endif; ?>

<?php
$conexion->close();
require_once "footer.php";
?>
