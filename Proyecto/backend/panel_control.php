<?php
session_start();
require __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_SESSION['user_id'];
    $id_empresa = $_GET['id_empresa'];
    

    try {
        // Preparar la consulta con PDO
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_grupos WHERE id_empresa = ? and id_usuario = ?");

        $stmt->execute([$id_empresa,$user_id]);

           
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
             $_SESSION['id_empresa'] = $id_empresa;
            header("Location: ../frontend/panel_control.html?id_empresa=".$id_empresa);
            exit();
        }else{
            $_SESSION['error'] = "Este usuario no tiene permisos para esta empresa";
           
        }


       
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }
    
    header("Location: ../frontend/dashboard.html");
    exit();
}
?>