/*document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Script `inventario.js` cargado correctamente.");

    setTimeout(() => {
        let botonGuardar = document.getElementById("botonGuardarProducto");
        if (botonGuardar) {
            botonGuardar.addEventListener("click", function () {
                console.log("🟢 Botón de Guardar presionado.");
                guardarProducto();
            });
            console.log("🟢 Evento de Guardar asignado.");
        } else {
            console.error("❌ Botón de Guardar no encontrado en el DOM. Verifica que el formulario de productos esté en `inventario.php`.");
        }
    }, 1000); // Espera 1 segundo para asegurarse de que el botón ya está cargado
});
*/
/*

function cargarSeccion(seccion) {
    $(".content-area").html("<h2>Cargando " + seccion + "...</h2>");
    $.get("../backend/" + seccion + ".php?id=" + obtenerGrupoId(), function (data) {
        $(".content-area").html(data);
    });
}
*/
function mostrarFormulario() {
    $("#formularioProducto").show();
    $("#tituloFormulario").text("Añadir Producto");
    $("#productoID").val("");
    $("#nombreProducto").val("");
    $("#descripcionProducto").val("");
    $("#precioProducto").val("");
    $("#stockProducto").val("");
    $("#nivelMinimoProducto").val("");
    $("#unidadesVendidasProducto").val("");

        // Asegurar que el botón de guardar tenga un evento asociado
    /*let botonGuardar = document.getElementById("botonGuardarProducto");
    if (botonGuardar) {
        botonGuardar.removeEventListener("click", guardarProducto); // Elimina eventos duplicados
        botonGuardar.addEventListener("click", guardarProducto);
        console.log("🟢 Evento de Guardar asignado al mostrar el formulario.");
    } else {
        console.error("❌ No se encontró el botón de Guardar dentro del formulario.");
    }*/
}

function cerrarFormulario() {
    $("#formularioProducto").hide();
}

function mostrarMensaje(mensaje, tipo) {
    $("#mensajeRespuesta").text(mensaje).removeClass().addClass(tipo).show();
}

function guardarProducto() {
    let id = $("#productoID").val();
    let accion = id ? "editar" : "crear";
    let nombre = $("#nombreProducto").val();
    let descripcion = $("#descripcionProducto").val();
    let precio = $("#precioProducto").val();
    let stock = $("#stockProducto").val();
    let nivelMinimo = $("#nivelMinimoProducto").val();
    let unidadesVendidas = $("#unidadesVendidasProducto").val();
    let grupoID = obtenerGrupoId();

    if (nombre.trim() === "" || precio === "" || stock === "" || nivelMinimo === "") {
        mostrarMensaje("Todos los campos son obligatorios.", "error");
        return;
    }

    $.post("../backend/gestionar_articulo.php", {
        accion: accion,
        id: id,
        nombre: nombre,
        descripcion: descripcion,
        precio: precio,
        stock: stock,
        nivel_minimo: nivelMinimo,
        unidades_vendidas: unidadesVendidas,
        id_empresa: grupoID
    }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('inventario');
            cerrarFormulario();
        }
    }).fail(function() {
        mostrarMensaje("Error en la solicitud al servidor.", "error");
    });
}


function editarProducto(id) {
    console.log("🟡 Solicitando datos para editar el producto con ID:", id);

    $.get("../backend/gestionar_articulo.php", { accion: "obtener", id: id,id_empresa:obtenerGrupoId() }, function(data) {
        let producto;
        try {
            producto = JSON.parse(data);
        } catch (error) {
            console.error("❌ Error al procesar los datos del producto:", error);
            mostrarMensaje("Error al obtener los datos del producto.", "error");
            return;
        }

        if (!producto || !producto.id_producto) {
            console.error("❌ No se encontró el producto.");
            mostrarMensaje("Error: No se pudo obtener la información del producto.", "error");
            return;
        }

        console.log("🟢 Datos del producto recibidos:", producto);

        $("#formularioProducto").show();
        $("#tituloFormulario").text("Editar Producto");
        $("#productoID").val(producto.id_producto);
        $("#nombreProducto").val(producto.nombre);
        $("#descripcionProducto").val(producto.descripcion);
        $("#precioProducto").val(producto.precio);
        $("#stockProducto").val(producto.stock);
        $("#nivelMinimoProducto").val(producto.nivel_minimo);
        $("#unidadesVendidasProducto").val(producto.unidades_vendidas);

        // Asegurar que el botón de guardar funcione correctamente
        let botonGuardar = document.getElementById("botonGuardarProducto");
        if (botonGuardar) {
            botonGuardar.removeEventListener("click", guardarProducto);
            botonGuardar.addEventListener("click", guardarProducto);
            console.log("🟢 Evento de Guardar asignado al botón después de editar.");
        } else {
            console.error("❌ No se encontró el botón de Guardar.");
        }
    }).fail(function() {
        console.error("❌ Error al obtener los datos del producto.");
        mostrarMensaje("Error al obtener los datos del producto.", "error");
    });
}



function eliminarProducto(id) {
    if (!confirm("¿Estás seguro de eliminar este producto?")) return;

    $.post("../backend/gestionar_articulo.php", { accion: "eliminar", id: id,
        id_empresa: obtenerGrupoId() }, function(response) {
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('inventario');
        }
    }).fail(function() {
        mostrarMensaje("Error al eliminar el producto.", "error");
    });
}

function actualizarUnidadesVendidas(id) {
    let nuevasUnidades = $("#unidadesVendidas_" + id).val();

    console.log("🟡 Actualizando unidades vendidas del producto ID:", id, "con valor:", nuevasUnidades);

    $.post("../backend/gestionar_articulo.php", {
        accion: "actualizar_unidades",
        id: id,
        unidades_vendidas: nuevasUnidades,
        id_empresa: obtenerGrupoId()
    }, function (response) {
        console.log("🟢 Respuesta del servidor al actualizar unidades:", response);
        if (response.includes("error:")) {
            mostrarMensaje(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('inventario');
        }
    });
}
