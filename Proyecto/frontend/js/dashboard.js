/**
 * Módulo: Gestión del Dashboard de Grupos
 * 
 * Este script maneja la carga de los grupos empresariales en los que el usuario está registrado,
 * permitiendo la creación y eliminación de grupos y redirigiendo al panel de control.
 *
 * Ejemplo de llamada:
 * -------------------
 * cargarGrupos(); // Carga y muestra la lista de grupos del usuario en el dashboard.
 * crearGrupo();   // Envía una solicitud para crear un nuevo grupo.
 * eliminarGrupo(1); // Elimina el grupo con ID 1 tras confirmación del usuario.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `cargarGrupos()`: Carga y muestra la lista de grupos del usuario.
 * - `crearGrupo()`: Permite la creación de un nuevo grupo en el sistema.
 * - `eliminarGrupo(grupoID)`: Elimina un grupo seleccionado por el usuario.
 * - `mostrarFormulario()`: Muestra el formulario para crear un nuevo grupo.
 * - `cerrarFormulario()`: Oculta el formulario de creación de grupos.
 *
 * Dependencias:
 * -------------
 * - `dashboard.php` → Devuelve la lista de grupos del usuario en formato JSON.
 * - `crear_grupo.php` → Maneja la creación de nuevos grupos en la base de datos.
 * - `eliminar_grupo.php` → Permite eliminar un grupo existente.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Carga de grupos (`cargarGrupos`)**:
 *    - Realiza una solicitud a `dashboard.php` para obtener los grupos del usuario.
 *    - Si el usuario no pertenece a ningún grupo, muestra un mensaje informativo.
 *    - Si hay grupos, los muestra como una lista con eventos de clic para redirigir al panel de control.
 * 2. **Creación de grupo (`crearGrupo`)**:
 *    - Obtiene el nombre del grupo ingresado por el usuario.
 *    - Valida que el campo no esté vacío.
 *    - Realiza una solicitud a `crear_grupo.php` y maneja la respuesta.
 *    - Si la creación es exitosa, recarga la lista de grupos y cierra el formulario.
 * 3. **Eliminación de grupo (`eliminarGrupo`)**:
 *    - Solicita confirmación al usuario antes de eliminar.
 *    - Realiza una solicitud a `eliminar_grupo.php` con el `id_empresa` del grupo a eliminar.
 *    - Si la eliminación es exitosa, recarga la lista de grupos.
 * 4. **Interfaz gráfica (`mostrarFormulario`, `cerrarFormulario`)**:
 *    - Muestra u oculta el formulario de creación de grupos según la acción del usuario.
 */


function mostrarFormulario() {
    document.getElementById('crearGrupoForm').style.display = 'block';
}

function cerrarFormulario() {
    document.getElementById('crearGrupoForm').style.display = 'none';
    document.getElementById('errorMensaje').style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function () {
    cargarGrupos();
});

function cargarGrupos() {
    fetch('../backend/dashboard.php')
        .then(response => response.json())
        .then(data => {
            let listaGrupos = document.getElementById("listaGrupos");
            listaGrupos.innerHTML = "";

            if (data.error) {
                listaGrupos.innerHTML = `<p class="error-message">${data.error}</p>`;
                return;
            }

            if (data.grupos.length === 0) {
                listaGrupos.innerHTML = "<p>No perteneces a ningún grupo.</p>";
                return;
            }

            data.grupos.forEach(grupo => {
                let li = document.createElement("li");
                li.textContent = grupo.nombre;
                li.classList.add("grupo-item");
                
                // ✅ Aseguramos que el ID del grupo se pase en la URL correctamente
                li.onclick = function () {
                    console.log(`🔹 Redirigiendo a panel_control.html con id_empresa=${grupo.id_empresa}`);
                    window.location.href = `../backend/panel_control.php?id_empresa=${grupo.id_empresa}`;
                };

                listaGrupos.appendChild(li);
            });
        })
        .catch(error => {
            console.error("Error al cargar los grupos:", error);
            document.getElementById("listaGrupos").innerHTML = `<p class="error-message">Error al cargar los grupos.</p>`;
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
