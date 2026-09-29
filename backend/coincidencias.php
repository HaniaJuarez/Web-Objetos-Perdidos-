<?php

// Incluye el archivo para validar que exista una sesión de usuario activa antes de ejecutar la consulta.
require_once 'verificar_sesion.php';
// Incluye el archivo de configuración global con las credenciales de Supabase.
require_once 'config.php';

// Establece la cabecera HTTP para indicar que la respuesta devuelta estará en formato JSON.
header('Content-Type: application/json');

// Obtiene el identificador del objeto enviado vía GET; si no existe, asigna una cadena vacía.
$id = $_GET['id'] ?? '';

// Comprueba si el ID está vacío.
if (empty($id)) {
    // Imprime un arreglo JSON vacío si no se especificó un ID de objeto.
    echo json_encode([]);
    // Detiene la ejecución del script.
    exit;
}


/* =========================
   CONFIGURACIÓN DE SUPABASE
   ========================= */

// Arreglo de opciones HTTP para configurar los encabezados de autenticación y contenido hacia Supabase.
$options = [
    'http' => [
        // Define el método de consulta HTTP como GET.
        'method' => 'GET',
        // Define las cabeceras requeridas por la API REST de Supabase (apikey, Bearer Token y Content-Type).
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        // Permite procesar la respuesta aunque el servidor HTTP devuelva un código de error (4xx/5xx).
        'ignore_errors' => true
    ]
];

// Crea el recurso de contexto con las opciones HTTP antes declaradas.
$context = stream_context_create($options);


/* =========================
   OBTENER OBJETO PERDIDO
   ========================= */

// Construye la URL para consultar el objeto perdido específico filtrando por su ID en Supabase.
$url_perdido =
    SUPABASE_URL .
    '/rest/v1/objetos?id=eq.' .
    urlencode($id) .
    '&select=*';

// Realiza la petición GET a Supabase utilizando file_get_contents con el contexto HTTP.
$response = file_get_contents(
    $url_perdido,
    false,
    $context
);

// Comprueba si la solicitud HTTP falló por completo.
if ($response === false) {
    // Retorna una estructura JSON con el mensaje de error correspondiente.
    echo json_encode([
        'error' => 'No se pudo consultar el objeto perdido.'
    ]);
    // Detiene la ejecución del script.
    exit;
}

// Convierte la respuesta JSON recibida en un arreglo asociativo de PHP.
$perdidos = json_decode($response, true);

// Verifica que la respuesta sea un arreglo válido y que no esté vacío.
if (!is_array($perdidos) || empty($perdidos)) {
    // Devuelve un JSON vacío si el objeto perdido no fue encontrado.
    echo json_encode([]);
    // Finaliza la ejecución.
    exit;
}

// Extrae el primer elemento del arreglo correspondiente al objeto perdido buscado.
$perdido = $perdidos[0];


/* =========================
   OBTENER OBJETOS ENCONTRADOS
   ========================= */

// Construye la URL para traer todos los objetos cuya propiedad estado sea 'Encontrado'.
$url_encontrados =
    SUPABASE_URL .
    '/rest/v1/objetos?estado=eq.Encontrado&select=*';

// Ejecuta la petición HTTP para consultar los objetos encontrados registrados.
$response = file_get_contents(
    $url_encontrados,
    false,
    $context
);

// Comprueba si la petición a Supabase devolvió un error de conexión.
if ($response === false) {
    // Imprime un JSON indicando la falla al consultar objetos encontrados.
    echo json_encode([
        'error' => 'No se pudieron consultar los objetos encontrados.'
    ]);
    // Cancela la ejecución restante del script.
    exit;
}

// Convierte el listado JSON de objetos encontrados en un arreglo asociativo de PHP.
$encontrados = json_decode($response, true);

// Evalúa si el resultado devuelto no es un arreglo válido.
if (!is_array($encontrados)) {
    // Retorna un arreglo JSON vacío.
    echo json_encode([]);
    // Detiene el script.
    exit;
}


/* =========================
   COMPARAR OBJETOS
   ========================= */

// Inicializa el arreglo donde se almacenarán las coincidencias que superen el umbral de puntos.
$coincidencias = [];

// Recorre cada uno de los objetos encontrados recuperados de la base de datos.
foreach ($encontrados as $encontrado) {

    // Inicializa el puntaje de coincidencia para el objeto encontrado en evaluación.
    $puntos = 0;


    /* =========================
       CATEGORÍA - 30 PUNTOS
       ========================= */

    // Evalúa si ambos objetos poseen una categoría definida y si coinciden exactamente.
    if (
        !empty($perdido['categoria_id']) &&
        !empty($encontrado['categoria_id']) &&
        $perdido['categoria_id'] ==
        $encontrado['categoria_id']
    ) {

        // Suma 30 puntos al puntaje total por coincidir en categoría.
        $puntos += 30;

    }


    /* =========================
       NOMBRE - 25 PUNTOS
       ========================= */

    // Normaliza el nombre del objeto perdido convirtiéndolo a minúsculas y eliminando espacios adicionales.
    $nombre_perdido =
        strtolower(trim($perdido['nombre'] ?? ''));

    // Normaliza el nombre del objeto encontrado a minúsculas sin espacios innecesarios.
    $nombre_encontrado =
        strtolower(trim($encontrado['nombre'] ?? ''));

    // Comprueba que los nombres no estén vacíos y si uno contiene al otro como subcadena.
    if (
        $nombre_perdido !== '' &&
        $nombre_encontrado !== '' &&
        (
            strpos($nombre_encontrado, $nombre_perdido) !== false ||
            strpos($nombre_perdido, $nombre_encontrado) !== false
        )
    ) {

        // Otorga 25 puntos por coincidencia parcial o total en el nombre.
        $puntos += 25;

    }


    /* =========================
       COLOR - 20 PUNTOS
       ========================= */

    // Limpia y convierte a minúsculas el valor del color del objeto perdido.
    $color_perdido =
        strtolower(trim($perdido['color'] ?? ''));

    // Limpia y convierte a minúsculas el valor del color del objeto encontrado.
    $color_encontrado =
        strtolower(trim($encontrado['color'] ?? ''));

    // Comprueba que existan valores de color y que coincidan exactamente.
    if (
        $color_perdido !== '' &&
        $color_encontrado !== '' &&
        $color_perdido === $color_encontrado
    ) {

        // Suma 20 puntos por coincidencia exacta de color.
        $puntos += 20;

    }


    /* =========================
       UBICACIÓN - 15 PUNTOS
       ========================= */

    // Prepara la cadena de ubicación del objeto perdido a minúsculas y sin espacios externos.
    $ubicacion_perdida =
        strtolower(trim($perdido['ubicacion'] ?? ''));

    // Prepara la cadena de ubicación del objeto encontrado a minúsculas sin espacios en los extremos.
    $ubicacion_encontrada =
        strtolower(trim($encontrado['ubicacion'] ?? ''));

    // Verifica la existencia de ambas ubicaciones y busca si se contienen mutuamente.
    if (
        $ubicacion_perdida !== '' &&
        $ubicacion_encontrada !== '' &&
        (
            strpos(
                $ubicacion_encontrada,
                $ubicacion_perdida
            ) !== false
            ||
            strpos(
                $ubicacion_perdida,
                $ubicacion_encontrada
            ) !== false
        )
    ) {

        // Suma 15 puntos por similitud en la ubicación.
        $puntos += 15;

    }


    /* =========================
       FECHA - 10 PUNTOS
       ========================= */

    // Comprueba si ambos registros poseen un valor en el campo fecha.
    if (
        !empty($perdido['fecha']) &&
        !empty($encontrado['fecha'])
    ) {

        // Convierte la cadena de fecha del objeto perdido a marca de tiempo Unix.
        $fecha1 =
            strtotime($perdido['fecha']);

        // Convierte la fecha del objeto encontrado a marca de tiempo Unix.
        $fecha2 =
            strtotime($encontrado['fecha']);

        // Comprueba que las conversiones a timestamp hayan sido exitosas.
        if (
            $fecha1 !== false &&
            $fecha2 !== false
        ) {

            // Calcula la diferencia absoluta en días dividiendo los segundos de la resta entre 86400 (segundos de un día).
            $diferencia =
                abs($fecha1 - $fecha2) / 86400;

            // Si la diferencia entre las fechas es de 7 días o menos, asigna puntos adicionales.
            if ($diferencia <= 7) {
                $puntos += 10;
            }

        }

    }


    /* =========================
       GUARDAR COINCIDENCIA
       ========================= */

    // Filtra para considerar como coincidencia válida solo aquellos objetos que obtengan 40 puntos o más.
    if ($puntos >= 40) {

        // Agrega un arreglo asociativo con los datos del objeto encontrado y su puntaje al listado de coincidencias.
        $coincidencias[] = [

            'id' =>
                $encontrado['id'],

            'nombre' =>
                $encontrado['nombre'] ?? '',

            'color' =>
                $encontrado['color'] ?? '',

            'estado' =>
                $encontrado['estado'] ?? '',

            'imagen' =>
                $encontrado['imagen'] ?? '',

            'puntos' =>
                $puntos

        ];

    }

}


/* =========================
   ORDENAR DE MAYOR A MENOR
   ========================= */

// Ordena el arreglo de coincidencias en orden descendente tomando como criterio la propiedad 'puntos'.
usort(
    $coincidencias,
    function ($a, $b) {

        return $b['puntos'] - $a['puntos'];

    }
);


/* =========================
   MOSTRAR RESULTADOS
   ========================= */

// Imprime el listado ordenado de coincidencias en formato JSON como respuesta final.
echo json_encode($coincidencias);

?>