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
                    window.location.href = `panel_control.html?id_empresa=${grupo.id_empresa}`;
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
