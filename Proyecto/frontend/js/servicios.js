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
