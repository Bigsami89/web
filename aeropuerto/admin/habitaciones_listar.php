<?php
require_once __DIR__ . "/../seguridad_admin.php";
require_once __DIR__ . "/../config.inc.php";
require_once __DIR__ . "/../conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar habitaciones</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS["raiz_sitio"]; ?>css/estilos.css">
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/validacion.js"></script>
</head>
<body>
<div class="contenedor">
    <h1>Catálogo de habitaciones</h1>
    
    <div>
        <a href="habitaciones_form.php"><button>+ Agregar habitación</button></a>
        <a href="<?php echo $GLOBALS["raiz_sitio"]; ?>index.php"><button type="button">Volver al inicio</button></a>
    </div>

    <?php
    // Obtener estadísticas
    $stats = $conexion->query("
        SELECT 
            COUNT(*) as total_habitaciones,
            SUM(disponibles) as total_disponibles,
            COUNT(DISTINCT id_categoria) as total_categorias
        FROM habitaciones
    ")->fetch_assoc();
    ?>
    
    <div class="resumen">
        <p><strong>Resumen del catálogo:</strong></p>
        <p>Total de tipos de habitación: <?php echo (int)$stats["total_habitaciones"]; ?></p>
        <p>Total de habitaciones disponibles: <?php echo (int)$stats["total_disponibles"]; ?></p>
        <p>Categorías: <?php echo (int)$stats["total_categorias"]; ?></p>
    </div>

    <?php
    // Verificar si la tabla de imágenes múltiples existe
    $tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;
    
    // Obtener categorías con habitaciones
    $sqlCategorias = "
        SELECT DISTINCT c.id, c.nombre
        FROM categorias c
        INNER JOIN habitaciones h ON h.id_categoria = c.id
        ORDER BY c.nombre
    ";
    $categorias = $conexion->query($sqlCategorias);
    
    while ($categoria = $categorias->fetch_assoc()):
    ?>
        <div class="categoria-grupo">
            <h2 class="categoria-titulo">
                Categoría: <?php echo htmlspecialchars($categoria["nombre"]); ?>
            </h2>
            <div class="categoria-contenido">
                <?php
                // Obtener habitaciones de esta categoría
                $stmt = $conexion->prepare("
                    SELECT h.id, h.nombre, h.descripcion_corta, h.precio_noche, h.disponibles, h.imagen AS imagen_original
                    FROM habitaciones h
                    WHERE h.id_categoria = ?
                    ORDER BY h.nombre
                ");
                $stmt->bind_param("i", $categoria["id"]);
                $stmt->execute();
                $habitaciones = $stmt->get_result();
                
                while ($hab = $habitaciones->fetch_assoc()):
                    // Obtener imágenes de esta habitación
                    $listaImagenes = [];
                    if ($tablaImagenesExiste) {
                        $stmtImg = $conexion->prepare("SELECT nombre_archivo FROM habitacion_imagenes WHERE id_habitacion = ?");
                        $stmtImg->bind_param("i", $hab["id"]);
                        $stmtImg->execute();
                        $imagenes = $stmtImg->get_result();
                        while ($img = $imagenes->fetch_assoc()) {
                            $listaImagenes[] = $img["nombre_archivo"];
                        }
                        $stmtImg->close();
                    }
                    // Si no hay en la nueva tabla, usar la original
                    if (empty($listaImagenes) && !empty($hab["imagen_original"])) {
                        $listaImagenes[] = $hab["imagen_original"];
                    }
                ?>
                    <div class="habitacion-card">
                        <div class="habitacion-imagenes habitacion-carrusel">
                            <?php if (!empty($listaImagenes)): ?>
                                <div class="carrusel" id="carrusel-<?php echo $hab["id"]; ?>">
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
                                        <button type="button" class="carrusel-btn carrusel-btn-prev" onclick="moverCarrusel(<?php echo $hab["id"]; ?>, -1)">&#10094;</button>
                                        <button type="button" class="carrusel-btn carrusel-btn-next" onclick="moverCarrusel(<?php echo $hab["id"]; ?>, 1)">&#10095;</button>
                                        <div class="carrusel-indicadores">
                                            <?php for ($i = 0; $i < count($listaImagenes); $i++): ?>
                                                <span class="carrusel-indicador <?php echo $i === 0 ? 'activo' : ''; ?>" 
                                                      onclick="irASlide(<?php echo $hab["id"]; ?>, <?php echo $i; ?>)"></span>
                                            <?php endfor; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="sin-imagen">Sin imágenes</div>
                            <?php endif; ?>
                        </div>
                        <div class="habitacion-info">
                            <h3><?php echo htmlspecialchars($hab["nombre"]); ?></h3>
                            <p><?php echo htmlspecialchars($hab["descripcion_corta"]); ?></p>
                            <p><strong>Precio por noche:</strong> $<?php echo number_format($hab["precio_noche"], 2); ?></p>
                            <p>
                                <strong>Disponibles:</strong> 
                                <?php if ($hab["disponibles"] > 0): ?>
                                    <span class="badge badge-disponible"><?php echo (int)$hab["disponibles"]; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-agotado">Agotado</span>
                                <?php endif; ?>
                            </p>
                            <p><small>ID: <?php echo (int)$hab["id"]; ?></small></p>
                        </div>
                        <div class="habitacion-acciones">
                            <a href="habitaciones_form.php?id=<?php echo (int)$hab["id"]; ?>">
                                <button>Editar</button>
                            </a>
                            <button type="button" class="btn-danger" onclick="confirmarEliminacion(<?php echo (int)$hab['id']; ?>, '<?php echo htmlspecialchars($hab["nombre"], ENT_QUOTES); ?>');">
                                Eliminar
                            </button>
                        </div>
                    </div>
                <?php 
                endwhile; 
                $stmt->close();
                ?>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if ($categorias->num_rows === 0): ?>
        <p>No hay habitaciones registradas. <a href="habitaciones_form.php">Agregar primera habitación</a></p>
    <?php endif; ?>
</div>

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

function confirmarEliminacion(id, nombre) {
    if (confirm("¿Estás seguro de eliminar la habitación '" + nombre + "'?\n\nEsta acción también eliminará todas sus imágenes.")) {
        window.location.href = "habitaciones_eliminar.php?id=" + id;
    }
}
</script>
</body>
</html>
<?php
$conexion->close();
?>