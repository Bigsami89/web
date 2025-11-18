<?php
require_once "../seguridad_admin.php";
require_once "../conexion.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {
    $stmt = $conexion->prepare("DELETE FROM habitaciones WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
$conexion->close();
header("Location: habitaciones_listar.php");
exit;
