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
| Verificar método POST
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

$reclamacion_id = $_POST['reclamacion_id'] ?? '';
$accion = $_POST['accion'] ?? '';

$docente_id = $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| Validar datos
|--------------------------------------------------------------------------
*/

if (empty($reclamacion_id) || empty($accion)) {

    echo "Datos incompletos.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Determinar acción
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {

    $nuevo_estado = 'Aprobada';

} elseif ($accion === 'rechazar') {

    $nuevo_estado = 'Rechazada';

} else {

    echo "Acción no válida.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Si se aprueba, primero obtenemos la reclamación
|--------------------------------------------------------------------------
*/

$objeto_id = null;


if ($accion === 'aprobar') {

    $url_reclamacion =
        SUPABASE_URL .
        '/rest/v1/reclamaciones' .
        '?id=eq.' .
        urlencode($reclamacion_id) .
        '&select=objeto_id';


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
        stream_context_create($options_reclamacion);


    $response_reclamacion =
        file_get_contents(
            $url_reclamacion,
            false,
            $context_reclamacion
        );


    if ($response_reclamacion === false) {

        echo "No se pudo consultar la reclamación.";
        exit;

    }


    $datos_reclamacion =
        json_decode(
            $response_reclamacion,
            true
        );


    if (
        !is_array($datos_reclamacion) ||
        empty($datos_reclamacion)
    ) {

        echo "No se encontró la reclamación.";
        exit;

    }


    $objeto_id =
        $datos_reclamacion[0]['objeto_id'] ?? null;


    if (empty($objeto_id)) {

        echo "La reclamación no tiene un objeto asociado.";
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Actualizar reclamación
|--------------------------------------------------------------------------
*/

$datos = [

    'estado' => $nuevo_estado,

    'docente_id' => (int)$docente_id

];


$url =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?id=eq.' .
    urlencode($reclamacion_id);


$options = [

    'http' => [

        'method' => 'PATCH',

        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Prefer: return=representation\r\n",

        'content' => json_encode($datos),

        'ignore_errors' => true

    ]

];


$context =
    stream_context_create($options);


$response =
    file_get_contents(
        $url,
        false,
        $context
    );


if ($response === false) {

    echo "Error al actualizar la reclamación.";
    exit;

}


$resultado =
    json_decode(
        $response,
        true
    );


if (
    isset($resultado['message']) ||
    isset($resultado['error'])
) {

    echo "Error al actualizar la reclamación.";
    echo "<br>";

    echo htmlspecialchars(
        $resultado['message']
        ?? $resultado['error']
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Si la reclamación fue aprobada
| cambiar objeto encontrado → En resguardo
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {


    $datos_objeto = [

        'estado' => 'En resguardo'

    ];


    $url_objeto =
        SUPABASE_URL .
        '/rest/v1/objetos?id=eq.' .
        urlencode($objeto_id);


    $options_objeto = [

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

        echo "La reclamación fue aprobada, pero no se pudo actualizar el objeto.";
        exit;

    }


    $resultado_objeto =
        json_decode(
            $response_objeto,
            true
        );


    if (
        isset($resultado_objeto['message']) ||
        isset($resultado_objeto['error'])
    ) {

        echo "La reclamación fue aprobada, pero ocurrió un error al cambiar el estado del objeto.";

        echo "<br>";

        echo htmlspecialchars(
            $resultado_objeto['message']
            ?? $resultado_objeto['error']
        );

        exit;

    }

    /*
|--------------------------------------------------------------------------
| Registrar cambio en el historial
|--------------------------------------------------------------------------
*/

    $datos_historial = [

        'objeto_id' => (int)$objeto_id,

        'estado_anterior' => 'Encontrado',

        'estado_nuevo' => 'En resguardo',

        'usuario_id' => (int)$docente_id,

        'descripcion' => 'Reclamación aprobada por docente'

    ];


    $url_historial =
        SUPABASE_URL .
        '/rest/v1/historial_objetos';


    $options_historial = [

        'http' => [

            'method' => 'POST',

            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: application/json\r\n" .
                "Prefer: return=representation\r\n",

            'content' =>
                json_encode($datos_historial),

            'ignore_errors' => true

        ]

    ];


    $context_historial =
        stream_context_create(
            $options_historial
        );


    $response_historial =
        file_get_contents(
            $url_historial,
            false,
            $context_historial
        );


    if ($response_historial === false) {

        echo "El objeto fue enviado a resguardo, pero no se pudo registrar el historial.";
        exit;

    }


    $resultado_historial =
        json_decode(
            $response_historial,
            true
        );


    if (
        isset($resultado_historial['message']) ||
        isset($resultado_historial['error'])
    ) {

        echo "El objeto fue enviado a resguardo, pero ocurrió un error al registrar el historial.";

        echo "<br>";

        echo htmlspecialchars(
            $resultado_historial['message']
            ?? $resultado_historial['error']
        );

        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Regresar al panel de reclamaciones
|--------------------------------------------------------------------------
*/

header(
    "Location: ../frontend/reclamaciones_docente.php"
);

exit;

?>