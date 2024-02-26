<?php

// Definir las credenciales de conexión
$servername = "localhost";
$username = "root";
$password = "";
$database = "lizg";


// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Resto del código aquí...
