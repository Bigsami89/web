<?php
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/conexion.php";

$esAdmin   = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
$esHuesped = isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "huesped";

// Verificar si la tabla de imágenes múltiples existe
$tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;

$sql = "SELECT h.id, h.nombre, h.descripcion_corta, h.precio_noche,
               h.disponibles, c.nombre AS categoria, h.imagen AS imagen_original
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
    
    // Obtener todas las imágenes de esta habitación
    $listaImagenes = [];
    if ($tablaImagenesExiste) {
        $stmtImg = $conexion->prepare("SELECT nombre_archivo FROM habitacion_imagenes WHERE id_habitacion = ?");
        $stmtImg->bind_param("i", $fila["id"]);
        $stmtImg->execute();
        $imagenes = $stmtImg->get_result();
        while ($img = $imagenes->fetch_assoc()) {
            $listaImagenes[] = $img["nombre_archivo"];
        }
        $stmtImg->close();
    }
    // Si no hay en la nueva tabla, usar la original
    if (empty($listaImagenes) && !empty($fila["imagen_original"])) {
        $listaImagenes[] = $fila["imagen_original"];
    }
?>
    <div class="habitacion">
        <div class="habitacion-carrusel">
            <?php if (!empty($listaImagenes)): ?>
                <div class="carrusel" id="carrusel-<?php echo $fila["id"]; ?>">
                    <div class="carrusel-contenedor">
                        <?php foreach ($listaImagenes as $img): ?>
                            <div class="carrusel-slide">
                                <img src="<?php echo $GLOBALS["raiz_sitio"]; ?>imagenes/<?php echo htmlspecialchars($img); ?>" 
                                     alt="Imagen habitación"
                                     onclick="abrirModal('<?php echo $GLOBALS["raiz_sitio"]; ?>imagenes/<?php echo htmlspecialchars($img); ?>')">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($listaImagenes) > 1): ?>
                        <button type="button" class="carrusel-btn carrusel-btn-prev" onclick="moverCarrusel(<?php echo $fila["id"]; ?>, -1)">&#10094;</button>
                        <button type="button" class="carrusel-btn carrusel-btn-next" onclick="moverCarrusel(<?php echo $fila["id"]; ?>, 1)">&#10095;</button>
                        <div class="carrusel-indicadores">
                            <?php for ($i = 0; $i < count($listaImagenes); $i++): ?>
                                <span class="carrusel-indicador <?php echo $i === 0 ? 'activo' : ''; ?>" 
                                      onclick="irASlide(<?php echo $fila["id"]; ?>, <?php echo $i; ?>)"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="sin-imagen">Sin imagen</div>
            <?php endif; ?>
        </div>
        
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

<!-- Modal para ver imagen grande -->
<div class="modal-imagen" id="modalImagen" onclick="cerrarModal()">
    <span class="modal-cerrar">&times;</span>
    <img id="modalImagenSrc" src="" alt="Imagen ampliada">
</div>

<script>
// Estado de los carruseles
const carruselEstados = {};

function moverCarrusel(id, direccion) {
    const carrusel = document.getElementById('carrusel-' + id);
    if (!carrusel) return;
    
    const contenedor = carrusel.querySelector('.carrusel-contenedor');
    const slides = carrusel.querySelectorAll('.carrusel-slide');
    const indicadores = carrusel.querySelectorAll('.carrusel-indicador');
    
    if (!carruselEstados[id]) {
        carruselEstados[id] = 0;
    }
    
    carruselEstados[id] += direccion;
    
    // Circular
    if (carruselEstados[id] < 0) {
        carruselEstados[id] = slides.length - 1;
    } else if (carruselEstados[id] >= slides.length) {
        carruselEstados[id] = 0;
    }
    
    contenedor.style.transform = 'translateX(-' + (carruselEstados[id] * 100) + '%)';
    
    // Actualizar indicadores
    indicadores.forEach((ind, i) => {
        ind.classList.toggle('activo', i === carruselEstados[id]);
    });
}

function irASlide(id, indice) {
    const carrusel = document.getElementById('carrusel-' + id);
    if (!carrusel) return;
    
    const contenedor = carrusel.querySelector('.carrusel-contenedor');
    const indicadores = carrusel.querySelectorAll('.carrusel-indicador');
    
    carruselEstados[id] = indice;
    contenedor.style.transform = 'translateX(-' + (indice * 100) + '%)';
    
    // Actualizar indicadores
    indicadores.forEach((ind, i) => {
        ind.classList.toggle('activo', i === indice);
    });
}

function abrirModal(src) {
    const modal = document.getElementById('modalImagen');
    const img = document.getElementById('modalImagenSrc');
    img.src = src;
    modal.classList.add('activo');
}

function cerrarModal() {
    const modal = document.getElementById('modalImagen');
    modal.classList.remove('activo');
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModal();
    }
});
</script>

<?php
$conexion->close();
require_once __DIR__ . "/footer.php";
?>