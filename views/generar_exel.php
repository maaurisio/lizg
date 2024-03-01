<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // Asegúrate de incluir el archivo autoload.php de PhpSpreadsheet en tu proyecto

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Obtener el ID del proyecto seleccionado
$idProyecto = isset($_POST['id_proyecto']) ? intval($_POST['id_proyecto']) : null;

if ($idProyecto) {
    // Crear instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();

    // Obtener la hoja activa
    $sheet = $spreadsheet->getActiveSheet();

    // Establecer encabezados
    $sheet->setCellValue('A1', 'Código');
    $sheet->setCellValue('B1', 'Material');
    $sheet->setCellValue('C1', 'Cantidad');

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
    $row = 2; // Comenzar en la fila 2 (debajo de los encabezados)
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
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="detalle_proyecto.xlsx"');
    header('Cache-Control: max-age=0');

    // Salida del archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Cerrar el statement y la conexión
    $stmt->close();
    $conn->close();
} else {
    echo "ID de proyecto no válido.";
}
