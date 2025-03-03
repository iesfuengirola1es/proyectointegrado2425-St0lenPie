document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Script `personas.js` cargado correctamente.");

    let botonesGuardar = document.querySelectorAll(".botonGuardarRol");

    if (botonesGuardar.length > 0) {
        console.log("🟢 Se encontraron " + botonesGuardar.length + " botones de guardar rol.");
        botonesGuardar.forEach(boton => {
            boton.addEventListener("click", function () {
                let usuarioID = this.getAttribute("data-usuario-id");
                let nuevoRol = $("#rol_" + usuarioID).val();

                console.log("🟡 Enviando solicitud para cambiar el rol...");
                console.log("🔹 ID Usuario:", usuarioID);
                console.log("🔹 Nuevo ID Rol:", nuevoRol);

                if (!usuarioID || !nuevoRol) {
                    console.error("❌ Error: No se ha seleccionado un rol válido.");
                    return;
                }

                $.post("../backend/gestionar_personas.php", {
                    accion: "cambiar_rol",
                    id_usuario: usuarioID,
                    id_rol: nuevoRol
                }, function(response) {
                    console.log("🟢 Respuesta del servidor al cambiar rol:", response);

                    if (response.includes("error:")) {
                        console.error("❌ Error en el servidor:", response);
                        mostrarMensaje(response.replace("error:", ""), "error");
                    } else {
                        mostrarMensaje(response.replace("success:", ""), "success");
                        cargarSeccion('personas'); // Recargar lista de usuarios después del cambio
                    }
                }).fail(function(xhr, status, error) {
                    console.error("❌ Error en la solicitud AJAX:", status, error);
                    mostrarMensaje("Error al cambiar el rol del usuario.", "error");
                });
            });
        });
    } else {
        console.error("❌ No se encontraron botones de guardar rol.");
    }
});



function asignarEventosBotonesGuardar() {
    let botonesGuardar = document.querySelectorAll(".botonGuardarRol");

    if (botonesGuardar.length > 0) {
        console.log("🟢 Se encontraron " + botonesGuardar.length + " botones de guardar rol.");
        botonesGuardar.forEach(boton => {
            boton.removeEventListener("click", guardarCambioRol); // Evitar duplicación de eventos
            boton.addEventListener("click", function () {
                let usuarioID = this.getAttribute("data-usuario-id");
                let nuevoRol = $("#rol_" + usuarioID).val();

                console.log("🟡 Enviando solicitud para cambiar el rol...");
                console.log("🔹 ID Usuario:", usuarioID);
                console.log("🔹 Nuevo ID Rol:", nuevoRol);

                if (!usuarioID || !nuevoRol) {
                    console.error("❌ Error: No se ha seleccionado un rol válido.");
                    return;
                }

                $.post("../backend/gestionar_personas.php", {
                    accion: "cambiar_rol",
                    id_usuario: usuarioID,
                    id_rol: nuevoRol
                }, function(response) {
                    console.log("🟢 Respuesta del servidor al cambiar rol:", response);

                    if (response.includes("error:")) {
                        console.error("❌ Error en el servidor:", response);
                        mostrarMensaje(response.replace("error:", ""), "error");
                    } else {
                        mostrarMensaje(response.replace("success:", ""), "success");
                        cargarSeccion('personas'); // Recargar lista de usuarios después del cambio
                    }
                }).fail(function(xhr, status, error) {
                    console.error("❌ Error en la solicitud AJAX:", status, error);
                    mostrarMensaje("Error al cambiar el rol del usuario.", "error");
                });
            });
        });
    } else {
        console.error("❌ No se encontraron botones de guardar rol. Asegúrate de que `personas.php` cargó correctamente.");
        setTimeout(asignarEventosBotonesGuardar, 1000); // Reintentar después de 1 segundo
    }
}

// Asignar eventos cuando la sección de usuarios esté completamente cargada
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Script `personas.js` cargado correctamente.");
    setTimeout(asignarEventosBotonesGuardar, 2000); // Esperar 2 segundos para asegurar la carga
});


// Asignar eventos cuando se cargue la sección de personas
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Script `personas.js` cargado correctamente.");
    setTimeout(asignarEventosBotonesGuardar, 2000); // Esperar 2 segundos para asegurar la carga
});


// Llamar a la función después de cargar la sección de personas
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Script `personas.js` cargado correctamente.");
    setTimeout(asignarEventosBotonesGuardar, 1000); // Esperar un segundo para asegurar la carga
});


function mostrarFormularioAgregar() {
    $("#formularioAgregar").show();

    $("#buscarUsuario").on("keyup", function () {
        let terminoBusqueda = $(this).val();
        $.get("../backend/gestionar_personas.php", { accion: "buscar_usuarios", email: terminoBusqueda }, function(data) {
            let usuarios = JSON.parse(data);
            let select = $("#usuarioSeleccionado");
            select.empty().append('<option value="">Seleccionar usuario...</option>');
            usuarios.forEach(usuario => {
                select.append(`<option value="${usuario.id_usuario}">${usuario.nombre} (${usuario.email})</option>`);
            });
        });
    }).trigger("keyup");
}

function cerrarFormularioAgregar() {
    $("#formularioAgregar").hide();
}

function agregarUsuario() {
    let usuarioID = $("#usuarioSeleccionado").val();
    let grupoID = obtenerGrupoId();

    if (!usuarioID) {
        mostrarMensaje("Debe seleccionar un usuario.", "error");
        return;
    }

    $.post("../backend/gestionar_personas.php", {
        accion: "agregar_usuario",
        id_usuario: usuarioID,
        id_empresa: grupoID
    }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('personas');
            cerrarFormularioAgregar();
        }
    });
}

function eliminarUsuario(id) {
    if (!confirm("¿Estás seguro de eliminar a este usuario del grupo?")) return;

    $.post("../backend/gestionar_personas.php", {
        accion: "eliminar_usuario",
        id_usuario: id,
        id_empresa: obtenerGrupoId()
    }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('personas');
        }
    });
}

function guardarCambioRol(usuarioID) {
    let nuevoRol = $("#rol_" + usuarioID).val();

    console.log("🟡 Enviando solicitud para cambiar el rol...");
    console.log("🔹 ID Usuario:", usuarioID);
    console.log("🔹 Nuevo ID Rol:", nuevoRol);

    if (!usuarioID || !nuevoRol) {
        console.error("❌ Error: No se ha seleccionado un rol válido.");
        return;
    }

    $.post("../backend/gestionar_personas.php", {
        accion: "cambiar_rol",
        id_usuario: usuarioID,
        id_rol: nuevoRol
    }, function(response) {
        console.log("🟢 Respuesta del servidor al cambiar rol:", response);

        if (response.includes("error:")) {
            console.error("❌ Error en el servidor:", response);
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje("✅ Rol actualizado correctamente.", "success");

            // ✅ Actualizar solo el campo de rol sin recargar toda la sección
            $("#rol_" + usuarioID).prop("disabled", true);
            setTimeout(() => $("#rol_" + usuarioID).prop("disabled", false), 2000);
        }
    }).fail(function(xhr, status, error) {
        console.error("❌ Error en la solicitud AJAX:", status, error);
        mostrarMensaje("Error al cambiar el rol del usuario.", "error");
    });
}
