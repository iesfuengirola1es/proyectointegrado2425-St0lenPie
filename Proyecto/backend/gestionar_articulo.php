<?php
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
