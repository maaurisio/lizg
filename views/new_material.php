<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";
?>

<body>
    <img src="<?php echo $url ?>images/encabezadoactual.png" width="700" class="img-fluid mb-4" alt="Encabezado">
    <h3 class="mb-4 text-center">Nuevo material que no existe</h3>
    <div class="container mt-5 col-md-4">
        <form class="py-2">
            <div class="form-group">
                <label for="codigo">Código Material</label>
                <input type="text" class="form-control" id="codigo" placeholder="000" value="0000" readonly>
            </div>
            <div class="form-group mt-3">
                <label for="nombre">Nombre Material</label>
                <input type="text" class="form-control" id="nombre" placeholder="Nombre del nuevo material">
            </div>
            <div class="my-2">
                <button type="submit" class="btn btn-success mr-2">Guardar Material</button>
                <a href="<?php echo isset($_GET['id']) ? 'informacion_proyecto.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-danger m-2">Volver</a>
            </div>
        </form>
    </div>
</body>

</html>