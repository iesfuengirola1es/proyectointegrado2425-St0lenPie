<?php

/**
 * Módulo: Gestión de Usuarios en un Grupo
 * 
 * Este script permite a los administradores buscar usuarios, agregarlos a un grupo, eliminarlos y cambiar sus roles.
 * Se requiere autenticación y permisos específicos para cada acción.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_personas.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ accion: 'agregar_usuario', id_usuario: 5, id_empresa: 1 }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `accion` (string) → Acción a realizar. Posibles valores:
 *   - "buscar_usuarios" → Buscar usuarios por correo.
 *   - "agregar_usuario" → Agregar un usuario a un grupo.
 *   - "eliminar_usuario" → Eliminar un usuario de un grupo.
 *   - "cambiar_rol" → Cambiar el rol de un usuario dentro de un grupo.
 * - `id_usuario` (int) → ID del usuario a gestionar (requerido para agregar, eliminar o cambiar rol).
 * - `id_empresa` (int) → ID del grupo en el que se gestiona el usuario (requerido para todas las acciones excepto "buscar_usuarios").
 * - `email` (string) → Email del usuario a buscar (solo para "buscar_usuarios").
 * - `id_rol` (int) → ID del nuevo rol a asignar al usuario (requerido para "cambiar_rol").
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado, requerido para validar permisos.
 *
 * Salida:
 * - `success: Usuario agregado al grupo con rol asignado.` → Si el usuario se agrega correctamente.
 * - `success: Usuario eliminado correctamente.` → Si el usuario es eliminado del grupo.
 * - `success: Rol cambiado correctamente.` → Si el rol del usuario se actualiza.
 * - `error: No tienes permiso para realizar esta acción.` → Si el usuario no tiene permisos suficientes.
 * - `error: El usuario ya pertenece al grupo.` → Si el usuario ya es miembro del grupo.
 * - `error: No puedes eliminar/modificar al creador del grupo.` → Restricción para el creador del grupo.
 * - `error: Acción no válida.` → Si la acción recibida no es reconocida.
 * - `error: Error en la operación: <mensaje>` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `usuarios` (tabla) → Almacena la información de los usuarios.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos y roles.
 * - `empresa` (tabla) → Contiene los datos de los grupos empresariales.
 * - `roles` (tabla) → Contiene la lista de roles del sistema.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se recibe y valida la acción `accion` enviada en la petición.
 * 3. Dependiendo de la acción recibida:
 *    - **"buscar_usuarios"**: Busca usuarios en la base de datos filtrando por email.
 *    - **"agregar_usuario"**: 
 *       - Verifica permisos.
 *       - Revisa si el usuario ya pertenece al grupo.
 *       - Asigna el rol predeterminado y lo agrega a la tabla `usuarios_grupos`.
 *    - **"eliminar_usuario"**:
 *       - Verifica permisos.
 *       - Revisa que el usuario no sea el creador del grupo.
 *       - Elimina la relación en `usuarios_grupos`.
 *    - **"cambiar_rol"**:
 *       - Verifica permisos.
 *       - Revisa que el usuario no sea el creador del grupo.
 *       - Actualiza el rol en `usuarios_grupos`.
 * 4. Se retornan mensajes en formato JSON indicando éxito o error en la operación.
 */


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
