/**
 * Módulo: Gestión de Permisos
 * 
 * Este script permite cargar y mostrar la lista de permisos disponibles en el sistema.
 *
 * Ejemplo de llamada:
 * -------------------
 * cargarPermisos(); // Carga y muestra la lista de permisos en la tabla de la interfaz.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `cargarPermisos()`: Obtiene la lista de permisos desde el backend y los muestra en la tabla.
 *
 * Dependencias:
 * -------------
 * - `gestionar_permisos.php` → Devuelve la lista de permisos en formato JSON.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Carga de permisos (`cargarPermisos`)**:
 *    - Realiza una solicitud `GET` a `gestionar_permisos.php` con la acción `listar_permisos`.
 *    - Recibe la lista de permisos en formato JSON.
 *    - Vacía la tabla y la llena con los permisos obtenidos.
 *    - Si la solicitud falla, muestra un mensaje de error en la interfaz.
 * 2. **Ejecución automática al cargar la página (`$(document).ready()`)**:
 *    - Llama a `cargarPermisos()` automáticamente cuando la página termina de cargar.
 */

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
