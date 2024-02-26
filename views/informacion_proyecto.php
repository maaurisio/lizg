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
    $sql = "SELECT * FROM Proyecto WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProyecto);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el proyecto
    if ($result->num_rows > 0) {
        // Obtener los datos del proyecto
        $proyecto = $result->fetch_assoc();
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
                </tbody>
            </table>
        </body>


        </html>
<?php
    } else {
        echo "<p>No se encontró el proyecto.</p>";
    }
} else {
    echo "<p>No se proporcionó un ID de proyecto válido.</p>";
}
?>