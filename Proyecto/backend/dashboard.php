<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Acceso no autorizado."]));
}

$user_id = $_SESSION['user_id'];

try {
    // Obtener grupos donde el usuario es creador o donde ha sido invitado
    $stmt = $pdo->prepare("
        SELECT DISTINCT empresa.id_empresa, empresa.nombre 
        FROM empresa
        LEFT JOIN usuarios ON empresa.id_empresa = usuarios.id_empresa AND usuarios.id_usuario = ?
        LEFT JOIN usuarios_grupos ON empresa.id_empresa = usuarios_grupos.id_empresa AND usuarios_grupos.id_usuario = ?
        WHERE usuarios.id_usuario IS NOT NULL OR usuarios_grupos.id_usuario IS NOT NULL
    ");
    
    $stmt->execute([$user_id, $user_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["grupos" => $grupos]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener grupos: " . $e->getMessage()]);
}
?>
