<?php
session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? null;
$id_empresa = $_SESSION['id_empresa'] ?? null;
$email = $_GET['email'] ?? '';

try {
    if ($accion === "buscar_usuarios") {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE email LIKE ?");
        $stmt->execute(["%$email%"]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuarios);
    } elseif ($accion === "agregar_usuario" && $id_usuario && $id_empresa) {

          if (!usuarioTienePermiso("gestionar_personas")) {
            die("error: No tienes permiso para crear usuarios.");
          }
        // Verificar si el usuario ya está en el grupo
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_grupos WHERE id_usuario = ? AND id_empresa = ?");
        $stmt->execute([$id_usuario, $id_empresa]);
    
        if ($stmt->fetchColumn() > 0) {
            die("error: El usuario ya pertenece al grupo.");
        }
    
       
        //rol usuario nuevo
        $id_rol_asignado = 2;
    

// Insertar relación en usuarios_grupos con el rol de creador
    $stmt = $pdo->prepare("INSERT INTO usuarios_grupos (id_usuario, id_empresa, rol) VALUES (?, ?, ?)");
    $stmt->execute([$id_usuario, $id_empresa,$id_rol_asignado]);

    
        error_log("🟢 Usuario ID $id_usuario agregado al grupo ID $id_empresa con rol ID $id_rol_asignado.");
        echo "success: Usuario agregado al grupo con rol asignado.";
            
    } elseif ($accion === "eliminar_usuario" && $id_usuario && $id_empresa) {
        if (!usuarioTienePermiso("gestionar_personas")) {
            die("error: No tienes permiso para eliminar usuarios.");
          }
        // Verificar si el usuario es el creador del grupo
        $stmt = $pdo->prepare("SELECT id_creador FROM empresa WHERE id_empresa = ? ");
        $stmt->execute([$id_empresa]);
        $creador = $stmt->fetchColumn();

        if ($id_usuario == $creador) {
            die("error: No puedes eliminar al creador del grupo.");
        }

        $stmt = $pdo->prepare("DELETE FROM usuarios_grupos WHERE id_empresa=? AND id_usuario=?");
        $resultado = $stmt->execute([$id_empresa,$id_usuario]);

    
        if ($resultado) {
            error_log("🟢 Rol del usuario ID $id_usuario actualizado a ID de rol $id_rol.");
            echo "success: Usuario eliminado correctamente.";
        } else {
            error_log("❌ Error al eliminar usuario ID $id_usuario.");
            echo "error: No se pudo eliminar.";
        }
       


    } elseif ($accion === "cambiar_rol") {
         if (!usuarioTienePermiso("gestionar_personas")) {
            die("error: No tienes permiso para modificar roles.");
          }
          // Verificar si el usuario es el creador del grupo
        $stmt = $pdo->prepare("SELECT id_creador FROM empresa WHERE id_empresa = ? ");
        $stmt->execute([$id_empresa]);
        $creador = $stmt->fetchColumn();

        if ($id_usuario == $creador) {
            die("error: No puedes modificar al creador del grupo.");
        }

        error_log("🟡 Solicitud de cambio de rol recibida en gestionar_personas.php");
        error_log("🔍 Datos recibidos: " . print_r($_POST, true));
    
        if (!isset($_POST['id_usuario']) || !isset($_POST['id_rol'])) {
            die("error: ID de usuario o ID de rol no válido.");
        }
    
        $id_usuario = $_POST['id_usuario'];
        $id_rol = $_POST['id_rol'];
    
        error_log("🔹 ID Usuario: $id_usuario - Nuevo ID Rol: $id_rol");
    
        $stmt = $pdo->prepare("UPDATE usuarios_grupos SET rol = ? WHERE id_usuario = ? and id_empresa=?");
        $resultado = $stmt->execute([$id_rol, $id_usuario,$id_empresa]);
    
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
