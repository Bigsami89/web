<?php
require_once __DIR__ . "/../seguridad_admin.php";
require_once __DIR__ . "/../conexion.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {
    // Verificar si la tabla de imágenes existe
    $tablaImagenesExiste = $conexion->query("SHOW TABLES LIKE 'habitacion_imagenes'")->num_rows > 0;
    
    if ($tablaImagenesExiste) {
        // Primero obtener y eliminar las imágenes físicamente
        $stmt = $conexion->prepare("SELECT nombre_archivo FROM habitacion_imagenes WHERE id_habitacion = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($img = $resultado->fetch_assoc()) {
            $ruta_imagen = __DIR__ . "/../imagenes/" . $img["nombre_archivo"];
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }
        $stmt->close();
        
        // Eliminar registros de imágenes de la base de datos
        $stmt = $conexion->prepare("DELETE FROM habitacion_imagenes WHERE id_habitacion = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Eliminar la habitación
    $stmt = $conexion->prepare("DELETE FROM habitaciones WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conexion->close();
header("Location: habitaciones_listar.php");
exit;