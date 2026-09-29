<?php

// Incluye el script encargarlo de verificar que exista una sesión de usuario activa antes de cargar la página.
require_once '../backend/verificar_sesion.php';

?>

<!-- Declaración del tipo de documento que especifica el estándar HTML5 -->
<!DOCTYPE html>
<!-- Elemento raíz que define el idioma principal de la página como español -->
<html lang="es">

<!-- Sección de metadatos de la página no visibles directamente en el cuerpo -->
<head>

    <!-- Define la codificación de caracteres en UTF-8 para admitir acentos y caracteres especiales -->
    <meta charset="UTF-8">

    <!-- Ajusta el escalado y el ancho de la ventana gráfica para una visualización correcta en dispositivos móviles -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <!-- Define el título que aparecerá en la pestaña del navegador -->
    <title>Buscar objetos</title>

</head>

<!-- Inicio del contenido visible de la página web -->
<body>

    <!-- Enlace de navegación para regresar a la página principal "index.php" -->
    <a href="index.php">
        ← Volver al inicio
    </a>

    <!-- Encabezado principal de la sección de búsqueda -->
    <h1>Buscar objeto</h1>


    <!-- Formulario interactivo para ingresar el criterio de búsqueda -->
    <form id="form-busqueda">

        <!-- Campo de entrada de texto donde el usuario escribe el nombre o descripción a buscar -->
        <input
            type="text"
            id="busqueda"
            placeholder="Escribe lo que buscas"
            required
        >

        <!-- Botón para enviar el formulario e iniciar el proceso de búsqueda -->
        <button type="submit">
            Buscar
        </button>

    </form>


    <!-- Contenedor vacío donde se desplegarán dinámicamente los resultados de la búsqueda vía JavaScript -->
    <div id="resultados"></div>


    <!-- Inclusión del archivo JavaScript externo encargado de gestionar la lógica de búsqueda asíncrona -->
    <script src="js/buscar.js"></script>

<!-- Cierre del cuerpo del documento HTML -->
</body>

<!-- Cierre del elemento raíz HTML -->
</html>