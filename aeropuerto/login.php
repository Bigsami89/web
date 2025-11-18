<?php
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario   = trim($_POST["usuario"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");

    if ($usuario === "" || $contrasena === "") {
        $error = "Debes llenar todos los campos.";
    } else {
        $stmt = $conexion->prepare(
            "SELECT id, usuario, contrasena, tipo
             FROM usuarios
             WHERE usuario = ? AND contrasena = ?"
        );
        $stmt->bind_param("ss", $usuario, $contrasena);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($fila = $resultado->fetch_assoc()) {
            $_SESSION["id_usuario"] = $fila["id"];
            $_SESSION["usuario"]    = $fila["usuario"];
            $_SESSION["tipo"]       = $fila["tipo"];
            header("Location: index.php");
            exit;
        } else {
            $error = "Nombre de usuario o contraseña incorrectos.";
        }

        $stmt->close();
    }
}
?>

<h1>Iniciar sesión</h1>

<?php if ($error !== ""): ?>
    <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="login.php" method="post" onsubmit="return validarLogin();">
    <label>Usuario:
        <input type="text" name="usuario" id="usuario">
    </label>
    <label>Contraseña:
        <input type="password" name="contrasena" id="contrasena">
    </label>
    <button type="submit">Entrar</button>
    <a href="index.php"><button type="button">Cancelar</button></a>
</form>

<?php
$conexion->close();
require_once __DIR__ . "/footer.php";
?>
