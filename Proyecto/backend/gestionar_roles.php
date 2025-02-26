<?php
session_start();
require 'config.php';
require 'verificar_permisos.php'; // Importar función para validar permisos

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_rol = $_POST['id'] ?? $_GET['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$permisos = json_decode($_POST['permisos'] ?? '[]', true);

try {
    if ($accion === "crear") {
        if (!usuarioTienePermiso("crear_roles")) {
            die("error: No tienes permiso para crear roles.");
        }
        
        if (empty($nombre) || empty($permisos)) {
            die("error: Todos los campos son obligatorios.");
        }

        $stmt = $pdo->prepare("INSERT INTO roles (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        $id_rol = $pdo->lastInsertId();

        foreach ($permisos as $permiso) {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM permisos WHERE id_permiso = ?");
            $stmtCheck->execute([$permiso]);

            if ($stmtCheck->fetchColumn() == 0) {
                die("error: Permiso inválido.");
            }

            $stmt = $pdo->prepare("INSERT INTO roles_permisos (id_rol, id_permiso) VALUES (?, ?)");
            $stmt->execute([$id_rol, $permiso]);
        }

        echo "success: Rol creado exitosamente.";
    } elseif ($accion === "editar") {
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para modificar roles.");
        }

        if (empty($nombre) || empty($permisos) || !$id_rol) {
            die("error: Todos los campos son obligatorios.");
        }

        $stmt = $pdo->prepare("UPDATE roles SET nombre=? WHERE id_rol=?");
        $stmt->execute([$nombre, $id_rol]);

        $stmt = $pdo->prepare("DELETE FROM roles_permisos WHERE id_rol=?");
        $stmt->execute([$id_rol]);

        foreach ($permisos as $permiso) {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM permisos WHERE id_permiso = ?");
            $stmtCheck->execute([$permiso]);

            if ($stmtCheck->fetchColumn() == 0) {
                die("error: Permiso inválido.");
            }

            $stmt = $pdo->prepare("INSERT INTO roles_permisos (id_rol, id_permiso) VALUES (?, ?)");
            $stmt->execute([$id_rol, $permiso]);
        }

        echo "success: Rol actualizado correctamente.";
    } elseif ($accion === "eliminar") {
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para eliminar roles.");
        }

        // Asegurar que el rol no está en uso antes de eliminarlo
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_rol = ?");
        $stmtCheck->execute([$id_rol]);
        if ($stmtCheck->fetchColumn() > 0) {
            die("error: No puedes eliminar un rol que está asignado a usuarios.");
        }

        $stmt = $pdo->prepare("DELETE FROM roles WHERE id_rol=?");
        $stmt->execute([$id_rol]);

        $stmt = $pdo->prepare("DELETE FROM roles_permisos WHERE id_rol=?");
        $stmt->execute([$id_rol]);

        echo "success: Rol eliminado correctamente.";
    } elseif ($accion === "obtener") {
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para ver roles.");
        }

        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id_rol = ?");
        $stmt->execute([$id_rol]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtPermisos = $pdo->prepare("SELECT id_permiso FROM roles_permisos WHERE id_rol = ?");
        $stmtPermisos->execute([$id_rol]);
        $rol['permisos'] = $stmtPermisos->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($rol);
    } elseif ($accion === "listar_permisos") {
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para ver permisos.");
        }

        $stmt = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
