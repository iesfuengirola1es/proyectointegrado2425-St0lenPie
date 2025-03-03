<?php
require 'config.php';

$grupo_id = $_GET['id'] ?? null;

if (!$grupo_id) {
    echo json_encode(["error" => "ID de grupo no proporcionado"]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT nombre FROM empresa WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($grupo) {
        echo json_encode(["nombre" => $grupo["nombre"]]);
    } else {
        echo json_encode(["error" => "Grupo no encontrado"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener el grupo: " . $e->getMessage()]);
}
?>
