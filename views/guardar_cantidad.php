<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibió el ID del proyecto y al menos un campo de cantidad
    if (isset($_POST['idProyecto']) && !empty($_POST['idProyecto'])) {
        $idProyecto = $_POST['idProyecto'];
        $materiales = array();

        // Recorrer los campos de cantidad
        foreach ($_POST as $key => $value) {
            // Verificar si el campo es de cantidad (tiene el prefijo "cantidad_")
            if (strpos($key, 'cantidad_') !== false) {
                // Obtener el código de material desde el nombre del campo
                $codigoMaterial = substr($key, strlen('cantidad_'));

                // Guardar el código de material y la cantidad en el array de materiales
                $materiales[$codigoMaterial] = $value;
            }
        }

        // Insertar los datos en la nueva tabla de la base de datos
        foreach ($materiales as $codigoMaterial => $cantidad) {
            $sql = "INSERT INTO detalle (idProyecto, codigoMaterial, cantidad) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $idProyecto, $codigoMaterial, $cantidad);
            $stmt->execute();
        }

        echo "Se ha guardado la cantidad correctamente.";
        header("Location: home.php?mensaje=Se+ha+guardado+correctamente+ahora+puede+generar+el+PDF.&tipo=success");
    } else {
        echo "No se proporcionó un ID de proyecto válido o cantidad.";
    }
} else {
    echo "La solicitud no es válida.";
}
