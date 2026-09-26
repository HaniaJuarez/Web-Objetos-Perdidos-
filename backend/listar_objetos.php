<?php

// Incluye el archivo de configuración externo que contiene las constantes requeridas (como SUPABASE_URL y SUPABASE_KEY).
require_once 'config.php';

// Construye la URL de la API de Supabase concatenando la base con el endpoint "objetos", solicitando todos los campos (*), 
// incluyendo la relación de la tabla "categorias" para obtener su "nombre", y ordenando los resultados por "id" de forma descendente.
$url =
    SUPABASE_URL .
    '/rest/v1/objetos?select=*,categorias(nombre)&order=id.desc';

// Define un arreglo de opciones para configurar el contexto de la petición HTTP.
$options = [
    // Define el bloque de configuración para el protocolo HTTP.
    'http' => [
        // Especifica el método de la petición HTTP como "GET".
        'method' => 'GET',

        // Define las cabeceras HTTP necesarias para autenticarse en Supabase (llave API y token de autorización Bearer) y especifica el tipo de contenido JSON.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        // Permite capturar la respuesta y evitar que PHP genere advertencias incluso si la API responde con un código de error HTTP (4xx o 5xx).
        'ignore_errors' => true
    ]
];

// Crea un recurso de contexto de flujo (stream context) pasando la configuración de opciones definida previamente.
$context = stream_context_create($options);

// Realiza la petición HTTP GET a la URL de Supabase usando el contexto configurado y almacena el resultado en la variable $response.
$response = file_get_contents(
    $url,
    false,
    $context
);

// Establece la cabecera HTTP de la respuesta del servidor para indicar al cliente que la salida es de tipo JSON.
header('Content-Type: application/json');

// Comprueba si la petición falló completamente (por ejemplo, si no hay conexión a internet o la URL no es válida).
if ($response === false) {

    // Imprime un mensaje de error en formato JSON indicando la falla de conexión.
    echo json_encode([
        'error' => 'No se pudo conectar con Supabase.'
    ]);

    // Detiene la ejecución del script inmediatamente.
    exit;
}

// Imprime la respuesta JSON obtenida directamente desde la API de Supabase.
echo $response;

?>