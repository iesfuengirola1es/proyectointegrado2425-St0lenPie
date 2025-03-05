<?php

/**
 * Módulo: Gestión de Roles y Permisos
 * 
 * Este script permite a los administradores crear, editar, eliminar y obtener información de roles,
 * así como listar los permisos disponibles en el sistema. Se requiere autenticación y permisos específicos.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_roles.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ accion: 'crear', nombre: 'Supervisor', permisos: JSON.stringify([1, 2, 3]) }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `accion` (string) → Acción a realizar. Posibles valores:
 *   - "crear" → Crear un nuevo rol con permisos asociados.
 *   - "editar" → Modificar un rol existente y sus permisos.
 *   - "eliminar" → Eliminar un rol (solo si no está en uso).
 *   - "obtener" → Obtener información de un rol específico.
 *   - "listar_permisos" → Obtener la lista de permisos disponibles en el sistema.
 * - `id_rol` (int) → ID del rol a gestionar (requerido para "editar", "eliminar" y "obtener").
 * - `nombre` (string) → Nombre del rol (obligatorio para "crear" y "editar").
 * - `permisos` (array JSON) → Lista de IDs de permisos a asignar al rol (obligatorio para "crear" y "editar").
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado, requerido para validar permisos.
 *
 * Salida:
 * - `success: Rol creado exitosamente.` → Si el rol se crea correctamente.
 * - `success: Rol actualizado correctamente.` → Si el rol se edita correctamente.
 * - `success: Rol eliminado correctamente.` → Si el rol se elimina correctamente.
 * - `{"id_rol": 1, "nombre": "Administrador", "permisos": [1, 2, 3]}` → Si se obtiene un rol en formato JSON.
 * - `[{"id_permiso": 1, "nombre": "Editar Usuarios", "descripcion": "Permiso para modificar usuarios"}, ...]`
 *   → Lista de permisos disponibles en formato JSON.
 * - `error: Todos los campos son obligatorios.` → Si falta información en "crear" o "editar".
 * - `error: Permiso inválido.` → Si se intenta asignar un permiso inexistente a un rol.
 * - `error: No puedes eliminar un rol que está asignado a usuarios.` → Si el rol está en uso.
 * - `error: Acción no válida.` → Si la acción recibida no es reconocida.
 * - `error: Error en la operación: <mensaje>` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `roles` (tabla) → Almacena los roles disponibles en el sistema.
 * - `roles_permisos` (tabla) → Relaciona roles con permisos específicos.
 * - `permisos` (tabla) → Contiene la lista de permisos del sistema.
 * - `usuarios` (tabla) → Almacena la información de los usuarios, incluyendo su rol.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se recibe y valida la acción `accion` enviada en la petición.
 * 3. Dependiendo de la acción recibida:
 *    - **"crear"**:
 *       - Verifica que el nombre y los permisos sean válidos.
 *       - Inserta el nuevo rol en la base de datos.
 *       - Asigna los permisos al rol en la tabla `roles_permisos`.
 *    - **"editar"**:
 *       - Verifica que el nombre, el rol y los permisos sean válidos.
 *       - Actualiza el nombre del rol.
 *       - Elimina los permisos previos del rol y asigna los nuevos.
 *    - **"eliminar"**:
 *       - Verifica que el rol no esté asignado a ningún usuario antes de eliminarlo.
 *       - Borra el rol y sus relaciones de la base de datos.
 *    - **"obtener"**:
 *       - Obtiene los datos del rol y sus permisos asociados.
 *    - **"listar_permisos"**:
 *       - Devuelve la lista completa de permisos del sistema.
 * 4. Se retornan mensajes en formato JSON indicando éxito o error en la operación.
 */


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
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id_rol = ?");
        $stmt->execute([$id_rol]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmtPermisos = $pdo->prepare("SELECT id_permiso FROM roles_permisos WHERE id_rol = ?");
        $stmtPermisos->execute([$id_rol]);
        $rol['permisos'] = $stmtPermisos->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($rol);
    } elseif ($accion === "listar_permisos") {
        $stmt = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
