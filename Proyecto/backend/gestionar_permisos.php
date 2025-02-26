<?php
session_start();
require 'config.php';
require 'verificar_permisos.php'; // Importar función para validar permisos

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;

try {
    if ($accion === "listar_permisos") {
        // Verificar si el usuario tiene permiso para ver permisos
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para ver los permisos.");
        }

        // Obtener todos los permisos disponibles en el sistema
        $stmt = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
