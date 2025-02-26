<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_servicio = $_POST['id'] ?? $_GET['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? null;
$id_empresa = $_POST['id_empresa'] ?? null;

try {
    if ($accion === "crear" || $accion === "editar") {
        if (empty($nombre) || empty($precio)) {
            die("error: Todos los campos son obligatorios.");
        }

        // **Validación de nombre duplicado**
        if ($accion === "crear") {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE nombre = ? AND id_empresa = ?");
            $stmt->execute([$nombre, $id_empresa]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE nombre = ? AND id_empresa = ? AND id_servicio != ?");
            $stmt->execute([$nombre, $id_empresa, $id_servicio]);
        }

        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            die("error: Ya existe un servicio con este nombre en esta empresa.");
        }
    }

    if ($accion === "crear") {
        $stmt = $pdo->prepare("INSERT INTO servicios (nombre, descripcion, precio, id_empresa) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $id_empresa]);
        echo "success: Servicio agregado exitosamente.";
    } elseif ($accion === "editar" && $id_servicio) {
        $stmt = $pdo->prepare("UPDATE servicios SET nombre=?, descripcion=?, precio=? WHERE id_servicio=?");
        $stmt->execute([$nombre, $descripcion, $precio, $id_servicio]);
        echo "success: Servicio actualizado correctamente.";
    } elseif ($accion === "eliminar" && $id_servicio) {
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        echo "success: Servicio eliminado correctamente.";
    } elseif ($accion === "obtener" && $id_servicio) {
        $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($servicio) {
            echo json_encode($servicio);
        } else {
            echo "error: Servicio no encontrado.";
        }
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
