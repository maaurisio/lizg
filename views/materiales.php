<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";

// Variable para almacenar el término de búsqueda
$busqueda = "";

// Verificar si se envió el formulario de búsqueda
if (isset($_POST['busqueda'])) {
    $busqueda = $_POST['busqueda'];
}
?>

<body>
    <div class="container">
        <h1>Lista de Materiales</h1>
        <!-- Formulario de búsqueda -->
        <form action="" method="POST" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="busqueda" placeholder="Buscar materiales" value="<?php echo $busqueda; ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <a href="<?php echo isset($_GET['id']) ? 'informacion_proyecto.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-warning">Volver</a>


        <form action="procesar_seleccion.php" method="POST" class="d-flex flex-column">
            <div class="mt-auto">
                <button type="submit" class="btn btn-primary">Guardar Selección</button>
            </div>
            <div class="form-group">
                <!-- Contenedor para la lista de materiales -->
                <?php
                // Consulta SQL para obtener los materiales que coinciden con el término de búsqueda
                $sql = "SELECT * FROM Materiales WHERE nombre LIKE ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $param_busqueda);

                // Añadir los comodines '%' al término de búsqueda
                $param_busqueda = "%" . $busqueda . "%";

                $stmt->execute();
                $result = $stmt->get_result();

                // Verificar si se encontraron materiales
                if ($result->num_rows > 0) {
                    // Mostrar los materiales con checkboxes
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="materiales[]" value="<?php echo $row['codigo']; ?>">
                            <label class="form-check-label">
                                <?php echo $row['nombre']; ?>
                            </label>
                        </div>
                <?php
                    }
                } else {
                    echo "No se encontraron materiales.";
                }
                ?>
            </div>
            <!-- Contenedor para el botón "Guardar Selección" -->

        </form>

    </div>
</body>

</html>