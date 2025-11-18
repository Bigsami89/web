<?php
require_once __DIR__ . "/../seguridad_admin.php";
require_once __DIR__ . "/../config.inc.php";
require_once __DIR__ . "/../conexion.php";

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
    "disponibles"       => ""
];
$imagenes_actuales = [];

if ($editando) {
    // Cargar datos de la habitación
    $stmt = $conexion->prepare(
        "SELECT id_categoria, nombre, descripcion_corta, descripcion_larga,
                precio_noche, disponibles
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
    
    // Cargar imágenes existentes (verificar si la tabla existe)
    $tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;
    if ($tablaImagenesExiste) {
        $stmt = $conexion->prepare("SELECT id, nombre_archivo FROM habitacion_imagenes WHERE id_habitacion = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($img = $resultado->fetch_assoc()) {
            $imagenes_actuales[] = $img;
        }
        $stmt->close();
    }
}

// Procesar eliminación de imagen individual
if (isset($_GET["eliminar_imagen"]) && $editando) {
    $tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;
    if ($tablaImagenesExiste) {
        $id_imagen = (int)$_GET["eliminar_imagen"];
        
        // Obtener nombre del archivo para eliminarlo físicamente
        $stmt = $conexion->prepare("SELECT nombre_archivo FROM habitacion_imagenes WHERE id = ? AND id_habitacion = ?");
        $stmt->bind_param("ii", $id_imagen, $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($img = $resultado->fetch_assoc()) {
            $ruta_imagen = __DIR__ . "/../imagenes/" . $img["nombre_archivo"];
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            $stmt->close();
            
            // Eliminar de la base de datos
            $stmt = $conexion->prepare("DELETE FROM habitacion_imagenes WHERE id = ?");
            $stmt->bind_param("i", $id_imagen);
            $stmt->execute();
            $stmt->close();
            
            header("Location: habitaciones_form.php?id=" . $id);
            exit;
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_categoria      = (int)($_POST["id_categoria"] ?? 0);
    $nombre            = trim($_POST["nombre"] ?? "");
    $descripcion_corta = trim($_POST["descripcion_corta"] ?? "");
    $descripcion_larga = trim($_POST["descripcion_larga"] ?? "");
    $precio_noche      = (float)($_POST["precio_noche"] ?? 0);
    $disponibles       = (int)($_POST["disponibles"] ?? 0);

    if ($nombre === "" || $descripcion_corta === "" || $precio_noche <= 0) {
        $error = "Nombre, descripción corta y precio son obligatorios.";
    } else {
        // Guardar o actualizar la habitación
        if ($editando) {
            $stmt = $conexion->prepare(
                "UPDATE habitaciones
                 SET id_categoria = ?, nombre = ?, descripcion_corta = ?,
                     descripcion_larga = ?, precio_noche = ?, disponibles = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "isssdii",
                $id_categoria,
                $nombre,
                $descripcion_corta,
                $descripcion_larga,
                $precio_noche,
                $disponibles,
                $id
            );
            $stmt->execute();
            $stmt->close();
            $id_habitacion = $id;
        } else {
            $stmt = $conexion->prepare(
                "INSERT INTO habitaciones
                    (id_categoria, nombre, descripcion_corta, descripcion_larga,
                     precio_noche, disponibles, imagen)
                 VALUES (?, ?, ?, ?, ?, ?, '')"
            );
            $stmt->bind_param(
                "isssdi",
                $id_categoria,
                $nombre,
                $descripcion_corta,
                $descripcion_larga,
                $precio_noche,
                $disponibles
            );
            $stmt->execute();
            $id_habitacion = $stmt->insert_id;
            $stmt->close();
        }

        // Procesar múltiples imágenes
        $tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;
        if ($tablaImagenesExiste && !empty($_FILES["imagenes"]["name"][0])) {
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $directorio_imagenes = __DIR__ . "/../imagenes/";
            
            // Crear directorio si no existe
            if (!is_dir($directorio_imagenes)) {
                mkdir($directorio_imagenes, 0755, true);
            }
            
            $total_imagenes = count($_FILES["imagenes"]["name"]);
            
            for ($i = 0; $i < $total_imagenes; $i++) {
                if ($_FILES["imagenes"]["error"][$i] === UPLOAD_ERR_OK) {
                    $nombre_original = $_FILES["imagenes"]["name"][$i];
                    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                    
                    if (in_array($extension, $extensiones_permitidas)) {
                        // Generar nombre único para evitar colisiones
                        $nombre_archivo = uniqid("hab_" . $id_habitacion . "_") . "." . $extension;
                        $destino = $directorio_imagenes . $nombre_archivo;
                        
                        if (move_uploaded_file($_FILES["imagenes"]["tmp_name"][$i], $destino)) {
                            // Guardar referencia en la base de datos
                            $stmt = $conexion->prepare(
                                "INSERT INTO habitacion_imagenes (id_habitacion, nombre_archivo) VALUES (?, ?)"
                            );
                            $stmt->bind_param("is", $id_habitacion, $nombre_archivo);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
        }

        header("Location: habitaciones_listar.php");
        exit;
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
            <select name="id_categoria" id="id_categoria" required>
                <option value="">-- Seleccione --</option>
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
                   value="<?php echo htmlspecialchars($datos["nombre"]); ?>" required>
        </label>

        <label>Descripción corta:
            <textarea name="descripcion_corta" id="descripcion_corta" rows="3" required><?php
                echo htmlspecialchars($datos["descripcion_corta"]);
            ?></textarea>
        </label>

        <label>Descripción larga:
            <textarea name="descripcion_larga" id="descripcion_larga" rows="5"><?php
                echo htmlspecialchars($datos["descripcion_larga"]);
            ?></textarea>
        </label>

        <label>Precio por noche ($):
            <input type="number" step="0.01" min="0.01" name="precio_noche" id="precio_noche"
                   value="<?php echo htmlspecialchars($datos["precio_noche"]); ?>" required>
        </label>

        <label>Número de habitaciones disponibles:
            <input type="number" min="0" name="disponibles" id="disponibles"
                   value="<?php echo htmlspecialchars($datos["disponibles"]); ?>">
        </label>

        <?php if ($editando && !empty($imagenes_actuales)): ?>
            <label>Imágenes actuales:</label>
            <div class="galeria-imagenes">
                <?php foreach ($imagenes_actuales as $img): ?>
                    <div class="imagen-item">
                        <img src="<?php echo $GLOBALS["raiz_sitio"]; ?>imagenes/<?php echo htmlspecialchars($img["nombre_archivo"]); ?>" 
                             alt="Imagen habitación">
                        <button type="button" class="btn-eliminar" 
                                onclick="confirmarEliminarImagen(<?php echo (int)$img['id']; ?>);"
                                title="Eliminar imagen">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label>Agregar imágenes (puede seleccionar múltiples):
            <input type="file" name="imagenes[]" id="imagenes" accept="image/*" multiple 
                   onchange="mostrarPrevisualizacion(this);">
            <small>Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP</small>
        </label>
        
        <div class="preview-container" id="preview-container"></div>

        <br>
        <button type="submit">Guardar</button>
        <a href="habitaciones_listar.php"><button type="button">Cancelar</button></a>
    </form>
</div>

<script src="<?php echo $GLOBALS["raiz_sitio"]; ?>js/validacion.js"></script>
<script>
function confirmarEliminarImagen(idImagen) {
    if (confirm("¿Estás seguro de eliminar esta imagen?")) {
        window.location.href = "habitaciones_form.php?id=<?php echo $id; ?>&eliminar_imagen=" + idImagen;
    }
}

function mostrarPrevisualizacion(input) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-item';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
</body>
</html>
<?php
$conexion->close();
?>