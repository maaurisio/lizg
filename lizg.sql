-- Crear tabla Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    usuario VARCHAR(255) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(255)
);

-- Crear tabla Proyecto
CREATE TABLE proyecto (
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
CREATE TABLE materiales (
    codigo int, 
    nombre varchar(255)
);

CREATE TABLE materialesproyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idProyecto INT,
    codigoMaterial INT,
    FOREIGN KEY (idProyecto) REFERENCES proyecto(id),
    FOREIGN KEY (codigoMaterial) REFERENCES materiales(codigo)
);
