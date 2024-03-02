<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";
session_start();

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

if (!$idUsuario) {
    header("Location: ../index.php");
}

// Variable para almacenar el término de búsqueda
$busqueda = "";

// Variable para almacenar el ID del proyecto
$idProyecto = isset($_GET['id']) ? $_GET['id'] : null;

// Realizar la consulta SQL para obtener la lista de materiales
$sql = "SELECT m.*, mp.codigoMaterial AS material_usado FROM materiales m LEFT JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial AND mp.idProyecto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idProyecto);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se envió el formulario de búsqueda
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el término de búsqueda
    $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

    // Verificar si el término de búsqueda tiene al menos 4 letras
    if (strlen($busqueda) >= 4) {
        // Realizar la búsqueda en la base de datos y mostrar los resultados
        $sql = "SELECT m.*, mp.codigoMaterial AS material_usado FROM materiales m LEFT JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial AND mp.idProyecto = ? WHERE m.nombre LIKE ?";
        $stmt = $conn->prepare($sql);
        $param_busqueda = "%" . $busqueda . "%";
        $stmt->bind_param("is", $idProyecto, $param_busqueda);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Mostrar todos los materiales si el término de búsqueda no cumple con los requisitos
        $sql = "SELECT m.*, mp.codigoMaterial AS material_usado FROM materiales m LEFT JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial AND mp.idProyecto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idProyecto);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}

// Verificar si se envió el formulario para guardar la selección de materiales
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron materiales seleccionados
    if (isset($_POST['materiales']) && !empty($_POST['materiales']) && $idProyecto) {
        // Recorrer los materiales seleccionados
        foreach ($_POST['materiales'] as $material) {
            // Insertar el material seleccionado en la base de datos
            $sql = "INSERT INTO materialesproyecto (idProyecto, codigoMaterial) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idProyecto, $material);
            $stmt->execute();
        }

        echo "Se genero completamente";
        header("Location: informacion_proyecto.php?id=$idProyecto"); // Redirige con el ID del proyecto
        exit; // Detiene la ejecución del script después de la redirección
    }
}

// Limpiar el almacenamiento local si se cambió de proyecto
if ($idProyecto) {
    $storedProjectId = isset($_SESSION['projectId']) ? $_SESSION['projectId'] : null;
    if ($storedProjectId !== $idProyecto) {
        unset($_SESSION['selectedMaterials'][$idProyecto]); // Elimina la selección de materiales para el proyecto actual
    }
    $_SESSION['projectId'] = $idProyecto;
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

        <!-- Formulario para mostrar y seleccionar materiales -->
        <form action="" method="POST" class="d-flex flex-column">
            <!-- Input oculto para pasar el ID del proyecto -->
            <input type="hidden" name="idProyecto" value="<?php echo $idProyecto; ?>">
            <div class="mt-auto">
                <button type="submit" class="btn btn-primary" id="guardarSeleccion" onclick="limpiarLocalStorage()" disabled>Guardar Selección</button>
            </div>
            <div class="form-group mt-3">
                <!-- Contenedor para la lista de materiales -->
                <?php
                // Verificar si se encontraron materiales
                if ($result && $result->num_rows > 0) {
                    // Mostrar los materiales con checkboxes
                    while ($row = $result->fetch_assoc()) {
                        // Verificar si este material está seleccionado
                        $checked = $row['material_usado'] ? 'checked' : '';
                ?>
                        <div class="form-check">
                            <input class="form-check-input border-primary" type="checkbox" name="materiales[]" value="<?php echo $row['codigo']; ?>" <?php echo $checked; ?>>
                            <label class="form-check-label">
                                <?php echo $row['nombre']; ?>
                            </label>
                        </div>
                <?php
                    }
                } else {
                    // No se encontraron materiales, mostrar mensaje y enlace al formulario para agregar material
                    echo 'No se encontraron materiales. <a href="new_material.php?id=' . $idProyecto . '">Agregar nuevo material</a>';
                }
                ?>
            </div>
        </form>

        <script>
            function limpiarLocalStorage() {
                localStorage.removeItem('selectedMaterials_<?php echo $idProyecto; ?>');
            }
        </script>


    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Obtiene los materiales seleccionados almacenados en el local storage
            var selectedMaterials = JSON.parse(localStorage.getItem('selectedMaterials_' + <?php echo $idProyecto; ?>)) || [];

            // Recorre los materiales seleccionados y marca los checkbox correspondientes
            selectedMaterials.forEach(function(material) {
                var checkbox = document.querySelector('input[name="materiales[]"][value="' + material + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            // Verificar si hay algún checkbox marcado al cargar la página
            verificarSeleccion();

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
                    localStorage.setItem('selectedMaterials_' + <?php echo $idProyecto; ?>, JSON.stringify(selectedMaterials));

                    // Verificar si hay algún checkbox marcado al cambiar su estado
                    verificarSeleccion();
                });
            });

            // Función para verificar si hay algún checkbox marcado y habilitar/deshabilitar el botón "Guardar Selección"
            function verificarSeleccion() {
                var checkboxes = document.querySelectorAll('input[name="materiales[]"]');
                var botonGuardar = document.getElementById('guardarSeleccion');
                var algunSeleccionado = Array.from(checkboxes).some(function(checkbox) {
                    return checkbox.checked;
                });
                botonGuardar.disabled = !algunSeleccionado;
            }
        });
    </script>
</body>

</html>