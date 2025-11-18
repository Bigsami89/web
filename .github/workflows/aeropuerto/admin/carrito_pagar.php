<?php
require_once "header.php";
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    echo "<p>Debes iniciar sesión para confirmar el pago.</p>";
    require_once "footer.php";
    exit;
}

if (!isset($_COOKIE["carrito"])) {
    echo "<p>No hay elementos en el carrito.</p>";
    require_once "footer.php";
    exit;
}

$carritoJson = $_COOKIE["carrito"];
$items = json_decode($carritoJson, true);

if (!is_array($items) || count($items) === 0) {
    echo "<p>No hay elementos válidos en el carrito.</p>";
    require_once "footer.php";
    exit;
}

// Creamos la reservación
$conexion->begin_transaction();

try {
    $idUsuario = $_SESSION["id_usuario"];
    $fecha = date("Y-m-d H:i:s");

    $stmt = $conexion->prepare(
        "INSERT INTO reservaciones (id_usuario, fecha, total)
         VALUES (?, ?, 0)"
    );
    $stmt->bind_param("is", $idUsuario, $fecha);
    $stmt->execute();
    $idReservacion = $stmt->insert_id;
    $stmt->close();

    $total = 0;

    foreach ($items as $item) {
        $idHab    = (int)$item["id"];
        $nombre   = $item["nombre"];
        $precio   = (float)$item["precio"];
        $cantidad = (int)$item["cantidad"];

        if ($cantidad <= 0) continue;

        // Insertar detalle
        $subtotal = $precio * $cantidad;
        $total += $subtotal;

        $stmt = $conexion->prepare(
            "INSERT INTO reservacion_detalle
                (id_reservacion, id_habitacion, cantidad, precio_unitario, subtotal)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiidd", $idReservacion, $idHab, $cantidad, $precio, $subtotal);
        $stmt->execute();
        $stmt->close();

        // Descontar de la base de datos el número de habitaciones disponibles
        $stmt = $conexion->prepare(
            "UPDATE habitaciones
             SET disponibles = disponibles - ?
             WHERE id = ? AND disponibles >= ?"
        );
        $stmt->bind_param("iii", $cantidad, $idHab, $cantidad);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception("No hay suficientes habitaciones disponibles para el ID $idHab.");
        }
        $stmt->close();
    }

    // Actualizar total de la reservación
    $stmt = $conexion->prepare(
        "UPDATE reservaciones SET total = ? WHERE id = ?"
    );
    $stmt->bind_param("di", $total, $idReservacion);
    $stmt->execute();
    $stmt->close();

    $conexion->commit();

    // Borrar cookie del carrito
    setcookie("carrito", "", time() - 3600, "/");

    echo "<h1>Pago realizado</h1>";
    echo "<p>Tu reservación se ha registrado correctamente.</p>";
    echo "<p>Total pagado: $" . number_format($total, 2) . "</p>";
    echo '<a href="index.php"><button>Volver al inicio</button></a>';

} catch (Exception $e) {
    $conexion->rollback();
    echo "<h1>Error al procesar el pago</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo '<a href="carrito.php"><button>Volver al carrito</button></a>';
}

$conexion->close();
require_once "footer.php";
?>
