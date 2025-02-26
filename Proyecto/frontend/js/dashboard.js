function mostrarFormulario() {
    document.getElementById('crearGrupoForm').style.display = 'block';
}

function cerrarFormulario() {
    document.getElementById('crearGrupoForm').style.display = 'none';
    document.getElementById('errorMensaje').style.display = 'none';
}

function cargarGrupos() {
    $.get("../backend/dashboard.php", function(data) {
        if (data.includes("error:")) {
            console.error(data);
            $("#listaGrupos").html("<p>Error al cargar los grupos.</p>");
        } else {
            $("#listaGrupos").html(data);
        }
    }).fail(function() {
        console.error("Error en la solicitud AJAX.");
        $("#listaGrupos").html("<p>Error al obtener la lista de grupos.</p>");
    });
}


function crearGrupo() {
    let nombreGrupo = $("#nombreGrupo").val();

    if (nombreGrupo.trim() === "") {
        $("#errorMensaje").text("El nombre del grupo no puede estar vacío.").show();
        return;
    }

    $.post("../backend/crear_grupo.php", { nombre: nombreGrupo }, function(response) {
        if (response.includes("error:")) {
            let errorMsg = response.replace("error:", "").trim();
            $("#errorMensaje").text(errorMsg).show();
        } else if (response.includes("success:")) {
            cargarGrupos(); // Recargar lista tras crear el grupo
            cerrarFormulario(); // Cerrar formulario tras creación
        }
    });
}

$(document).ready(function() {
    cargarGrupos();
});

function eliminarGrupo(grupoID) {
    if (!confirm("¿Estás seguro de que deseas eliminar este grupo? Esta acción no se puede deshacer.")) return;

    $.post("../backend/eliminar_grupo.php", { id_empresa: grupoID }, function(response) {
        if (response.includes("error:")) {
            alert(response.replace("error:", "").trim());
        } else {
            alert("Grupo eliminado correctamente.");
            cargarGrupos();
        }
    });
}
