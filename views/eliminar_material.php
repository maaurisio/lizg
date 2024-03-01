<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";
session_start();

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

if (!$idUsuario) {
    header("Location: ../index.php");
}

// Verificar si se proporcionó un código de material y un ID de proyecto válidos
if (isset($_GET['codigo']) && isset($_GET['idProyecto'])) {
    $codigoMaterial = $_GET['codigo'];
    $idProyecto = $_GET['idProyecto'];

    // Consulta SQL para eliminar el material del proyecto
    $sql_eliminar_material = "DELETE FROM detalle WHERE codigoMaterial = ? AND idProyecto = ?";
    $stmt_eliminar_material = $conn->prepare($sql_eliminar_material);
    $stmt_eliminar_material->bind_param("ii", $codigoMaterial, $idProyecto);

    // Ejecutar la consulta
    if ($stmt_eliminar_material->execute()) {
        // Redirigir de vuelta a la página del proyecto
        header("Location: informacion_proyecto.php?id=" . $idProyecto);
        exit();
    } else {
        echo "Error al intentar eliminar el material.";
    }
} else {
    echo "No se proporcionó un código de material y un ID de proyecto válidos.";
}
