function cargarSeccion(seccion) {
    $(".content-area").html("<h2>Cargando " + seccion + "...</h2>");
    $.get("../backend/" + seccion + ".php?id=" + obtenerGrupoId(), function(data) {
        $(".content-area").html(data);
    }).fail(function() {
        $(".content-area").html("<h2>Error al cargar la sección " + seccion + ".</h2>");
    });
}

function obtenerGrupoId() {
    let urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id_empresa');
}

function cerrarSesion() {
    window.location.href = "../backend/logout.php";
}
