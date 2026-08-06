CREATE DATABASE SistemaUsuario;

--tablas 
CREATE TABLE Rol (
Idrol INT PRIMARY KEY AUTO_INCREMENT,
Descripcion VARCHAR (30) NOT NULL UNIQUE
);

CREATE TABLE Credencial (
idCredencial int PRIMARY KEY AUTO_INCREMENT,
NombreUsuario VARCHAR (30) NOT NULL UNIQUE,
Correo VARCHAR (30) NOT NULL UNIQUE,
contrasena VARCHAR (255),
Activo BOOLEAN DEFAULT TRUE,
FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE LoginToken(
IdToken INT AUTO_INCREMENT PRIMARY KEY,
IdCredencial INT NOT NULL,
Token VARCHAR(64) NOT NULL UNIQUE,
FechaExpiracion DATETIME NOT NULL,
Usado BOOLEAN DEFAULT FALSE,
FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (IdCredencial)REFERENCES Credencial(IdCredencial)

);

CREATE TABLE Usuario (
IdUsuario INT AUTO_INCREMENT PRIMARY KEY ,
PrimerNombre VARCHAR(30),
SegundoNombre VARCHAR(30) NULL,
PrimerApellido VARCHAR(30),
SegundoApellido VARCHAR(30),
IdRol int ,
IdCredencial int ,
FOREIGN KEY (IdRol)REFERENCES Rol(IdRol) ON DELETE RESTRICT,
FOREIGN KEY (IdCredencial) REFERENCES Credencial(IdCredencial)
);

--inserts necesarios
INSERT INTO Rol(Descripcion)
VALUES
('administrador'),
('empleado');