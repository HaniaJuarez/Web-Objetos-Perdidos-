<?php

require_once 'verificar_sesion.php';
require_once 'config.php';


/*
|--------------------------------------------------------------------------
| Verificar que sea docente
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {

    echo "Acceso denegado.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar método
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo "Solicitud no válida.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener datos
|--------------------------------------------------------------------------
*/

$objeto_id = $_POST['objeto_id'] ?? '';

$observaciones =
    trim($_POST['observaciones'] ?? '');


if (empty($objeto_id)) {

    echo "No se especificó el objeto.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el objeto exista
|--------------------------------------------------------------------------
*/

$url_objeto =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?id=eq.' .
    urlencode($objeto_id) .
    '&select=id,estado';


$options_objeto = [

    'http' => [

        'method' => 'GET',

        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        'ignore_errors' => true

    ]

];


$context_objeto =
    stream_context_create(
        $options_objeto
    );


$response_objeto =
    file_get_contents(
        $url_objeto,
        false,
        $context_objeto
    );


if ($response_objeto === false) {

    echo "No se pudo consultar el objeto.";
    exit;

}


$objetos =
    json_decode(
        $response_objeto,
        true
    );


if (
    !is_array($objetos) ||
    empty($objetos)
) {

    echo "Objeto no encontrado.";
    exit;

}


$objeto =
    $objetos[0];


/*
|--------------------------------------------------------------------------
| Verificar que esté en resguardo
|--------------------------------------------------------------------------
*/

if (
    ($objeto['estado'] ?? '') !==
    'En resguardo'
) {

    echo "El objeto no se encuentra en resguardo.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Cambiar objeto a Recuperado
|--------------------------------------------------------------------------
*/

$datos_objeto = [

    'estado' => 'Recuperado'

];


$url_actualizar_objeto =
    SUPABASE_URL .
    '/rest/v1/objetos?id=eq.' .
    urlencode($objeto_id);


$options_actualizar_objeto = [

    'http' => [

        'method' => 'PATCH',

        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Prefer: return=representation\r\n",

        'content' =>
            json_encode($datos_objeto),

        'ignore_errors' => true

    ]

];


$context_actualizar_objeto =
    stream_context_create(
        $options_actualizar_objeto
    );


$response_actualizar_objeto =
    file_get_contents(
        $url_actualizar_objeto,
        false,
        $context_actualizar_objeto
    );


if ($response_actualizar_objeto === false) {

    echo "No se pudo actualizar el estado del objeto.";
    exit;

}


$resultado_objeto =
    json_decode(
        $response_actualizar_objeto,
        true
    );


if (
    isset($resultado_objeto['message']) ||
    isset($resultado_objeto['error'])
) {

    echo "Error al actualizar el objeto.";

    echo "<br>";

    echo htmlspecialchars(
        $resultado_objeto['message']
        ?? $resultado_objeto['error']
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Buscar reclamación relacionada
|--------------------------------------------------------------------------
*/

$url_reclamacion =
    SUPABASE_URL .
    '/rest/v1/reclamaciones' .
    '?objeto_id=eq.' .
    urlencode($objeto_id) .
    '&estado=eq.Aprobada' .
    '&select=id';


$options_reclamacion = [

    'http' => [

        'method' => 'GET',

        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        'ignore_errors' => true

    ]

];


$context_reclamacion =
    stream_context_create(
        $options_reclamacion
    );


$response_reclamacion =
    file_get_contents(
        $url_reclamacion,
        false,
        $context_reclamacion
    );


if ($response_reclamacion !== false) {

    $reclamaciones =
        json_decode(
            $response_reclamacion,
            true
        );


    /*
    |--------------------------------------------------------------------------
    | Cerrar reclamación
    |--------------------------------------------------------------------------
    */

    if (
        is_array($reclamaciones) &&
        !empty($reclamaciones)
    ) {

        $reclamacion_id =
            $reclamaciones[0]['id'];


        $datos_reclamacion = [

            'estado' => 'Cerrada'

        ];


        $url_cerrar =
            SUPABASE_URL .
            '/rest/v1/reclamaciones?id=eq.' .
            urlencode($reclamacion_id);


        $options_cerrar = [

            'http' => [

                'method' => 'PATCH',

                'header' =>
                    "apikey: " . SUPABASE_KEY . "\r\n" .
                    "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Prefer: return=representation\r\n",

                'content' =>
                    json_encode($datos_reclamacion),

                'ignore_errors' => true

            ]

        ];


        $context_cerrar =
            stream_context_create(
                $options_cerrar
            );


        file_get_contents(
            $url_cerrar,
            false,
            $context_cerrar
        );

    }

}


/*
|--------------------------------------------------------------------------
| Mostrar mensaje de éxito
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Entrega registrada</title>

</head>

<body>

    <h1>
        Entrega registrada correctamente
    </h1>

    <p>
        El objeto ha sido marcado como
        <strong>Recuperado</strong>.
    </p>

    <p>
        La reclamación relacionada ha sido
        marcada como <strong>Cerrada</strong>.
    </p>

    <br>

    <a href="../frontend/resguardo_docente.php">
        Volver a objetos en resguardo
    </a>

    <br><br>

    <a href="../frontend/docente.php">
        Volver al panel docente
    </a>

</body>

</html>