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
