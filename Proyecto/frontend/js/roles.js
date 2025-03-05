/**
 * Módulo: Gestión de Roles
 * 
 * Este script permite la administración de roles en el sistema, incluyendo su creación, edición y eliminación.
 * También gestiona la carga dinámica de permisos asignables a los roles.
 *
 * Ejemplo de llamada:
 * -------------------
 * mostrarFormularioRol(); // Muestra el formulario para agregar un nuevo rol.
 * guardarRol(); // Guarda un nuevo rol o edita uno existente.
 * editarRol(2); // Carga los datos del rol con ID 2 para su edición.
 * eliminarRol(3); // Elimina el rol con ID 3 tras confirmación.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `mostrarFormularioRol()`: Muestra el formulario para agregar un nuevo rol y carga los permisos disponibles.
 * - `cerrarFormularioRol()`: Oculta el formulario de roles.
 * - `cargarPermisos()`: Obtiene la lista de permisos desde el backend y los muestra en el formulario.
 * - `guardarRol()`: Guarda un nuevo rol o edita uno existente en la base de datos.
 * - `editarRol(id)`: Carga los datos de un rol específico para su edición y marca sus permisos.
 * - `eliminarRol(id)`: Elimina un rol tras confirmación del usuario.
 *
 * Dependencias:
 * -------------
 * - `gestionar_roles.php` → Backend para manejar la gestión de roles y permisos.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Creación y edición de roles (`guardarRol`)**:
 *    - Determina si la acción es "crear" o "editar" según la presencia de un ID de rol.
 *    - Valida que el nombre no esté vacío y que al menos un permiso haya sido seleccionado.
 *    - Envía la solicitud `POST` a `gestionar_roles.php` con los datos del rol y sus permisos.
 *    - Si la operación es exitosa, recarga la sección de roles y cierra el formulario.
 * 2. **Carga de rol para edición (`editarRol`)**:
 *    - Obtiene los datos del rol desde `gestionar_roles.php` con la acción `obtener`.
 *    - Llena el formulario con la información del rol y sus permisos asociados.
 *    - Marca dinámicamente los checkboxes de los permisos asignados al rol.
 * 3. **Eliminación de roles (`eliminarRol`)**:
 *    - Solicita confirmación al usuario antes de eliminar un rol.
 *    - Realiza una solicitud `POST` a `gestionar_roles.php` con la acción `eliminar`.
 *    - Si la operación es exitosa, recarga la sección de roles.
 * 4. **Carga de permisos disponibles (`cargarPermisos`)**:
 *    - Realiza una solicitud `GET` a `gestionar_roles.php` con la acción `listar_permisos`.
 *    - Recibe la lista de permisos y los muestra en el formulario como checkboxes.
 */

function mostrarFormularioRol() {
    $("#formularioRol").show();
    $("#tituloFormularioRol").text("Añadir Rol");
    $("#rolID").val("");
    $("#nombreRol").val("");
    $(".permisoCheckbox").prop("checked", false);

    cargarPermisos();
}

function cerrarFormularioRol() {
    $("#formularioRol").hide();
}

function cargarPermisos() {
    $.get("../backend/gestionar_roles.php", { accion: "listar_permisos" }, function(data) {
        let permisos = JSON.parse(data);
        let listaPermisos = $("#listaPermisos");
        listaPermisos.empty();

        permisos.forEach(permiso => {
            listaPermisos.append(`
                <label>
                    <input type="checkbox" class="permisoCheckbox" value="${permiso.id_permiso}"> 
                    ${permiso.nombre} - ${permiso.descripcion}
                </label><br>
            `);
        });
    });
}

function guardarRol() {
    let id = $("#rolID").val();
    let accion = id ? "editar" : "crear";
    let nombre = $("#nombreRol").val();
    let permisos = [];

    $(".permisoCheckbox:checked").each(function() {
        permisos.push($(this).val());
    });

    if (nombre.trim() === "" || permisos.length === 0) {
        mostrarMensaje("Debe ingresar un nombre y seleccionar al menos un permiso.", "error");
        return;
    }

    $.post("../backend/gestionar_roles.php", {
        accion: accion,
        id: id,
        nombre: nombre,
        permisos: JSON.stringify(permisos)
    }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('roles');
            cerrarFormularioRol();
        }
    });
}

function editarRol(id) {
    $.get("../backend/gestionar_roles.php", { accion: "obtener", id: id }, function(data) {
        let rol = JSON.parse(data);

        if (!rol.id_rol) {
            mostrarMensaje("Error: No se pudo obtener la información del rol.", "error");
            return;
        }

        $("#formularioRol").show();
        $("#tituloFormularioRol").text("Editar Rol");
        $("#rolID").val(rol.id_rol);
        $("#nombreRol").val(rol.nombre);

        cargarPermisos();

        setTimeout(() => {
            $(".permisoCheckbox").prop("checked", false);
            rol.permisos.forEach(permiso => {
                $(".permisoCheckbox[value='" + permiso + "']").prop("checked", true);
            });
        }, 500);
    }).fail(function() {
        mostrarMensaje("Error al obtener los datos del rol.", "error");
    });
}

function eliminarRol(id) {
    if (!confirm("¿Estás seguro de eliminar este rol?")) return;

    $.post("../backend/gestionar_roles.php", { accion: "eliminar", id: id }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('roles');
        }
    });
}
