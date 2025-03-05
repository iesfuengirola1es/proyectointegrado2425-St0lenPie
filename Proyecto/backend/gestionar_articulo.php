<?php

/**
 * Módulo: Gestión de Artículos
 * 
 * Este script permite la gestión de artículos en la base de datos. Los usuarios pueden crear, editar,
 * obtener información, eliminar artículos y actualizar unidades vendidas. 
 * Se requiere autenticación y permisos específicos para cada acción.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_articulo.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ accion: 'crear', nombre: 'Laptop', precio: 500, stock: 10, nivel_minimo: 2, id_empresa: 1 }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.json()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `accion` (string) → Acción a realizar. Posibles valores: "crear", "editar", "obtener", "eliminar", "actualizar_unidades".
 * - `id_producto` (int) → ID del producto a gestionar (obligatorio para "editar", "obtener", "eliminar", "actualizar_unidades").
 * - `nombre` (string) → Nombre del producto. Obligatorio para "crear" y "editar".
 * - `descripcion` (string) → Descripción del producto (opcional).
 * - `precio` (float) → Precio del producto. Obligatorio para "crear" y "editar".
 * - `stock` (int) → Cantidad disponible del producto. Obligatorio para "crear" y "editar".
 * - `nivel_minimo` (int) → Nivel mínimo de stock antes de alerta. Obligatorio para "crear" y "editar".
 * - `unidades_vendidas` (int) → Unidades vendidas del producto. Obligatorio para "actualizar_unidades".
 * - `id_empresa` (int) → ID de la empresa a la que pertenece el producto. Obligatorio para "crear".
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado, requerido para validar permisos.
 *
 * Salida:
 * - `{"success": "Mensaje de éxito."}` → Si la operación fue exitosa.
 * - `{"error": "Mensaje de error."}` → Si ocurrió un error o no se tienen permisos suficientes.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene funciones para validar permisos del usuario.
 * - `productos` (tabla) → Contiene la información de los productos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con permisos sobre productos.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica la autenticación del usuario (`$_SESSION['user_id']`).
 * 2. Se valida que se haya recibido una acción válida en `$_POST['accion']` o `$_GET['accion']`.
 * 3. Dependiendo de la acción recibida:
 *    - **"crear"**: Verifica permisos, revisa si el producto ya existe en la empresa, y lo inserta en la base de datos.
 *    - **"editar"**: Verifica permisos, actualiza los datos del producto en la base de datos.
 *    - **"obtener"**: Verifica permisos y devuelve la información de un producto específico en formato JSON.
 *    - **"eliminar"**: Verifica permisos y elimina un producto de la base de datos.
 *    - **"actualizar_unidades"**: Verifica permisos y actualiza las unidades vendidas de un producto.
 * 4. Se retornan mensajes en formato JSON indicando éxito o error en la operación.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Acceso no autorizado."]));
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_producto = $_POST['id'] ?? $_GET['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? null;
$stock = $_POST['stock'] ?? null;
$nivel_minimo = $_POST['nivel_minimo'] ?? null;
$unidades_vendidas = $_POST['unidades_vendidas'] ?? 0;
$id_empresa = $_REQUEST['id_empresa'] ?? null;

error_log("🟡 Datos POST recibidos en `gestionar_articulo.php`: " . print_r($_POST, true));

try {
    if (!$accion) {
        die(json_encode(["error" => "No se ha especificado ninguna acción."]));
    }

    // ** Acción para CREAR un nuevo producto **
    if ($accion === "crear") {
        if (!usuarioTienePermiso("crear_articulos")) {
            die(json_encode(["error" => "No tienes permiso para crear artículos."]));
        }

        if (empty($nombre) || empty($precio) || empty($stock) || empty($nivel_minimo) || empty($id_empresa)) {
            die(json_encode(["error" => "Todos los campos son obligatorios."]));
        }

        // ** Verificar si ya existe un artículo con el mismo nombre en el mismo grupo **
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE nombre = ? AND id_empresa = ?");
        $stmt->execute([$nombre, $id_empresa]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            die(json_encode(["error" => "Ya existe un producto con este nombre en esta empresa."]));
        }

        $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, nivel_minimo, unidades_vendidas, id_empresa) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        $resultado = $stmt->execute([$nombre, $descripcion, $precio, $stock, $nivel_minimo, $unidades_vendidas , $id_empresa]);

        if ($resultado) {
            error_log("🟢 Producto agregado correctamente: $nombre en empresa ID: $id_empresa");
            echo json_encode(["success" => "Producto agregado exitosamente."]);
        } else {
            error_log("❌ Error al insertar el producto en la base de datos.");
            echo json_encode(["error" => "No se pudo agregar el producto."]);
        }
    }

    // ** Acción para EDITAR un producto existente **
    elseif ($accion === "editar" && $id_producto) {
        if (!usuarioTienePermiso("editar_articulos")) {
            die(json_encode(["error" => "No tienes permiso para editar artículos."]));
        }

        if (empty($nombre) || empty($precio) || empty($stock) || empty($nivel_minimo)) {
            die(json_encode(["error" => "Todos los campos son obligatorios para editar un producto."]));
        }

        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, nivel_minimo=?,unidades_vendidas=? WHERE id_producto=?");
        $resultado = $stmt->execute([$nombre, $descripcion, $precio, $stock, $nivel_minimo, $unidades_vendidas,$id_producto]);

        if ($resultado) {
            error_log("🟢 Producto con ID $id_producto actualizado correctamente.");
            echo json_encode(["success" => "Producto actualizado correctamente."]);
        } else {
            error_log("❌ Error al actualizar el producto en la base de datos.");
            echo json_encode(["error" => "No se pudo actualizar el producto."]);
        }
    }

    // ** Acción para OBTENER un producto existente (para editar) **
    elseif ($accion === "obtener" && $id_producto) {
        if (!usuarioTienePermiso("editar_articulos")) {
            die(json_encode(["error" => "No tienes permiso para editar artículos."]));
        }

        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            echo json_encode($producto);
        } else {
            error_log("❌ Producto no encontrado en la base de datos.");
            echo json_encode(["error" => "Producto no encontrado."]);
        }
    }

    // ** Acción para ELIMINAR un producto **
    elseif ($accion === "eliminar" && $id_producto) {
        if (!usuarioTienePermiso("eliminar_articulos")) {
            die(json_encode(["error" => "No tienes permiso para eliminar artículos."]));
        }

        $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);

        error_log("🟢 Producto con ID $id_producto eliminado correctamente.");
        echo json_encode(["success" => "Producto eliminado correctamente."]);
    }

    // ** Acción para ACTUALIZAR unidades vendidas **
    elseif ($accion === "actualizar_unidades" && $id_producto) {
        if (!usuarioTienePermiso("gestionar_unidades")) {
            die(json_encode(["error" => "No tienes permiso para actualizar unidades vendidas."]));
        }

        $stmt = $pdo->prepare("UPDATE productos SET unidades_vendidas = ? WHERE id_producto = ?");
        $stmt->execute([$unidades_vendidas, $id_producto]);

        error_log("🟢 Unidades vendidas actualizadas para producto ID: $id_producto.");
        echo json_encode(["success" => "Unidades vendidas actualizadas."]);
    } else {
        error_log("❌ Error: Acción no válida. Acción recibida: $accion");
        echo json_encode(["error" => "Acción no válida."]);
    }

} catch (PDOException $e) {
    error_log("❌ Error en la operación: " . $e->getMessage());
    echo json_encode(["error" => "Error en la operación."]);
}
?>
