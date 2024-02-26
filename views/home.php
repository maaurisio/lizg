<?php
//database
include "../config/database.php";
//HEADER 
include "../config/partials/header.php";

session_start();
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

// Realizar la consulta para obtener los proyectos
if ($rol === 'admin') {
    // Si el usuario es un administrador, seleccionar todos los proyectos
    $sql = "SELECT p.id, p.nombre AS nombre_proyecto, u.nombre AS nombre_tecnico 
            FROM proyecto p 
            INNER JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Si el usuario es un usuario normal, seleccionar solo los proyectos asociados a su ID de usuario
    $sql = "SELECT p.id, p.nombre AS nombre_proyecto, u.nombre AS nombre_tecnico 
            FROM proyecto p 
            INNER JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.usuario_id = ? 
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<body class="d-flex flex-column h-100">
    <img src="<?php echo $url ?>images/encabezadoactual.png" width="500">
    <div class="container py-3">

        <h2 class="text-center">Mis Proyectos </h2>
        <div class="text-center">
            Usuario:
            <?php echo $nombre; ?>
        </div>
        <?php if (isset($_SESSION['msg']) && isset($_SESSION['color'])) { ?>
            <div class="alert alert-<?= $_SESSION['color']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        <?php
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        } ?>

        <div class="row justify-content-end">

            <div>
                <a href="addproyecto.php" class="btn btn-primary">Crear Proyecto</a>
            </div>

            <div class="col-auto">
                <a href="../index.php" class="btn btn-danger">Salir</a>
            </div>

            <form action="" method="post" accept-charset="utf-8">

                <table class="table table-sm table-striped table-hover mt-4">
                    <thead class="table-dark">
                        <tr>
                            <th>Id</th>
                            <th>Nombre del Proyecto</th>
                            <th>Técnico</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // Iterar sobre cada fila de resultados
                            while ($row = $result->fetch_assoc()) {
                                // Imprimir cada fila en la tabla
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['nombre_proyecto'] . "</td>";
                                echo "<td>" . $row['nombre_tecnico'] . "</td>";
                                echo "<td><a href='informacion_proyecto.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Ver</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            // Si no hay proyectos, imprimir una fila indicando que no hay proyectos
                            echo "<tr><td colspan='4'>No hay proyectos disponibles.</td></tr>";
                        }

                        ?>
                    </tbody>
                </table>
            </form>
        </div>
</body>

</html>