<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Obtener el ID del proyecto seleccionado
$idProyecto = isset($_POST['id_proyecto']) ? intval($_POST['id_proyecto']) : null;

if ($idProyecto) {
    // Consultar el nombre del proyecto y el nombre del servicio
    $sql_nombre_proyecto = "SELECT p.nombre AS nombre_proyecto, s.nombre_servicio AS nombre_servicio
                            FROM proyecto p
                            INNER JOIN servicio s ON p.servicio_id = s.id
                            WHERE p.id = ?";
    $stmt_nombre_proyecto = $conn->prepare($sql_nombre_proyecto);
    $stmt_nombre_proyecto->bind_param("i", $idProyecto);
    $stmt_nombre_proyecto->execute();
    $result_nombre_proyecto = $stmt_nombre_proyecto->get_result();

    if ($result_nombre_proyecto->num_rows > 0) {
        $row_nombre_proyecto = $result_nombre_proyecto->fetch_assoc();
        $nombre_proyecto = $row_nombre_proyecto['nombre_proyecto'];
        $nombre_servicio = $row_nombre_proyecto['nombre_servicio'];

        // Crear instancia de Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Obtener la hoja activa
        $sheet = $spreadsheet->getActiveSheet();

        // Obtener los datos del proyecto desde la base de datos
        $sql = "SELECT d.*, m.nombre AS nombre_material
                FROM detalle d
                INNER JOIN materiales m ON d.codigoMaterial = m.codigo
                WHERE d.idProyecto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idProyecto);
        $stmt->execute();
        $result = $stmt->get_result();

        // Escribir los datos en el archivo Excel
        $row = 1; // Comenzar en la fila 2 (debajo de los encabezados)
        while ($data = $result->fetch_assoc()) {
            // Verificar si la cantidad es cero (0)
            if ($data['cantidad'] != 0) {
                $sheet->setCellValue('A' . $row, $data['codigoMaterial']);
                $sheet->setCellValue('B' . $row, $data['nombre_material']);
                $sheet->setCellValue('C' . $row, $data['cantidad']);
                $row++;
            }
        }

        // Configurar el tipo de contenido y el nombre del archivo
        $nombre_archivo = $nombre_proyecto . '_' . $nombre_servicio . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
        header('Cache-Control: max-age=0');

        // Salida del archivo
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        // Cerrar el statement y la conexión
        $stmt->close();
        $stmt_nombre_proyecto->close();
        $conn->close();
    } else {
        echo "No se encontró el nombre del proyecto o del servicio.";
    }
} else {
    echo "ID de proyecto no válido.";
}
?>
