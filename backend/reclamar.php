<?php

// Incluye el script encargado de verificar que la sesión del usuario se encuentre activa.
require_once 'verificar_sesion.php';
// Incluye el archivo de configuración global que contiene las constantes de la API de Supabase.
require_once 'config.php';

// Verifica que la petición HTTP recibida haya sido enviada mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Imprime un mensaje de error si el método no es POST.
    echo "Solicitud no válida.";
    // Cancela la ejecución del script.
    exit;
}


// Recupera el ID del objeto enviado por el formulario vía POST; si no existe, asigna una cadena vacía.
$objeto_id =
    $_POST['objeto_id'] ?? '';

// Recupera el ID del objeto perdido asociado desde los datos POST; si no existe, asigna una cadena vacía.
$objeto_perdido_id =
    $_POST['objeto_perdido_id'] ?? '';

// Obtiene la descripción o detalle para verificar la propiedad enviado por el usuario.
$descripcion =
    $_POST['descripcion'] ?? '';

// Obtiene el identificador del usuario autenticado almacenado en la variable global de sesión.
$usuario_id =
    $_SESSION['usuario_id'];


// Valida que ninguno de los tres campos requeridos esté vacío.
if (
    empty($objeto_id) ||
    empty($objeto_perdido_id) ||
    empty($descripcion)
) {

    // Notifica al usuario que debe llenar todos los campos obligatorios.
    echo "Completa todos los campos.";
    // Finaliza la ejecución del script.
    exit;

}


/* =========================
   GENERAR TICKET
   ========================= */

// Genera un código de ticket único concatenando el prefijo 'REC-', la fecha/hora actual (AñoMesDíaHoraMinutoSegundo) y un número aleatorio de 3 dígitos.
$ticket =
    'REC-' .
    date('YmdHis') .
    '-' .
    rand(100, 999);


/* =========================
   DATOS
   ========================= */

// Define el arreglo asociativo con la estructura de datos requerida para insertar en la tabla "reclamaciones" de Supabase.
$datos = [

    // Convierte explícitamente el ID del objeto a entero.
    'objeto_id' =>
        (int)$objeto_id,

    // Convierte el ID del objeto perdido a entero.
    'objeto_perdido_id' =>
        (int)$objeto_perdido_id,

    // Convierte el ID del usuario actual a entero.
    'usuario_id' =>
        (int)$usuario_id,

    // Almacena la descripción de verificación introducida por el usuario.
    'descripcion' =>
        $descripcion,

    // Asigna el estado inicial por defecto de la reclamación como 'Pendiente'.
    'estado' =>
        'Pendiente',

    // Guarda el código de ticket recién generado.
    'ticket' =>
        $ticket

];


// Define el punto final de la API REST de Supabase para la tabla de reclamaciones.
$url =
    SUPABASE_URL .
    '/rest/v1/reclamaciones';


// Configura las opciones de la petición HTTP POST enviada a Supabase.
$options = [

    // Especifica la sección de configuración del protocolo HTTP.
    'http' => [

        // Define el método de la petición como POST para la inserción de registros.
        'method' =>
            'POST',

        // Define los encabezados de autenticación apikey, Bearer token, el tipo de contenido JSON y el retorno de la representación insertada.
        'header' =>

            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n" .

            "Prefer: return=representation\r\n",

        // Asigna la cadena JSON codificada del arreglo $datos como el cuerpo de la solicitud HTTP.
        'content' =>
            json_encode($datos),

        // Permite procesar las respuestas del servidor aun cuando devuelvan códigos de estado HTTP con error.
        'ignore_errors' =>
            true
    ]

];


// Crea el recurso de contexto de transmisión HTTP con las opciones de la petición configuradas.
$context =
    stream_context_create($options);


// Realiza la petición POST a la API de Supabase para guardar la reclamación.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// Comprueba si la solicitud a través de file_get_contents falló.
if ($response === false) {

    // Muestra un mensaje informando que ocurrió un error durante el proceso.
    echo "Error al registrar la reclamación.";

    // Detiene la ejecución.
    exit;

}


// Transforma el cuerpo de la respuesta JSON recibida de Supabase a un arreglo asociativo en PHP.
$resultado =
    json_decode(
        $response,
        true
    );


// Comprueba si la respuesta de Supabase contiene una clave 'message', lo cual indica una falla o excepción devuelta por la base de datos.
if (
    isset($resultado['message'])
) {

    // Imprime el mensaje general de error.
    echo "Error al registrar la reclamación.";

    // Agrega un salto de línea en HTML.
    echo "<br>";

    // Imprime de forma segura el mensaje detallado devuelto por la API.
    echo htmlspecialchars(
        $resultado['message']
    );

    // Detiene la ejecución del código.
    exit;

}


/* =========================
   MOSTRAR TICKET
   ========================= */

?>

<!-- Declaración del tipo de documento HTML5 -->
<!DOCTYPE html>
<!-- Apertura del elemento raíz especificado en idioma español -->
<html lang="es">

<!-- Cabecera de la página web que contiene los metadatos y el título -->
<head>

    <!-- Establece la codificación UTF-8 para garantizar la correcta visualización de caracteres en español -->
    <meta charset="UTF-8">

    <!-- Define el título de la pestaña en el navegador -->
    <title>Reclamación registrada</title>

</head>

<!-- Inicio del cuerpo del documento HTML -->
<body>

    <!-- Encabezado de nivel 1 con el aviso de registro exitoso -->
    <h1>
        Reclamación registrada
    </h1>

    <!-- Párrafo confirmatorio del registro de la solicitud -->
    <p>
        Tu solicitud fue registrada correctamente.
    </p>

    <!-- Encabezado secundario para la etiqueta del ticket -->
    <h2>
        Ticket:
    </h2>

    <!-- Encabezado principal que imprime el código del ticket escapado de forma segura -->
    <h1>
        <?php echo htmlspecialchars($ticket); ?>
    </h1>

    <!-- Párrafo con instrucciones sobre la conservación del ticket -->
    <p>
        Guarda este número para presentarlo
        al docente encargado.
    </p>

    <!-- Enlace para ver la vista detallada del ticket enviando el código codificado en la URL -->
    <a href="ticket.php?ticket=<?php echo urlencode($ticket); ?>">
        Ver ticket
    </a>

    <!-- Espaciado mediante doble salto de línea -->
    <br><br>

    <!-- Enlace para navegar de regreso a la interfaz de inicio del panel frontal -->
    <a href="../frontend/index.php">
        Volver al inicio
    </a>

<!-- Cierre de la sección del cuerpo -->
</body>

<!-- Cierre del documento HTML -->
</html>