<?php
include "../config/partials/header.php";
include "../config/database.php";

session_start();
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

if (!$idUsuario) {
    header("Location: ../index.php");
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Redireccionar al usuario a la página de inicio de sesión si no ha iniciado sesión
    header("Location: ../index.php");
    exit; // Terminar el script para evitar que el resto del código se ejecute
}

// Obtener el ID del usuario de la sesión
$idUsuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    //Preparar la consulta
    $stmt = $conn->prepare("INSERT INTO proyecto(nombre,descripcion,usuario_id) VALUES(?,?,?)");

    $stmt->bind_param("ssi", $nombre, $descripcion, $idUsuario);

    if ($stmt->execute()) {
        // Redireccionar al usuario a la página de inicio con un mensaje de éxito
        header("Location: home.php?mensaje=¡Proyecto creado con éxito!&tipo=success");
        exit;
    } else {
        // Redireccionar al usuario a la página de inicio con un mensaje de error
        header("Location: home.php?mensaje=Error al crear el proyecto.&tipo=danger");
        exit;
    }
}
?>

<body class="d-flex flex-column h-100">
    <style>

    </style>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-4">
                <img src="<?php echo $url ?>images/encabezadoactual.png" class="img-fluid mb-4" alt="Encabezado">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Añadir Nuevo Proyecto</h2>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="nombre">Nombre del Proyecto:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="descripcion">Descripción:</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary btn-block">Crear Proyecto</button>
                                <a href="home.php" class="btn btn-warning">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>