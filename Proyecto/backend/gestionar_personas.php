<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? null;
$id_empresa = $_POST['id_empresa'] ?? null;
$email = $_GET['email'] ?? '';

try {
    if ($accion === "buscar_usuarios") {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE email LIKE ?");
        $stmt->execute(["%$email%"]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuarios);
    } elseif ($accion === "agregar_usuario" && $id_usuario && $id_empresa) {
        // Verificar si el usuario ya está en el grupo
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = ? AND id_empresa = ?");
        $stmt->execute([$id_usuario, $id_empresa]);
    
        if ($stmt->fetchColumn() > 0) {
            die("error: El usuario ya pertenece al grupo.");
        }
    
        // Obtener el ID del creador del grupo
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_empresa = ? AND id_rol = 1");
        $stmt->execute([$id_empresa]);
        $id_creador = $stmt->fetchColumn();
    
        // Si el usuario agregado NO es el creador, asignarle el rol "Usuario Nuevo" (id_rol = 2)
        $id_rol_asignado = ($id_usuario == $id_creador) ? 1 : 2;
    
        // Asignar el grupo y el rol al usuario
        $stmt = $pdo->prepare("UPDATE usuarios SET id_empresa = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt->execute([$id_empresa, $id_rol_asignado, $id_usuario]);
    
        error_log("🟢 Usuario ID $id_usuario agregado al grupo ID $id_empresa con rol ID $id_rol_asignado.");
        echo "success: Usuario agregado al grupo con rol asignado.";
            
    } elseif ($accion === "eliminar_usuario" && $id_usuario && $id_empresa) {
        // Verificar si el usuario es el creador del grupo
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_empresa = ? ORDER BY id_usuario ASC LIMIT 1");
        $stmt->execute([$id_empresa]);
        $creador = $stmt->fetchColumn();

        if ($id_usuario == $creador) {
            die("error: No puedes eliminar al creador del grupo.");
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET id_empresa = NULL WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        echo "success: Usuario eliminado del grupo.";
    } elseif ($accion === "cambiar_rol") {
        error_log("🟡 Solicitud de cambio de rol recibida en gestionar_personas.php");
        error_log("🔍 Datos recibidos: " . print_r($_POST, true));
    
        if (!isset($_POST['id_usuario']) || !isset($_POST['id_rol'])) {
            die("error: ID de usuario o ID de rol no válido.");
        }
    
        $id_usuario = $_POST['id_usuario'];
        $id_rol = $_POST['id_rol'];
    
        error_log("🔹 ID Usuario: $id_usuario - Nuevo ID Rol: $id_rol");
    
        $stmt = $pdo->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
        $resultado = $stmt->execute([$id_rol, $id_usuario]);
    
        if ($resultado) {
            error_log("🟢 Rol del usuario ID $id_usuario actualizado a ID de rol $id_rol.");
            echo "success: Rol cambiado correctamente.";
        } else {
            error_log("❌ Error al cambiar el rol del usuario ID $id_usuario.");
            echo "error: No se pudo cambiar el rol.";
        }
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
