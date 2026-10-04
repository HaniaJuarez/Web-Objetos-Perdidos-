<?php

// Incluye el script encargado de verificar que exista una sesión de usuario activa antes de cargar la página.
require_once '../backend/verificar_sesion.php';
// Incluye el archivo de configuración con las variables globales y credenciales necesarias.
require_once '../backend/config.php';

// Obtiene el ID del objeto enviado por URL a través del método GET; si no existe, asigna una cadena vacía.
$id = $_GET['id'] ?? '';
// Obtiene el ID del objeto perdido asociado enviado por URL a través del método GET; si no existe, asigna una cadena vacía.
$perdido_id = $_GET['perdido_id'] ?? '';

// Comprueba si el ID está vacío para detener la ejecución si no se proporcionó un parámetro válido.
if (empty($id)) {
    // Imprime un mensaje en pantalla indicando la falta del identificador del objeto.
    echo "Objeto no especificado.";
    // Detiene completamente la ejecución del script.
    exit;
}

// Verifica que exista el objeto perdido.
if (empty($perdido_id)) {
    // Imprime un mensaje en pantalla especificando que no se encontro el objeto perdido.
    echo "No se especificó el objeto perdido relacionado.";
    echo "<br><br>";
    // linea que indica la opcion de volver al inicio
    echo '<a href="index.php">Volver al inicio</a>';
    // Detiene completamente la ejecución del script.
    exit;
}

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
    <title>Solicitar reclamación</title>

</head>

<!-- Inicio del contenido visible de la página web -->
<body>

    <!-- Encabezado principal de la página de solicitud de reclamación -->
    <h1>Solicitar reclamación</h1>

    <!-- Párrafo de instrucción para orientar al usuario sobre el contenido que debe ingresar -->
    <p>
        Describe una característica privada que permita
        comprobar que el objeto te pertenece.
    </p>


    <!-- Formulario que envía la información mediante el método POST hacia el script backend encargado de la reclamación -->
    <form action="../backend/reclamar.php" method="POST">

        <!-- Campo oculto que envía el ID del objeto a reclamar escapando caracteres especiales de forma segura -->
        <input
            type="hidden"
            name="objeto_id"
            value="<?php echo htmlspecialchars($id); ?>"
        >

        <!-- Campo oculto que envía el ID del objeto perdido de referencia escapando caracteres especiales -->
        <input
            type="hidden"
            name="objeto_perdido_id"
            value="<?php echo htmlspecialchars($perdido_id); ?>"
        >

        <!-- Etiqueta descriptiva vinculada al campo del área de texto para la descripción privada -->
        <label for="descripcion">
            Información para verificar la propiedad:
        </label>

        <!-- Salto de línea estructural -->
        <br>

        <!-- Campo de entrada multilínea obligatorio donde el usuario escribe los detalles de comprobación -->
        <textarea
            id="descripcion"
            name="descripcion"
            rows="6"
            cols="50"
            required
        ></textarea>

        <!-- Doble salto de línea para separar el área de texto del botón de envío -->
        <br><br>


        <!-- Botón para enviar el formulario y procesar la solicitud de reclamación -->
        <button type="submit">
            Enviar reclamación
        </button>

    </form>


    <!-- Salto de línea previo al enlace de cancelación -->
    <br>

    <!-- Enlace de navegación para cancelar la operación y volver a la página principal "index.php" -->
    <a href="index.php">
        Cancelar
    </a>

<!-- Cierre del cuerpo del documento HTML -->
</body>

<!-- Cierre del elemento raíz HTML -->
</html>