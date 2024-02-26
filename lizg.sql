-- Crear tabla Usuarios
CREATE TABLE Usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    usuario VARCHAR(255) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(255)
);

-- Crear tabla Proyecto
CREATE TABLE Proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripción VARCHAR(255),
);

ALTER TABLE Proyecto
ADD COLUMN usuario_id INT,
ADD CONSTRAINT fk_usuario
    FOREIGN KEY (usuario_id)
    REFERENCES Usuarios(id);

-- Crear tabla Materiales
CREATE TABLE Materiales (
    codigo int, 
    nombre varchar(255)
);
