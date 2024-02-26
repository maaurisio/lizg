<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";

// Verificar si se ha proporcionado un ID de proyecto
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Obtener el ID del proyecto desde la URL
    $idProyecto = $_GET['id'];

    // Consulta SQL para obtener los datos del proyecto
    $sql_proyecto = "SELECT * FROM Proyecto WHERE id = ?";
    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("i", $idProyecto);
    $stmt_proyecto->execute();
    $result_proyecto = $stmt_proyecto->get_result();

    // Verificar si se encontró el proyecto
    if ($result_proyecto->num_rows > 0) {
        // Obtener los datos del proyecto
        $proyecto = $result_proyecto->fetch_assoc();
?>

        <body class="d-flex flex-column h-100">
            <img src="<?php echo $url ?>images/encabezadoactual.png" width="700">
            <div class="container d-flex justify-content-center align-item-center">
                <div class="border border-danger p-3 mb-2 rounded mt-2">
                    <h1>Información del Proyecto</h1>
                    <p><strong>Nombre del Proyecto:</strong> <?php echo $proyecto['nombre']; ?></p>
                    <p><strong>Descripción del Proyecto:</strong> <?php echo $proyecto['descripcion']; ?></p>
                </div>
            </div>
            <div class="container d-flex justify-content-evenly">
                <a href="home.php" class="btn btn-warning">Volver</a>
                <!-- Enlace para dirigir al usuario a la página de edición con el ID del proyecto -->
                <a href="editar_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-primary">Editar Información</a>
                <a href="materiales.php?id=<?php echo $idProyecto; ?>" class="btn btn-success">Ver Lista de Materiales</a>
                <a href="#" class="btn btn-info">Agregar Material que falta</a>
            </div>
            <table class="table table-sm table-striped table-hover mt-4 container">
                <thead class="table-dark">
                    <tr>
                        <th>Codigo</th>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    // Consulta SQL para obtener los materiales asociados al proyecto
                    $sql_materiales = "SELECT m.codigo, m.nombre FROM Materiales m INNER JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial WHERE mp.idProyecto = ?";
                    $stmt_materiales = $conn->prepare($sql_materiales);
                    $stmt_materiales->bind_param("i", $idProyecto);
                    $stmt_materiales->execute();
                    $result_materiales = $stmt_materiales->get_result();

                    // Verificar si se encontraron materiales asociados al proyecto
                    if ($result_materiales->num_rows > 0) {
                        // Mostrar los materiales en la tabla
                        while ($row = $result_materiales->fetch_assoc()) {
                            $codigo = stripslashes($row['codigo']);
                            $nombre = stripslashes($row['nombre']);
                    ?>
                            <tr>
                                <td><?php echo $codigo; ?></td>
                                <td><?php echo $nombre; ?></td>
                                <td>
                                    <!-- Campo de entrada para la cantidad -->
                                    <input type='number' name='cantidad_<?php echo $codigo; ?>' value='<?php echo $row['cantidad']; ?>' class='form-control'>
                                </td>
                                <td>Acción</td><!-- Aquí puedes agregar las acciones que desees -->
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay materiales asociados a este proyecto.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>


        </body>
<?php
    } else {
        echo "<p>No se encontró el proyecto.</p>";
    }
} else {
    echo "<p>No se proporcionó un ID de proyecto válido.</p>";
}
?>