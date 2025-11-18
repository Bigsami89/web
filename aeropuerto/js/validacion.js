function validarLogin() {
    const usuario = document.getElementById("usuario");
    const contrasena = document.getElementById("contrasena");

    if (!usuario.value.trim() || !contrasena.value.trim()) {
        alert("Debes llenar todos los campos de inicio de sesión.");
        return false;
    }
    return true;
}

function validarRegistro() {
    const nombre = document.getElementById("nombre");
    const usuario = document.getElementById("usuario_reg");
    const contrasena = document.getElementById("contrasena_reg");

    if (!nombre.value.trim() || !usuario.value.trim() || !contrasena.value.trim()) {
        alert("Todos los campos de registro son obligatorios.");
        return false;
    }
    return true;
}

function validarHabitacion() {
    const nombre = document.getElementById("nombre_hab");
    const descC = document.getElementById("descripcion_corta");
    const precio = document.getElementById("precio_noche");

    if (!nombre.value.trim() || !descC.value.trim() || !precio.value.trim()) {
        alert("Nombre, descripción corta y precio son obligatorios.");
        return false;
    }
    if (parseFloat(precio.value) <= 0) {
        alert("El precio debe ser mayor que cero.");
        return false;
    }
    return true;
}

function validarBusqueda() {
    const q = document.getElementById("q");
    if (!q.value.trim()) {
        alert("Escribe un término de búsqueda.");
        return false;
    }
    return true;
}
