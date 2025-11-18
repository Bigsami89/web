// Guardar carrito en cookie (JSON)
function obtenerCarrito() {
    const nombre = "carrito=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(nombre) === 0) {
            const valor = c.substring(nombre.length, c.length);
            try {
                return JSON.parse(valor);
            } catch (e) {
                return [];
            }
        }
    }
    return [];
}

function guardarCarrito(carrito) {
    const valor = JSON.stringify(carrito);
    // Cookie por 7 días
    const d = new Date();
    d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000));
    const expires = "expires=" + d.toUTCString();
    document.cookie = "carrito=" + encodeURIComponent(valor) + ";" + expires + ";path=/";
}

function agregarAlCarrito(id, nombre, precio) {
    let carrito = obtenerCarrito();
    let encontrado = false;

    for (let i = 0; i < carrito.length; i++) {
        if (carrito[i].id === id) {
            carrito[i].cantidad += 1;
            encontrado = true;
            break;
        }
    }

    if (!encontrado) {
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }

    guardarCarrito(carrito);
    alert("Habitación agregada al carrito.");
}

function actualizarCantidadCarrito(id, cantidad) {
    let carrito = obtenerCarrito();
    cantidad = parseInt(cantidad);
    if (isNaN(cantidad) || cantidad <= 0) {
        cantidad = 1;
    }

    for (let i = 0; i < carrito.length; i++) {
        if (carrito[i].id === id) {
            carrito[i].cantidad = cantidad;
            break;
        }
    }
    guardarCarrito(carrito);
    mostrarCarritoEnTabla();
}

function eliminarDelCarrito(id) {
    if (!confirm("¿Deseas eliminar este elemento del carrito?")) {
        return;
    }
    let carrito = obtenerCarrito();
    carrito = carrito.filter(item => item.id !== id);
    guardarCarrito(carrito);
    mostrarCarritoEnTabla();
}

function vaciarCarrito() {
    if (!confirm("¿Deseas vaciar el carrito?")) {
        return;
    }
    document.cookie = "carrito=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    mostrarCarritoEnTabla();
}

function mostrarCarritoEnTabla() {
    const carrito = obtenerCarrito();
    const cuerpo = document.querySelector("#tablaCarrito tbody");
    const totalElemento = document.getElementById("totalCarrito");

    if (!cuerpo || !totalElemento) return;

    cuerpo.innerHTML = "";
    let total = 0;

    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;

        const fila = document.createElement("tr");

        const tdNombre = document.createElement("td");
        tdNombre.textContent = item.nombre;
        fila.appendChild(tdNombre);

        const tdPrecio = document.createElement("td");
        tdPrecio.textContent = "$" + item.precio.toFixed(2);
        fila.appendChild(tdPrecio);

        const tdCantidad = document.createElement("td");
        const input = document.createElement("input");
        input.type = "number";
        input.value = item.cantidad;
        input.min = "1";
        input.onchange = function () {
            actualizarCantidadCarrito(item.id, this.value);
        };
        tdCantidad.appendChild(input);
        fila.appendChild(tdCantidad);

        const tdSubtotal = document.createElement("td");
        tdSubtotal.textContent = "$" + subtotal.toFixed(2);
        fila.appendChild(tdSubtotal);

        const tdAcciones = document.createElement("td");
        const btnEliminar = document.createElement("button");
        btnEliminar.textContent = "Eliminar";
        btnEliminar.onclick = function () {
            eliminarDelCarrito(item.id);
        };
        tdAcciones.appendChild(btnEliminar);
        fila.appendChild(tdAcciones);

        cuerpo.appendChild(fila);
    });

    totalElemento.textContent = "$" + total.toFixed(2);
}

// Ventana emergente con información sintetizada de la habitación
function mostrarDetallesHabitacion(nombre, descripcion, precio) {
    alert("Habitación: " + nombre +
          "\nDescripción: " + descripcion +
          "\nPrecio por noche: $" + precio);
}

function confirmarPago() {
    return confirm("¿Deseas confirmar el pago y realizar la reservación?");
}
