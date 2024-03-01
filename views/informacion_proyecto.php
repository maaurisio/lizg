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

// Variable para almacenar el ID del proyecto
$idProyecto = isset($_GET['id']) ? $_GET['id'] : null;

// Variable para almacenar mensceajes de error o éxito
$mensaje = '';
$tipoMensaje = '';
$readonly = '';

// Variable para determinar si se han guardado los materiales
$guardado = false;

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
        // Insertar o actualizar los datos en la tabla de la base de datos
        foreach ($materiales as $codigoMaterial => $cantidad) {
            // Consultar la cantidad actual en la base de datos
            $sql_select = "SELECT cantidad FROM detalle WHERE idProyecto = ? AND codigoMaterial = ?";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("is", $idProyecto, $codigoMaterial);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();

            if ($result_select->num_rows > 0) {
                // Si el material ya existe para el proyecto, actualizar la cantidad
                $sql_update = "UPDATE detalle SET cantidad = ? WHERE idProyecto = ? AND codigoMaterial = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("isi", $cantidad, $idProyecto, $codigoMaterial);
                $stmt_update->execute();
            } else {
                // Si el material no existe para el proyecto, insertarlo
                $sql_insert = "INSERT INTO detalle (idProyecto, codigoMaterial, cantidad) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("isi", $idProyecto, $codigoMaterial, $cantidad);
                $stmt_insert->execute();
            }
        }


        // Marcar como guardado
        $guardado = true;

        // Mostrar mensaje de éxito utilizando alerta Bootstrap
        $tipoMensaje = 'success';
        $mensaje = '¡Se guardó correctamente ahora puede generar el PDF!';
        $readonly = 'readonly';
    } else {
        $readonly = '';
        $mensaje = "No se proporcionó un ID de proyecto válido o cantidad.";
        $tipoMensaje = 'error';
    }
}


// Consultar información del proyecto
if (!empty($idProyecto)) {
    $sql_proyecto = "SELECT * FROM proyecto WHERE id = ?";
    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("i", $idProyecto);
    $stmt_proyecto->execute();
    $result_proyecto = $stmt_proyecto->get_result();

    if ($result_proyecto->num_rows > 0) {
        $proyecto = $result_proyecto->fetch_assoc();

        // Definir la variable $num_materiales y establecerla en 0 inicialmente
        $num_materiales = 0;

        // Consultar los materiales asociados al proyecto
        $sql_materiales = "SELECT m.codigo, m.nombre, d.cantidad AS cantidad_detalle 
                           FROM materiales m 
                           INNER JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial 
                           LEFT JOIN detalle d ON m.codigo = d.codigoMaterial AND d.idProyecto = ?
                           WHERE mp.idProyecto = ?";

        $stmt_materiales = $conn->prepare($sql_materiales);
        $stmt_materiales->bind_param("ii", $idProyecto, $idProyecto);
        $stmt_materiales->execute();
        $result_materiales = $stmt_materiales->get_result();

        // Contar el número de materiales obtenidos
        $num_materiales = $result_materiales->num_rows;
?>

        <body class="d-flex flex-column h-100">
            <style>
                /* Estilos específicos para dispositivos de escritorio */
                @media (min-width: 768px) {
                    /* Personaliza los estilos aquí */
                }

                /* Estilos específicos para dispositivos móviles */
                @media (max-width: 767px) {

                    /* Ajustar el estilo del contenedor de botones para dispositivos móviles */
                    .contenedor-botones {
                        flex-direction: column;
                        align-items: center;
                    }
                }
            </style>
            <img src="<?php echo $url ?>images/encabezadoactual.png" width="700">
            <div class="container container-fluid d-flex justify-content-center align-item-center mb-5">
                <div class="border border-success border-3 p-3 mb-2 rounded mt-2">
                    <h1>Información del Proyecto</h1>
                    <p><strong>Nombre del Proyecto:</strong> <?php echo $proyecto['nombre']; ?></p>
                    <p><strong>Descripción del Proyecto:</strong> <?php echo $proyecto['descripcion']; ?></p>
                </div>
            </div>




            <!-- Código HTML para mostrar la alerta -->
            <?php if (!empty($mensaje)) : ?>
                <div class="d-flex justify-content-center"> <!-- Contenedor flexbox -->

                    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>




            <div class="container container-fluid d-flex justify-content-evenly contenedor-botones">
                <a href="home.php" class="btn btn-warning mb-2">Volver</a>
                <a href="materiales.php?id=<?php echo $idProyecto; ?>" class="btn btn-dark mb-2">Ver Lista de Materiales</a>

                <?php
                // Verificar si hay materiales para habilitar o deshabilitar los botones
                if ($num_materiales > 0) {
                    echo '<a href="generar_pdf.php?id=' . $idProyecto . '&nombre_proyecto=' . urlencode($proyecto["nombre"]) . '" class="btn btn-danger mb-2" target="_blank">Generar PDF</a>';

                    echo '<form action="generar_exel.php" method="post">';
                    echo '<input type="hidden" name="id_proyecto" value="' . $idProyecto . '">';
                    echo '<button type="submit" class="btn btn-success mb-2">Generar EXCEL</button>';
                    echo '</form>';
                } else {
                    echo '<button class="btn btn-danger mb-2" disabled>Generar PDF</button>';
                    echo '<button class="btn btn-success mb-2" disabled>Generar EXCEL</button>';
                }
                ?>

            </div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <input type="hidden" name="idProyecto" value="<?php echo $idProyecto; ?>">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mt-4 container">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Codigo</th>
                                <th scope="col">Material</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Verificar si se encontraron materiales asociados al proyecto
                            if ($num_materiales > 0) {
                                // Crear un array para almacenar los códigos de material ya procesados
                                $codigos_procesados = array();

                                // Mostrar los materiales en la tabla
                                while ($row = $result_materiales->fetch_assoc()) {
                                    $codigo = stripslashes($row['codigo']);
                                    $nombre = stripslashes($row['nombre']);
                                    $cantidad_detalle = $row['cantidad_detalle'];

                                    // Verificar si el código de material ya fue procesado
                                    if (!in_array($codigo, $codigos_procesados)) {
                                        // Agregar el código a la lista de códigos procesados
                                        $codigos_procesados[] = $codigo;

                                        // Mostrar la fila en la tabla
                            ?>
                                        <tr>
                                            <td><?php echo $codigo; ?></td>
                                            <td><?php echo $nombre; ?></td>
                                            <td>
                                                <!-- Campo de entrada para la cantidad -->
                                                <input type='number' min="0" autofocus name='cantidad_<?php echo $codigo; ?>' class='form-control' value='<?php echo $cantidad_detalle; ?>' <?php echo $readonly; ?> required>
                                            </td>
                                            <td>
                                                <!-- Siempre mostrar el botón de "Editar" -->
                                                <button type="button" class="btn btn-warning editar-cantidad" data-codigo="<?php echo $codigo; ?>">Editar</button>
                                            </td>
                                        </tr>
                            <?php
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='4'>No hay materiales asociados a este proyecto.</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <?php
                    // Si no hay materiales, deFshabilitar el botón Guardar
                    if ($num_materiales == 0) {
                        echo '<button type="submit" class="btn btn-primary my-2" disabled>Guardar</button>';
                    } else {
                        echo '<button type="submit" class="btn btn-primary my-2">Guardar</button>';
                    }
                    ?>
                </div>
            </form>


    <?php
    } else {
        echo "<p>No se encontró el proyecto.</p>";
    }
} else {
    echo "<p>No se proporcionó un ID de proyecto válido.</p>";
}
    ?>
    <script>
        // Script JavaScript para cambiar entre solo lectura y editable al hacer clic en "Editar"
        const botonesEditar = document.querySelectorAll('.editar-cantidad');
        botonesEditar.forEach(boton => {
            boton.addEventListener('click', function() {
                const codigoMaterial = this.getAttribute('data-codigo');
                const inputCantidad = document.querySelector('input[name="cantidad_' + codigoMaterial + '"]');

                // Quitar el atributo 'readonly' para permitir la edición
                inputCantidad.removeAttribute('readonly');
                inputCantidad.focus(); // Opcional: enfocar el campo automáticamente al hacer clic en "Editar"
            });
        });

        // Bloquear los campos de cantidad que ya tienen información y mostrar el botón "Editar"
        // Bloquear los campos de cantidad que ya tienen información y mostrar el botón "Editar"
        document.addEventListener("DOMContentLoaded", function() {
            const inputsCantidad = document.querySelectorAll('input[name^="cantidad_"]');
            inputsCantidad.forEach(input => {
                if (input.value !== '') {
                    input.setAttribute('readonly', 'readonly');
                    const codigoMaterial = input.getAttribute('name').replace('cantidad_', '');
                    const botonEditar = document.querySelector('.editar-cantidad[data-codigo="' + codigoMaterial + '"]');
                    botonEditar.style.display = 'inline'; // Mostrar el botón "Editar"
                }
            });
        });
    </script>
        </body>

        </html>