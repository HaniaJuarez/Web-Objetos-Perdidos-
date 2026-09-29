<?php

// Incluye el archivo que verifica que la sesión del usuario esté activa antes de continuar.
require_once '../backend/verificar_sesion.php';
// Incluye el archivo de configuración general con las constantes del sistema (como SUPABASE_URL y SUPABASE_KEY).
require_once '../backend/config.php';

// Obtiene el identificador del ticket pasado por la URL via GET; si no existe, asigna una cadena vacía.
$ticket =
    $_GET['ticket'] ?? '';

// Comprueba si la variable del ticket está vacía.
if (empty($ticket)) {

    // Muestra un mensaje en pantalla informando que falta el código del ticket.
    echo "Ticket no especificado.";

    // Detiene la ejecución del script.
    exit;

}


// Recupera el identificador único del usuario actualmente logueado desde la sesión global PHP.
$usuario_id =
    $_SESSION['usuario_id'];


// Construye la URL para consultar la API de Supabase en la tabla "reclamaciones", filtrando por el ticket y el id del usuario.
$url =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?' .
    'ticket=eq.' .
    urlencode($ticket) .
    '&usuario_id=eq.' .
    urlencode($usuario_id) .
    '&select=*';


// Define las opciones de configuración para realizar la solicitud HTTP a la API REST.
$options = [

    // Especifica la configuración propia del protocolo HTTP.
    'http' => [

        // Define que la petición será de tipo GET para consultar información.
        'method' =>
            'GET',

        // Define las cabeceras HTTP necesarias para pasar la API key, el token Bearer de autenticación y el Content-Type JSON.
        'header' =>

            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n"

    ]

];


// Crea el recurso de contexto HTTP con las opciones antes declaradas.
$context =
    stream_context_create($options);


// Realiza la petición a la API de Supabase mediante file_get_contents empleando el contexto de red configurado.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// Transforma la respuesta en formato JSON a un arreglo asociativo de PHP.
$reclamaciones =
    json_decode(
        $response,
        true
    );


// Comprueba si el arreglo devuelto está vacío (si no se halló el ticket o no pertenece al usuario).
if (empty($reclamaciones)) {

    // Informa en pantalla que el ticket solicitado no existe o no se encontró.
    echo "Ticket no encontrado.";

    // Cancela la ejecución restante del script.
    exit;

}


// Asigna el primer resultado obtenido a la variable de trabajo $reclamacion.
$reclamacion =
    $reclamaciones[0];

?>

<!-- Declaración del tipo de documento estándar HTML5 -->
<!DOCTYPE html>
<!-- Apertura de la etiqueta raíz HTML especificando el idioma español -->
<html lang="es">

<!-- Cabecera del documento donde se colocan los metadatos y título -->
<head>

    <!-- Define el conjunto de caracteres UTF-8 para permitir tildes y símbolos especiales -->
    <meta charset="UTF-8">

    <!-- Define el título de la página que se visualiza en la pestaña del navegador -->
    <title>Ticket de reclamación</title>

</head>

<!-- Inicio del cuerpo visible de la página web -->
<body>

    <!-- Encabezado principal del documento HTML -->
    <h1>
        Ticket de reclamación
    </h1>


    <!-- Encabezado secundario donde se imprime el código del ticket de forma segura con htmlspecialchars -->
    <h2>
        <?php echo htmlspecialchars($reclamacion['ticket']); ?>
    </h2>


    <!-- Párrafo que despliega el estado actual de la reclamación -->
    <p>
        <strong>Estado:</strong>

        <?php echo htmlspecialchars(
            $reclamacion['estado']
        ); ?>

    </p>


    <!-- Párrafo que indica el título de la sección de descripción -->
    <p>
        <strong>Descripción:</strong>
    </p>

    <!-- Párrafo que contiene la descripción ingresada para la reclamación escapada de forma segura -->
    <p>
        <?php echo htmlspecialchars(
            $reclamacion['descripcion']
        ); ?>
    </p>


    <!-- Párrafo con las instrucciones orientativas para el usuario -->
    <p>
        Presenta este ticket al docente encargado
        para continuar con el proceso de verificación.
    </p>


    <!-- Espaciado vertical mediante un salto de línea -->
    <br>

    <!-- Enlace de navegación para regresar al menú principal "index.php" -->
    <a href="index.php">
        Volver al inicio
    </a>

<!-- Cierre de la sección del cuerpo -->
</body>

<!-- Cierre del documento HTML -->
</html>