<?php
require_once "header.php";
require_once "conexion.php";

$termino = trim($_GET["q"] ?? "");
?>
<h1>Búsqueda de habitaciones y servicios</h1>

<form action="buscar.php" method="get" onsubmit="return validarBusqueda();">
    <label>Buscar:
        <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($termino); ?>">
    </label>
    <button type="submit">Buscar</button>
    <a href="index.php"><button type="button">Cancelar</button></a>
</form>

<?php
if ($termino !== "") {
    $sql = "SELECT h.id, h.nombre, h.descripcion_corta, h.precio_noche,
                   h.disponibles, c.nombre AS categoria
            FROM habitaciones h
            INNER JOIN categorias c ON h.id_categoria = c.id
            WHERE h.nombre LIKE ? OR h.descripcion_corta LIKE ? OR c.nombre LIKE ?
            ORDER BY c.nombre, h.nombre";
    $like = "%$termino%";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo "<h2>Resultados de la búsqueda</h2>";
    echo "<p>Reporte de habitaciones encontradas.</p>";

    if ($resultado->num_rows > 0) {
        echo '<table class="tabla">';
        echo '<thead><tr>
                <th>Categoría</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Disponibles</th>
              </tr></thead><tbody>';
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fila["categoria"]) . "</td>";
            echo "<td>" . htmlspecialchars($fila["nombre"]) . "</td>";
            echo "<td>" . htmlspecialchars($fila["descripcion_corta"]) . "</td>";
            echo "<td>$" . number_format($fila["precio_noche"], 2) . "</td>";
            echo "<td>" . (int)$fila["disponibles"] . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No se encontraron registros.</p>";
    }

    $stmt->close();
}
$conexion->close();
require_once "footer.php";
?>
