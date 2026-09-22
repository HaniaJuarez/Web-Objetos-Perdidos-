-- TABLA DE USUARIOS
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(100),
  correo VARCHAR(120) UNIQUE,
  password VARCHAR(255),
  rol VARCHAR(20),
  foto TEXT
);

-- TABLA DE CATEGORÍAS
CREATE TABLE categorias (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(50)
);

-- TABLA DE OBJETOS
CREATE TABLE objetos (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(100),
  descripcion_publica TEXT,
  descripcion_privada TEXT,
  color VARCHAR(40),
  estado VARCHAR(30),
  fecha DATE,
  imagen TEXT,
  
  usuario_id INT,
  categoria_id INT
);

-- TABLA DE RECLAMACIONES
CREATE TABLE reclamaciones (
  id SERIAL PRIMARY KEY,
  objeto_id INT,
  usuario_id INT,
  descripcion TEXT,
  estado VARCHAR(30)
);