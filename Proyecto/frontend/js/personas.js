/**
 * Módulo: Gestión de Usuarios en un Grupo
 * 
 * Este script permite la administración de los usuarios dentro de un grupo empresarial.
 * Incluye la funcionalidad de agregar, eliminar y cambiar el rol de un usuario.
 *
 * Ejemplo de llamada:
 * -------------------
 * mostrarFormularioAgregar(); // Muestra el formulario de búsqueda y selección de usuarios.
 * agregarUsuario(); // Agrega un usuario seleccionado al grupo.
 * eliminarUsuario(5); // Elimina al usuario con ID 5 tras confirmación.
 * guardarCambioRol(3); // Cambia el rol del usuario con ID 3.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `mostrarFormularioAgregar()`: Muestra el formulario de búsqueda de usuarios y realiza una consulta en tiempo real.
 * - `cerrarFormularioAgregar()`: Oculta el formulario de búsqueda de usuarios.
 * - `agregarUsuario()`: Añade un usuario seleccionado al grupo actual.
 * - `eliminarUsuario(id)`: Elimina a un usuario del grupo tras confirmación del usuario.
 * - `guardarCambioRol(usuarioID)`: Cambia el rol de un usuario dentro del grupo.
 *
 * Dependencias:
 * -------------
 * - `gestionar_personas.php` → Backend para manejar la gestión de usuarios en grupos.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Agregar usuario (`agregarUsuario`)**:
 *    - Recupera el ID del usuario seleccionado en el formulario.
 *    - Envía una solicitud `POST` a `gestionar_personas.php` con la acción `agregar_usuario`.
 *    - Muestra un mensaje de éxito o error según la respuesta del servidor.
 * 2. **Eliminar usuario (`eliminarUsuario`)**:
 *    - Solicita confirmación al usuario antes de eliminar.
 *    - Envía una solicitud `POST` con la acción `eliminar_usuario`.
 *    - Si la operación es exitosa, recarga la sección `personas`.
 * 3. **Cambiar rol (`guardarCambioRol`)**:
 *    - Recupera el nuevo rol seleccionado para el usuario.
 *    - Envía una solicitud `POST` con la acción `cambiar_rol` y los datos del usuario.
 *    - Si la actualización es exitosa, deshabilita el `select` temporalmente y lo habilita después de 2 segundos.
 * 4. **Búsqueda en tiempo real (`mostrarFormularioAgregar`)**:
 *    - Muestra el formulario de búsqueda de usuarios.
 *    - Realiza una consulta en `gestionar_personas.php` con la acción `buscar_usuarios` mientras el usuario escribe.
 *    - Llena el `select` con los resultados obtenidos.
 */

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
        id_usuario: usuarioID
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
        id_usuario: id
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
