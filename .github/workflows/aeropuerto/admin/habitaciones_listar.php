<?php
require_once "../seguridad_admin.php";
require_once "../config.inc.php";
require_once "../conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar habitaciones</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS["raiz_sitio"]; ?>css/estilos.css">
    <script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/validaciones.js"></script>
</head>
<body>
<div class="contenedor">
    <h1>Catálogo de habitaciones</h1>
    <a href="habitaciones_form.php"><button>Agregar habitación</button></a>
    <a href="../index.php"><button>Volver al inicio</button></a>

    <table class="tabla">
        <thead>
        <tr>
            <th>ID</th>
            <th>Categoría</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Disponibles</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT h.id, h.nombre, h.precio_noche, h.disponibles,
                       h.imagen, c.nombre AS categoria
                FROM habitaciones h
                INNER JOIN categorias c ON h.id_categoria = c.id
                ORDER BY c.nombre, h.nombre";
        $resultado = $conexion->query($sql);
        while ($fila = $resultado->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo (int)$fila["id"]; ?></td>
                <td><?php echo htmlspecialchars($fila["categoria"]); ?></td>
                <td><?php echo htmlspecialchars($fila["nombre"]); ?></td>
                <td>$<?php echo number_format($fila["precio_noche"], 2); ?></td>
                <td><?php echo (int)$fila["disponibles"]; ?></td>
                <td><?php echo htmlspecialchars($fila["imagen"]); ?></td>
                <td>
                    <a href="habitaciones_form.php?id=<?php echo (int)$fila["id"]; ?>">
                        <button>Editar</button>
                    </a>
                    <button onclick="confirmarEliminacion(<?php echo (int)$fila['id']; ?>);">
                        Eliminar
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm("¿Estás seguro de eliminar esta habitación?")) {
        window.location.href = "habitaciones_eliminar.php?id=" + id;
    }
}
</script>
</body>
</html>
<?php
$conexion->close();
?>
