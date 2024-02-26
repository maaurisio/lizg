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

        <a href="<?php echo isset($_GET['id']) ? 'informacion_proyecto.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-warning m-2">Volver</a>


        <form action="procesar_seleccion.php?id=<?php echo $idProyecto; ?>" method="POST" class="d-flex flex-column">
            <!-- Input oculto para pasar el ID del proyecto -->
            <input type="hidden" name="idProyecto" value="<?php echo $idProyecto; ?>">

            <div class="mt-auto">
                <button type="submit" class="btn btn-primary">Guardar Selección</button>
            </div>
            <div class="form-group mt-3">
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
                            <input class="form-check-input border-primary" type="checkbox" name="materiales[]" value="<?php echo $row['codigo']; ?>">
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
        </form>

    </div>
    <script>
        // Espera a que el DOM esté completamente cargado
        document.addEventListener("DOMContentLoaded", function() {
            // Obtiene los materiales seleccionados almacenados en el local storage
            var selectedMaterials = JSON.parse(localStorage.getItem('selectedMaterials')) || [];

            // Recorre los materiales seleccionados y marca los checkbox correspondientes
            selectedMaterials.forEach(function(material) {
                var checkbox = document.querySelector('input[name="materiales[]"][value="' + material + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            // Escucha los eventos de cambio en los checkbox
            var checkboxes = document.querySelectorAll('input[name="materiales[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function(event) {
                    // Si el checkbox está marcado, agrega el material seleccionado a la lista
                    if (this.checked) {
                        selectedMaterials.push(parseInt(this.value));
                    } else {
                        // Si el checkbox está desmarcado, remueve el material seleccionado de la lista
                        var index = selectedMaterials.indexOf(parseInt(this.value));
                        if (index !== -1) {
                            selectedMaterials.splice(index, 1);
                        }
                    }

                    // Guarda los materiales seleccionados en el local storage
                    localStorage.setItem('selectedMaterials', JSON.stringify(selectedMaterials));
                });
            });
        });
    </script>
</body>

</html>