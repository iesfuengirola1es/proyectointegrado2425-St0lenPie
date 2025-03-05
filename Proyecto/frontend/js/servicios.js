/**
 * Módulo: Gestión de Servicios
 * 
 * Este script permite la administración de los servicios dentro de un grupo empresarial.
 * Los usuarios pueden agregar, editar y eliminar servicios.
 *
 * Ejemplo de llamada:
 * -------------------
 * mostrarFormularioServicio(); // Muestra el formulario para agregar un nuevo servicio.
 * guardarServicio(); // Guarda un nuevo servicio o edita uno existente.
 * editarServicio(1); // Carga los datos del servicio con ID 1 para su edición.
 * eliminarServicio(2); // Elimina el servicio con ID 2 tras confirmación del usuario.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `mostrarFormularioServicio()`: Muestra el formulario para agregar un nuevo servicio.
 * - `cerrarFormularioServicio()`: Oculta el formulario de servicios.
 * - `guardarServicio()`: Guarda un nuevo servicio o edita uno existente en la base de datos.
 * - `editarServicio(id)`: Carga los datos de un servicio específico para su edición.
 * - `eliminarServicio(id)`: Elimina un servicio tras confirmación del usuario.
 *
 * Dependencias:
 * -------------
 * - `gestionar_servicio.php` → Backend para manejar la gestión de servicios.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Creación y edición de servicios (`guardarServicio`)**:
 *    - Determina si la acción es "crear" o "editar" según la presencia de un ID de servicio.
 *    - Valida que los campos obligatorios estén completos.
 *    - Envía la solicitud `POST` a `gestionar_servicio.php` con los datos del servicio.
 *    - Si la operación es exitosa, recarga la sección de servicios y cierra el formulario.
 * 2. **Carga de servicio para edición (`editarServicio`)**:
 *    - Obtiene los datos del servicio desde `gestionar_servicio.php` con la acción `obtener`.
 *    - Llena el formulario con la información del servicio.
 * 3. **Eliminación de servicios (`eliminarServicio`)**:
 *    - Solicita confirmación al usuario antes de eliminar un servicio.
 *    - Realiza una solicitud `POST` a `gestionar_servicio.php` con la acción `eliminar`.
 *    - Si la operación es exitosa, recarga la sección de servicios.
 */

function mostrarFormularioServicio() {
    $("#formularioServicio").show();
    $("#tituloFormularioServicio").text("Añadir Servicio");
    $("#servicioID").val("");
    $("#nombreServicio").val("");
    $("#descripcionServicio").val("");
    $("#precioServicio").val("");
}

function cerrarFormularioServicio() {
    $("#formularioServicio").hide();
}

function guardarServicio() {
    let id = $("#servicioID").val();
    let accion = id ? "editar" : "crear";
    let nombre = $("#nombreServicio").val();
    let descripcion = $("#descripcionServicio").val();
    let precio = $("#precioServicio").val();
    let grupoID = obtenerGrupoId();

    if (nombre.trim() === "" || precio === "") {
        mostrarMensaje("Todos los campos son obligatorios.", "error");
        return;
    }

    $.post("../backend/gestionar_servicio.php", {
        accion: accion,
        id: id,
        nombre: nombre,
        descripcion: descripcion,
        precio: precio,
        id_empresa: grupoID
    }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('servicios');
            cerrarFormularioServicio();
        }
    });
}

function editarServicio(id) {
    $.get("../backend/gestionar_servicio.php", { accion: "obtener", id: id }, function(data) {
        let servicio = JSON.parse(data);

        if (!servicio.id_servicio) {
            mostrarMensaje("Error: No se pudo obtener la información del servicio.", "error");
            return;
        }

        $("#formularioServicio").show();
        $("#tituloFormularioServicio").text("Editar Servicio");
        $("#servicioID").val(servicio.id_servicio);
        $("#nombreServicio").val(servicio.nombre);
        $("#descripcionServicio").val(servicio.descripcion);
        $("#precioServicio").val(servicio.precio);
    }).fail(function() {
        mostrarMensaje("Error al obtener los datos del servicio.", "error");
    });
}

function eliminarServicio(id) {
    if (!confirm("¿Estás seguro de eliminar este servicio?")) return;

    $.post("../backend/gestionar_servicio.php", { accion: "eliminar", id: id }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('servicios');
        }
    }).fail(function() {
        mostrarMensaje("Error al eliminar el servicio.", "error");
    });
}
