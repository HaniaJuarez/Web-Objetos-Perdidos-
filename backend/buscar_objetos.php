<?php

// Incluye el archivo de configuración externo que contiene las constantes requeridas (como SUPABASE_URL y SUPABASE_KEY).
require_once 'config.php';

// Obtiene el término de búsqueda enviado mediante el parámetro 'q' por URL (método GET); si no existe, asigna una cadena vacía.
$busqueda = $_GET['q'] ?? '';

// Elimina los espacios en blanco sobrantes al inicio y al final del texto ingresado en la búsqueda.
$busqueda = trim($busqueda);

// Evalúa si la cadena de búsqueda está completamente vacía después de limpiar los espacios.
if ($busqueda === '') {

    // Devuelve un arreglo JSON vacío [] indicando que no hay criterios para buscar.
    echo json_encode([]);

    // Detiene inmediatamente la ejecución del script.
    exit;
}


// Construye el filtro de búsqueda para la API de Supabase utilizando la sintaxis 'or' e 'ilike' (búsqueda no sensible a mayúsculas/minúsculas) 
// para evaluar coincidencias parciales en los campos nombre, descripcion_publica y color.
$filtro =
    'or=(' .
    'nombre.ilike.*' . urlencode($busqueda) . '*,' .
    'descripcion_publica.ilike.*' . urlencode($busqueda) . '*,' .
    'color.ilike.*' . urlencode($busqueda) . '*' .
    ')';


// Construye la URL completa hacia Supabase concatenando la base, la tabla "objetos", la consulta de relación con "categorias", 
// el filtro de búsqueda previamente generado y el ordenamiento descendente por el campo "id".
$url =
    SUPABASE_URL .
    '/rest/v1/objetos?select=*,categorias(nombre)&' .
    $filtro .
    '&order=id.desc';


// Define un arreglo con la configuración necesaria para la petición HTTP a la API REST.
$options = [
    // Define la sección de configuración correspondiente al protocolo HTTP.
    'http' => [
        // Establece el método de la solicitud HTTP como "GET".
        'method' => 'GET',

        // Define las cabeceras HTTP requeridas para la autenticación en Supabase (API key, Bearer Token) y especifica el tipo de contenido JSON.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        // Permite capturar la respuesta recibida incluso si el servidor de la API responde con un código de error HTTP (como 4xx o 5xx).
        'ignore_errors' => true
    ]
];


// Crea un recurso de contexto de flujo (stream context) utilizando las opciones HTTP definidas arriba.
$context =
    stream_context_create($options);


// Realiza la petición HTTP GET a la URL de Supabase usando el contexto de flujo y guarda la respuesta recibida.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// Establece la cabecera HTTP de respuesta indicando al navegador o cliente que el contenido enviado será en formato JSON.
header('Content-Type: application/json');


// Comprueba si la petición falló a nivel de red o conexión.
if ($response === false) {

    // Devuelve una estructura JSON con un mensaje de error explicativo.
    echo json_encode([
        'error' => 'Error al realizar la búsqueda.'
    ]);

    // Finaliza la ejecución del script PHP.
    exit;
}


// Imprime directamente la respuesta JSON obtenida desde el servicio de Supabase.
echo $response;

?>