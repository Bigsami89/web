<?php
require_once __DIR__ . "/../header.php";
?>

<h1>Carrito de reservaciones</h1>
<p>Aquí se muestran las habitaciones que agregaste. Puedes modificar cantidades o eliminar.</p>

<table class="tabla" id="tablaCarrito">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Precio por noche</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <!-- Se llena dinámicamente con JavaScript -->
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
        <td id="totalCarrito">$0.00</td>
        <td></td>
    </tr>
    </tfoot>
</table>

<br>
<button type="button" onclick="vaciarCarrito();">Vaciar carrito</button>

<?php if (isset($_SESSION["tipo"]) && ($_SESSION["tipo"] === "admin" || $_SESSION["tipo"] === "huesped")): ?>
    <form action="carrito_pagar.php" method="post" onsubmit="return confirmarPago();">
        <button type="submit">Pagar y confirmar reservación</button>
    </form>
<?php else: ?>
    <p>Debes iniciar sesión para poder pagar.</p>
<?php endif; ?>

<a href="<?php echo $GLOBALS["raiz_sitio"]; ?>index.php"><button type="button">Seguir reservando</button></a>

<script>
// Al cargar la página, mostrar contenido del carrito
document.addEventListener("DOMContentLoaded", function() {
    mostrarCarritoEnTabla();
});
</script>

<?php
require_once __DIR__ . "/../footer.php";
?>
