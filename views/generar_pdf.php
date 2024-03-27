<?php
session_start();
date_default_timezone_set('America/Los_Angeles');

// Incluir Dompdf
require_once '../config/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Incluir la configuración de la base de datos
require_once '../config/database.php';

// Crear instancia de Dompdf con opciones
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Obtener el ID y el nombre del proyecto del parámetro GET
$idProyecto = isset($_GET['id']) ? intval($_GET['id']) : null;
$nombreProyecto = isset($_GET['nombre_proyecto']) ? $_GET['nombre_proyecto'] : '';

// Verificar si el ID del proyecto es válido
if ($idProyecto <= 0) {
    die('ID de proyecto no válido.');
}

// Contenido HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: Arial, sans-serif;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid black;
    padding: 5px;
}
th {
    background-color: #f2f2f2;
}
</style>
</head>
<body>
<img src="../images/encabezadoactual.png" style="margin-bottom: 15px;"><br>
<h2>Detalles del Proyecto: ' . $nombreProyecto . '</h2>';

// Consulta SQL para obtener los detalles del proyecto y el nombre del técnico que lo creó
$sql = "SELECT d.*, m.nombre, u.nombre AS nombre_tecnico
        FROM detalle d
        INNER JOIN materiales m ON d.codigoMaterial = m.codigo
        INNER JOIN proyecto p ON d.idProyecto = p.id
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE d.idProyecto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idProyecto);
$stmt->execute();
$result = $stmt->get_result();

// Obtener todos los resultados en un array
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Obtener el nombre del técnico que creó el proyecto
$nombreTecnico = !empty($rows) ? $rows[0]['nombre_tecnico'] : '';
$html .= '<h4>ID del Proyecto: ' . $idProyecto . '</h4>';

$html .= '<p><strong>Técnico:</strong> ' . $nombreTecnico . '</p>
<p><strong>Fecha:</strong> ' . date('d/m/Y') . '</p>

<table>
<tr>
<th>Código</th>
<th>Material</th>
<th>Cantidad</th>
</tr>';

// Recorrer los resultados almacenados en el array y agregar filas a la tabla HTML
foreach ($rows as $row) {
    // Verificar si la cantidad es cero (0)
    if ($row['cantidad'] != 0) {
        $html .= '
        <tr>
        <td>' . $row['codigoMaterial'] . '</td>
        <td>' . $row['nombre'] . '</td>
        <td>' . $row['cantidad'] . '</td>
        </tr>';
    }
}

$html .= '
</table>
</body>
</html>';

// Cargar el contenido HTML en DOMPDF
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->render();

// Generar el PDF en la salida con nombre de archivo personalizado
$dompdf->stream("$nombreProyecto.pdf", array("Attachment" => false));

// Después de renderizar el PDF
echo json_encode(array("success" => true));
