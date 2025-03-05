<?php

/**
 * Módulo: Obtención de Información de un Grupo
 * 
 * Este script permite obtener el nombre de un grupo empresarial a partir de su ID.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('obtener_grupo.php?id=1', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.json()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `id` (int) → ID del grupo a consultar. Obligatorio.
 *
 * Salida:
 * - `{"nombre": "Grupo Empresarial XYZ"}` → Si el grupo existe.
 * - `{"error": "ID de grupo no proporcionado"}` → Si no se envía un `id`.
 * - `{"error": "Grupo no encontrado"}` → Si el ID no corresponde a un grupo existente.
 * - `{"error": "Error al obtener el grupo: <mensaje>"}` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `empresa` (tabla) → Contiene la información de los grupos empresariales.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se recibe y valida el parámetro `id` de la URL.
 * 2. Si no se proporciona un `id`, se devuelve un mensaje de error en formato JSON.
 * 3. Se ejecuta una consulta en la base de datos para obtener el nombre del grupo.
 * 4. Si el grupo existe, se devuelve un JSON con el nombre del grupo.
 * 5. Si el grupo no se encuentra, se devuelve un mensaje de error.
 * 6. Si ocurre un error en la base de datos, se captura la excepción y se devuelve un mensaje de error.
 */

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
