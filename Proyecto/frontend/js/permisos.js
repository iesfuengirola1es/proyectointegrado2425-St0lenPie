function cargarPermisos() {
    $.get("../backend/gestionar_permisos.php", { accion: "listar_permisos" }, function(data) {
        let permisos = JSON.parse(data);
        let tablaPermisos = $("tbody");
        tablaPermisos.empty();

        permisos.forEach(permiso => {
            tablaPermisos.append(`
                <tr>
                    <td>${permiso.nombre}</td>
                    <td>${permiso.descripcion}</td>
                </tr>
            `);
        });
    }).fail(function() {
        mostrarMensaje("Error al cargar la lista de permisos.", "error");
    });
}

$(document).ready(function() {
    cargarPermisos();
});
