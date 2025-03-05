/**
 * Módulo: Gestión de Productos en el Inventario
 * 
 * Este script permite la gestión de productos dentro del inventario de una empresa. 
 * Los usuarios pueden agregar, editar, eliminar productos y actualizar unidades vendidas.
 *
 * Ejemplo de llamada:
 * -------------------
 * guardarProducto(); // Guarda un nuevo producto o edita uno existente.
 * editarProducto(1); // Carga los datos del producto con ID 1 para editar.
 * eliminarProducto(1); // Elimina el producto con ID 1 tras confirmación del usuario.
 * actualizarUnidadesVendidas(1); // Actualiza la cantidad de unidades vendidas del producto con ID 1.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `mostrarFormulario()`: Muestra el formulario para agregar un nuevo producto.
 * - `cerrarFormulario()`: Oculta el formulario de productos.
 * - `mostrarMensaje(mensaje, tipo)`: Muestra un mensaje de estado en la interfaz.
 * - `guardarProducto()`: Guarda un nuevo producto o edita uno existente en el inventario.
 * - `editarProducto(id)`: Carga los datos de un producto específico para su edición.
 * - `eliminarProducto(id)`: Elimina un producto tras confirmación del usuario.
 * - `actualizarUnidadesVendidas(id)`: Permite modificar la cantidad de unidades vendidas de un producto.
 *
 * Dependencias:
 * -------------
 * - `gestionar_articulo.php` → Backend para manejar la base de datos de productos.
 * - jQuery (`$`) → Se utiliza para manejar eventos y solicitudes AJAX.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Creación y edición de productos (`guardarProducto`)**:
 *    - Determina si la acción es "crear" o "editar" en función del ID del producto.
 *    - Valida los datos ingresados.
 *    - Envía la solicitud a `gestionar_articulo.php` para guardar los cambios.
 *    - Si la operación es exitosa, recarga la sección de inventario y cierra el formulario.
 * 2. **Carga de producto para edición (`editarProducto`)**:
 *    - Obtiene los datos del producto a editar desde `gestionar_articulo.php`.
 *    - Llena los campos del formulario con la información del producto.
 *    - Asegura que el botón de guardar esté vinculado correctamente a la función `guardarProducto`.
 * 3. **Eliminación de productos (`eliminarProducto`)**:
 *    - Solicita confirmación al usuario antes de eliminar un producto.
 *    - Realiza una solicitud a `gestionar_articulo.php` con la acción "eliminar".
 *    - Si la operación es exitosa, recarga la sección de inventario.
 * 4. **Actualización de unidades vendidas (`actualizarUnidadesVendidas`)**:
 *    - Toma el nuevo valor de unidades vendidas del input correspondiente.
 *    - Envía la actualización a `gestionar_articulo.php`.
 *    - Muestra mensajes de éxito o error según la respuesta del servidor.
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
}

function cerrarFormulario() {
    $("#formularioProducto").hide();
}

function mostrarMensaje(mensaje, tipo) {
    $("#mensajeRespuesta").text(mensaje).removeClass().addClass(tipo).show();
}
function mostrarMensajeEditar(mensaje, tipo) {
    $("#mensajeRespuestaEditar").text(mensaje).removeClass().addClass(tipo).show();
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
        mostrarMensajeEditar("Todos los campos son obligatorios.", "error");
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
            mostrarMensajeEditar(response.replace("error:", ""), "error");
        } else {
            mostrarMensajeEditar(response.replace("success:", ""), "success");
            cargarSeccion('inventario');
            cerrarFormulario();
        }
    }).fail(function() {
        mostrarMensajeEditar("Error en la solicitud al servidor.", "error");
    });
}


function editarProducto(id) {
    console.log("🟡 Solicitando datos para editar el producto con ID:", id);

    $.get("../backend/gestionar_articulo.php", { accion: "obtener", id: id }, function(data) {
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
        mostrarMensajeEditar("Error al obtener los datos del producto.", "error");
    });
}



function eliminarProducto(id) {
    if (!confirm("¿Estás seguro de eliminar este producto?")) return;

    $.post("../backend/gestionar_articulo.php", { accion: "eliminar", id: id,
       }, function(response) {
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
        unidades_vendidas: nuevasUnidades
    }, function (response) {
        console.log("🟢 Respuesta del servidor al actualizar unidades:", response);
        if (response.includes("error:")) {
            mostrarMensajeEditar(response.replace("error:", ""), "error");
        } else {
            mostrarMensaje(response.replace("success:", ""), "success");
            cargarSeccion('inventario');
        }
    });
}
