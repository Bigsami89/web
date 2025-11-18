<?php
require_once "../seguridad_admin.php";
require_once "../config.inc.php";
require_once "../conexion.php";

$id         = $_GET["id"] ?? "";
$editando   = $id !== "";
$mensaje    = "";
$error      = "";
$datos      = [
    "id_categoria"      => "",
    "nombre"            => "",
    "descripcion_corta" => "",
    "descripcion_larga" => "",
    "precio_noche"      => "",
    "disponibles"       => "",
    "imagen"            => ""
];

if ($editando) {
    $stmt = $conexion->prepare(
        "SELECT id_categoria, nombre, descripcion_corta, descripcion_larga,
                precio_noche, disponibles, imagen
         FROM habitaciones
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $datos = $fila;
    } else {
        $error = "Habitación no encontrada.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_categoria      = (int)($_POST["id_categoria"] ?? 0);
    $nombre            = trim($_POST["nombre"] ?? "");
    $descripcion_corta = trim($_POST["descripcion_corta"] ?? "");
    $descripcion_larga = trim($_POST["descripcion_larga"] ?? "");
    $precio_noche      = (float)($_POST["precio_noche"] ?? 0);
    $disponibles       = (int)($_POST["disponibles"] ?? 0);

    // Imagen (simple: solo guardamos el nombre del archivo recibido)
    $imagen = $datos["imagen"];
    if (!empty($_FILES["imagen"]["name"])) {
        $imagen = basename($_FILES["imagen"]["name"]);
        $destino = "../imagenes/" . $imagen;
        move_uploaded_file($_FILES["imagen"]["tmp_name"], $destino);
    }

    if ($nombre === "" || $descripcion_corta === "" || $precio_noche <= 0) {
        $error = "Nombre, descripción corta y precio son obligatorios.";
    } else {
        if ($editando) {
            $stmt = $conexion->prepare(
                "UPDATE habitaciones
                 SET id_categoria = ?, nombre = ?, descripcion_corta = ?,
                     descripcion_larga = ?, precio_noche = ?, disponibles = ?,
                     imagen = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "isssdisi",
                $id_categoria,
                $nombre,
                $descripcion_corta,
                $descripcion_larga,
                $precio_noche,
                $disponibles,
                $imagen,
                $id
            );
        } else {
            $stmt = $conexion->prepare(
                "INSERT INTO habitaciones
                    (id_categoria, nombre, descripcion_corta, descripcion_larga,
                     precio_noche, disponibles, imagen)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "isssdis",
                $id_categoria,
                $nombre,
                $descripcion_corta,
                $descripcion_larga,
                $precio_noche,
                $disponibles,
                $imagen
            );
        }

        if ($stmt->execute()) {
            header("Location: habitaciones_listar.php");
            exit;
        } else {
            $error = "Error al guardar la habitación.";
        }

        $stmt->close();
    }
}

// Cargar categorías
$categorias = [];
$resCat = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre");
while ($row = $resCat->fetch_assoc()) {
    $categorias[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $editando ? "Editar" : "Agregar"; ?> habitación</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS["raiz_sitio"]; ?>css/estilos.css">
</head>
<body>
<div class="contenedor">
    <h1><?php echo $editando ? "Editar" : "Agregar"; ?> habitación</h1>

    <?php if ($error !== ""): ?>
        <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" onsubmit="return validarHabitacion();">
        <label>Categoría:
            <select name="id_categoria" id="id_categoria">
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo (int)$cat["id"]; ?>"
                        <?php echo ($cat["id"] == $datos["id_categoria"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($cat["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Nombre:
            <input type="text" name="nombre" id="nombre_hab"
                   value="<?php echo htmlspecialchars($datos["nombre"]); ?>">
        </label>

        <label>Descripción corta:
            <textarea name="descripcion_corta" id="descripcion_corta"><?php
                echo htmlspecialchars($datos["descripcion_corta"]);
            ?></textarea>
        </label>

        <label>Descripción larga:
            <textarea name="descripcion_larga" id="descripcion_larga"><?php
                echo htmlspecialchars($datos["descripcion_larga"]);
            ?></textarea>
        </label>

        <label>Precio por noche:
            <input type="number" step="0.01" name="precio_noche" id="precio_noche"
                   value="<?php echo htmlspecialchars($datos["precio_noche"]); ?>">
        </label>

        <label>Número de habitaciones disponibles:
            <input type="number" name="disponibles" id="disponibles"
                   value="<?php echo htmlspecialchars($datos["disponibles"]); ?>">
        </label>

        <label>Imagen:
            <input type="file" name="imagen" id="imagen">
            <?php if ($datos["imagen"] !== ""): ?>
                <br>Actual: <?php echo htmlspecialchars($datos["imagen"]); ?>
            <?php endif; ?>
        </label>

        <button type="submit">Guardar</button>
        <a href="habitaciones_listar.php"><button type="button">Cancelar</button></a>
    </form>
</div>
</body>
</html>
<?php
$conexion->close();
?>
