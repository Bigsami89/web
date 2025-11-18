<?php
require_once "header.php";
require_once "conexion.php";

$mensaje = "";
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario   = trim($_POST["usuario"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");
    $nombre    = trim($_POST["nombre"] ?? "");

    if ($usuario === "" || $contrasena === "" || $nombre === "") {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            $stmt->close();
            $tipo = "huesped";
            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (usuario, contrasena, nombre_completo, tipo)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $usuario, $contrasena, $nombre, $tipo);
            if ($stmt->execute()) {
                $mensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "Ocurrió un error al registrar.";
            }
        }
        $stmt->close();
    }
}
?>

<h1>Registro de huésped</h1>

<?php if ($mensaje !== ""): ?>
    <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if ($error !== ""): ?>
    <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="registro.php" method="post" onsubmit="return validarRegistro();">
    <label>Nombre completo:
        <input type="text" name="nombre" id="nombre">
    </label>
    <label>Usuario:
        <input type="text" name="usuario" id="usuario_reg">
    </label>
    <label>Contraseña:
        <input type="password" name="contrasena" id="contrasena_reg">
    </label>
    <button type="submit">Registrarse</button>
    <a href="index.php"><button type="button">Cancelar</button></a>
</form>

<?php
$conexion->close();
require_once "footer.php";
?>
