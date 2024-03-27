<?php
// Incluir archivo de configuración de la base de datos
include "../config/database.php";

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit; // Detener la ejecución del script
}

// Obtener datos del usuario de la sesión
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$idUsuario = $_SESSION['id_usuario'];

// Realizar la consulta para obtener los proyectos
if ($rol === 'admin') {
    // Si el usuario es un administrador, seleccionar todos los proyectos
    $sql = "SELECT p.id, p.fecha AS fecha, p.nombre AS nombre_proyecto, u.nombre AS nombre_tecnico 
            FROM proyecto p 
            INNER JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Si el usuario es un usuario normal, seleccionar solo los proyectos asociados a su ID de usuario
    $sql = "SELECT p.id, p.fecha AS fecha, p.nombre AS nombre_proyecto, u.nombre AS nombre_tecnico 
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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column h-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <span class="nav-link text-white">Bienvenido
                            <?php echo $nombre; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="addproyecto.php" class="btn btn-primary me-2">Crear Proyecto</a>
                    </li>
                    <br>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger me-2">Salir</a>
                    </li>
                    <br>
                    <!--
                    <li class="nav-item">
                        <a href="registro_tecnicos.php" class="nav-link text-white">Registrar Técnicos</a>
                    </li>
                    -->
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Buscar por proyecto o técnico" autofocus>

        <?php
        if (isset($_SESSION['msg']) && isset($_SESSION['color'])) {
            echo "<div class='alert alert-{$_SESSION['color']} alert-dismissible fade show text-center' role='alert'>
                    {$_SESSION['msg']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        }

        if (!empty($mensaje) && !empty($tipo)) {
            echo "<div class='alert alert-$tipo alert-dismissible fade show text-center' role='alert'>
                    $mensaje
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        }
        ?>

        <div class="table-responsive">
            <table id="projectTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Proyecto</th>
                        <th class="text-center">Técnico</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='text-center'>" . $row['id'] . "</td>";
                            echo "<td class='text-center'>" . $row['fecha'] . "</td>";
                            echo "<td class='text-center projectName'>" . $row['nombre_proyecto'] . "</td>"; // Añadir una clase "projectName" al td del nombre del proyecto
                            echo "<td class='text-center technicianName'>" . $row['nombre_tecnico'] . "</td>"; // Añadir una clase "technicianName" al td del nombre del técnico
                            echo "<td class='text-center'><a href='informacion_proyecto.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Visualizar</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No hay proyectos disponibles.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

    <footer class="mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">©
                <?php echo date("Y"); ?> Todos los derechos reservados
            </span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cerrar el menú al hacer clic en un enlace del menú en dispositivos móviles
        document.querySelectorAll('.navbar-nav>li>a').forEach(function(elem) {
            elem.addEventListener('click', function() {
                document.querySelector('.navbar-collapse').classList.remove('show');
            });
        });
        // Función para realizar la búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchTerm = this.value.trim(); // Obtener el valor del input y eliminar espacios en blanco al principio y al final
            // Filtrar los proyectos según el término de búsqueda
            filterProjects(searchTerm);
        });

        // Función para filtrar los proyectos según el término de búsqueda
        function filterProjects(searchTerm) {
            let rows = document.querySelectorAll('#projectTableBody tr');
            rows.forEach(function(row) {
                let projectName = row.querySelector('.projectName').textContent.trim().toLowerCase();
                if (projectName.includes(searchTerm.toLowerCase())) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }


        // Función para realizar la búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchTerm = this.value.trim(); // Obtener el valor del input y eliminar espacios en blanco al principio y al final
            // Filtrar los proyectos según el término de búsqueda
            filterProjects(searchTerm);
        });

        // Función para filtrar los proyectos según el término de búsqueda
        function filterProjects(searchTerm) {
            let rows = document.querySelectorAll('#projectTable tbody tr');
            rows.forEach(function(row) {
                let projectName = row.querySelector('.projectName').textContent.trim().toLowerCase();
                let technicianName = row.querySelector('.technicianName').textContent.trim().toLowerCase();
                if (projectName.includes(searchTerm.toLowerCase()) || technicianName.includes(searchTerm.toLowerCase())) {
                    row.style.display = ''; // Mostrar la fila si el término de búsqueda coincide con el nombre del proyecto o del técnico
                } else {
                    row.style.display = 'none'; // Ocultar la fila si el término de búsqueda no coincide con el nombre del proyecto ni del técnico
                }
            });
        }
    </script>
</body>

</html>