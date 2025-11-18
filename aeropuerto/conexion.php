<?php
require_once __DIR__ . "/config.inc.php";

$conexion = new mysqli(
    $GLOBALS["servidor"],
    $GLOBALS["usuario"],
    $GLOBALS["contrasena"],
    $GLOBALS["base_datos"]
);

if ($conexion->connect_errno) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>
