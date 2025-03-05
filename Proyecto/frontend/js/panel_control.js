/**
 * Módulo: Panel de Control
 * 
 * Este script maneja la carga dinámica de secciones dentro del panel de control de una empresa.
 * También permite cambiar de empresa, cerrar sesión y obtener el nombre del grupo actual.
 *
 * Ejemplo de llamada:
 * -------------------
 * cargarSeccion('inventario'); // Carga la sección de inventario en el panel de control.
 * cerrarSesion(); // Cierra la sesión del usuario y lo redirige a la página de inicio.
 * cambiarEmpresa(); // Redirige al usuario al dashboard para seleccionar otra empresa.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `cargarSeccion(seccion)`: Carga dinámicamente una sección dentro del panel de control.
 * - `obtenerGrupoId()`: Extrae el ID de la empresa desde la URL.
 * - `cambiarEmpresa()`: Redirige al usuario al dashboard para seleccionar otro grupo.
 * - `cerrarSesion()`: Cierra la sesión del usuario y lo redirige a la página de inicio.
 * - `DOMContentLoaded` (evento): Obtiene el nombre del grupo al cargar el panel de control.
 *
 * Dependencias:
 * -------------
 * - `obtener_grupo.php` → Obtiene el nombre del grupo desde la base de datos.
 * - `logout.php` → Maneja el cierre de sesión.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Carga del nombre del grupo (`DOMContentLoaded`)**:
 *    - Obtiene el `id_empresa` desde la URL con `obtenerGrupoId()`.
 *    - Realiza una solicitud a `obtener_grupo.php` para obtener el nombre del grupo.
 *    - Muestra el nombre del grupo en el panel de control.
 * 2. **Carga dinámica de secciones (`cargarSeccion`)**:
 *    - Muestra un mensaje temporal de "Cargando..." mientras se obtiene la sección.
 *    - Solicita la sección correspondiente al backend (`<seccion>.php?id_empresa=<id>`).
 *    - Si la carga falla, muestra un mensaje de error en la interfaz.
 * 3. **Cambio de empresa (`cambiarEmpresa`)**:
 *    - Redirige al usuario al `dashboard.html` para seleccionar otro grupo.
 * 4. **Cierre de sesión (`cerrarSesion`)**:
 *    - Envía una solicitud a `logout.php` para cerrar la sesión.
 *    - Si la operación es exitosa, redirige al usuario a la página de inicio.
 *    - Si hay un error, se muestra un mensaje en la consola.
 */

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
