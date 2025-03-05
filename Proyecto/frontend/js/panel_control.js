document.addEventListener("DOMContentLoaded", function () {
    let grupoID = obtenerGrupoId();

    // Llamada AJAX para obtener el nombre del grupo
    fetch(`../backend/obtener_grupo.php?id=${grupoID}`)
        .then(response => response.json())
        .then(data => {
            if (data.nombre) {
                document.getElementById("nombreGrupo").textContent = data.nombre;
            } else {
                document.getElementById("nombreGrupo").textContent = "Tu Grupo";
            }
        })
        .catch(error => console.error("Error al obtener el nombre del grupo:", error));
});

function cargarSeccion(seccion) {
    $(".content-area").html("<h2>Cargando " + seccion + "...</h2>");
    $.get("../backend/" + seccion + ".php?id_empresa=" + obtenerGrupoId(), function(data) {
        $(".content-area").html(data);
    }).fail(function() {
        $(".content-area").html("<h2>Error al cargar la sección " + seccion + ".</h2>");
    });
}

function obtenerGrupoId() {
    let urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id_empresa');
}

function cambiarEmpresa(){
    window.location.href = "../frontend/dashboard.html";
}

function cerrarSesion() {
    fetch('../backend/logout.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "../frontend";
            } else {
                console.error("❌ Error al cerrar sesión:", data.error);
            }
        })
        .catch(error => console.error("❌ Error en la solicitud de cierre de sesión:", error));
}
