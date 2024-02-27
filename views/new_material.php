<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";

$idProyecto = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_material = $_POST['nombre_material'];

    // Generar un código único combinando una marca de tiempo y una cadena aleatoria
    $codigo = hash('crc32', uniqid() . '-' . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6));

    $stmt = $conn->prepare("INSERT INTO materiales (codigo, nombre, codigocero) VALUES (?, ?, '')");
    $stmt->bind_param("ss", $codigo, $nombre_material);

    if ($stmt->execute()) {
        header("Location: materiales.php?mensaje=Nuevo+material+agregado&id=$idProyecto");
    }
}

?>

<body>
    <img src="<?php echo $url ?>images/encabezadoactual.png" width="700" class="img-fluid mb-4" alt="Encabezado">
    <h3 class="mb-4 text-center">Nuevo material que no existe</h3>
    <div class="container mt-5 col-md-4">
        <form class="py-2" action="" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre Material</label>
                <input type="text" class="form-control" id="nombre" name="nombre_material" placeholder="Nombre del nuevo material">
            </div>
            <!-- Campo hidden para almacenar el código generado -->
            <input type="hidden" name="codigo" value="<?php echo $codigo; ?>">
            <div class="my-2">
                <button type="submit" class="btn btn-success mr-2">Guardar Material</button>
                <a href="<?php echo isset($_GET['id']) ? 'materiales.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-danger m-2">Volver</a>
            </div>
        </form>
    </div>
</body>