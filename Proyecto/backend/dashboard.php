<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$user_id = $_SESSION['user_id'];

try {
    // Obtener la lista de grupos a los que pertenece el usuario
    $stmt = $pdo->prepare("SELECT empresa.id_empresa, empresa.nombre, usuarios_grupos.rol FROM empresa 
                           INNER JOIN usuarios_grupos ON empresa.id_empresa = usuarios_grupos.id_empresa 
                           WHERE usuarios_grupos.id_usuario = ?");
    $stmt->execute([$user_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($grupos) > 0) {
        foreach ($grupos as $grupo) {
            echo "<li>
                    <a href='panel_control.html?id_empresa={$grupo['id_empresa']}'>{$grupo['nombre']} ({$grupo['rol']})</a>
                    <button onclick='eliminarGrupo({$grupo['id_empresa']})'>🗑 Eliminar</button>
                  </li>";
        }
    } else {
        echo "<p>No tienes grupos creados.</p>";
    }
} catch (PDOException $e) {
    echo "error: Error al obtener la lista de grupos: " . $e->getMessage();
}
?>
