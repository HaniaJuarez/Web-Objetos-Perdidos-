<?php

// Incluye el archivo encargado de verificar que exista una sesión iniciada
require_once 'verificar_sesion.php';

// Incluye el archivo que contiene la configuración y las constantes de Supabase
require_once 'config.php';

// Comprueba que la solicitud recibida haya sido enviada mediante el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // Muestra un mensaje cuando la solicitud no utiliza el método POST
    echo "Solicitud no válida.";

    // Detiene la ejecución del programa
    exit;

}


/* =========================
   DATOS DEL FORMULARIO
   ========================= */

// Obtiene el tipo de acción enviado desde el formulario
$accion = $_POST['accion'] ?? '';

// Obtiene el nombre del objeto enviado desde el formulario
$nombre = $_POST['nombre'] ?? '';

// Obtiene el identificador de la categoría seleccionada
$categoria_id = $_POST['categoria_id'] ?? '';

// Obtiene la descripción pública del objeto
$descripcion_publica = $_POST['descripcion_publica'] ?? '';

// Obtiene la descripción privada del objeto
$descripcion_privada = $_POST['descripcion_privada'] ?? '';

// Obtiene el color del objeto
$color = $_POST['color'] ?? '';

// Obtiene la fecha proporcionada en el formulario
$fecha = $_POST['fecha'] ?? '';

// Obtiene la ubicación proporcionada en el formulario
$ubicacion = $_POST['ubicacion'] ?? '';

// Obtiene el nombre o información del responsable
$responsable = $_POST['responsable'] ?? '';

// Obtiene la información relacionada con el lugar de resguardo
$resguardo = $_POST['resguardo'] ?? '';

// Obtiene el identificador del usuario almacenado en la sesión
$usuario_id = $_SESSION['usuario_id'];


/* =========================
   VALIDACIONES
   ========================= */

// Comprueba que la acción recibida sea "perdido" o "encontrado"
if ($accion !== 'perdido' && $accion !== 'encontrado') {

    // Muestra un mensaje cuando el tipo de reporte no es válido
    echo "Tipo de reporte no válido.";

    // Detiene la ejecución del programa
    exit;

}

// Comprueba que los campos obligatorios tengan información
if (

    // Comprueba que el nombre del objeto no esté vacío
    empty($nombre) ||

    // Comprueba que se haya seleccionado una categoría
    empty($categoria_id) ||

    // Comprueba que exista una descripción pública
    empty($descripcion_publica) ||

    // Comprueba que se haya proporcionado una fecha
    empty($fecha)

) {

    // Muestra un mensaje indicando que faltan campos obligatorios
    echo "Completa los campos obligatorios.";

    // Detiene la ejecución del programa
    exit;

}


/* =========================
   ESTADO
   ========================= */

// Comprueba si el reporte corresponde a un objeto perdido
if ($accion === 'perdido') {

    // Establece el estado del objeto como "Perdido"
    $estado = 'Perdido';

} else {

    // Si la acción no es "perdido", establece el estado como "Encontrado"
    $estado = 'Encontrado';

}


/* =========================
   FOTOGRAFÍA
   ========================= */

// Inicializa la variable que almacenará la URL de la imagen
$imagen_url = null;

// Comprueba que se haya enviado una imagen y que no haya ocurrido un error al subirla
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    // Guarda en $archivo la información del archivo recibido
    $archivo = $_FILES['imagen'];

    // Crea una lista con los tipos de imagen permitidos
    $permitidos = [

        // Permite archivos de imagen JPEG
        'image/jpeg',

        // Permite archivos de imagen PNG
        'image/png',

        // Permite archivos de imagen WebP
        'image/webp'

    ];

    // Comprueba que el tipo de imagen recibida esté dentro de los formatos permitidos
    if (!in_array($archivo['type'], $permitidos)) {

        // Muestra un mensaje indicando que el formato no está permitido
        echo "Formato de imagen no permitido.";

        // Detiene la ejecución del programa
        exit;

    }

    // Comprueba que el tamaño de la imagen no supere los 5 MB
    if ($archivo['size'] > 5 * 1024 * 1024) {

        // Muestra un mensaje indicando que la imagen supera el tamaño permitido
        echo "La imagen no puede superar los 5 MB.";

        // Detiene la ejecución del programa
        exit;

    }

    // Obtiene la extensión del archivo original
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

    // Genera un nombre único para la imagen y conserva su extensión
    $nombre_archivo = uniqid('objeto_', true) . '.' . $extension;

    // Obtiene el contenido del archivo temporal de la imagen
    $contenido = file_get_contents($archivo['tmp_name']);

    // Construye la URL donde se enviará la imagen dentro de Supabase Storage
    $storage_url =
        SUPABASE_URL .
        '/storage/v1/object/objetos/' .
        $nombre_archivo;

    // Define las opciones necesarias para realizar la petición HTTP a Supabase Storage
    $storage_options = [

        // Define las opciones de comunicación mediante HTTP
        'http' => [

            // Indica que se utilizará el método POST para subir la imagen
            'method' => 'POST',

            // Define las cabeceras que se enviarán junto con la imagen
            'header' =>

                // Envía la clave de Supabase mediante la cabecera apikey
                "apikey: " . SUPABASE_KEY . "\r\n" .

                // Envía la clave de Supabase como autorización Bearer
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .

                // Indica el tipo de contenido de la imagen enviada
                "Content-Type: " . $archivo['type'] . "\r\n" .

                // Permite crear o reemplazar un archivo con el mismo nombre
                "x-upsert: true\r\n",

            // Coloca el contenido de la imagen dentro de la petición
            'content' => $contenido,

            // Permite obtener la respuesta aunque el servidor devuelva un error HTTP
            'ignore_errors' => true

        ]

    ];

    // Crea el contexto que contiene las opciones de la petición HTTP
    $storage_context =
        stream_context_create($storage_options);

    // Envía la imagen a Supabase Storage y obtiene la respuesta
    $storage_response =
        file_get_contents(
            $storage_url,
            false,
            $storage_context
        );

    // Comprueba si la subida de la imagen falló
    if ($storage_response === false) {

        // Muestra un mensaje indicando que no se pudo subir la imagen
        echo "No se pudo subir la imagen.";

        // Detiene la ejecución del programa
        exit;

    }

    // Construye la URL pública de la imagen almacenada en Supabase
    $imagen_url =
        SUPABASE_URL .
        '/storage/v1/object/public/objetos/' .
        $nombre_archivo;

}


/* =========================
   DATOS DEL OBJETO
   ========================= */

// Crea un arreglo que contiene toda la información que se guardará del objeto
$datos = [

    // Guarda el nombre del objeto
    'nombre' => $nombre,

    // Guarda la descripción pública del objeto
    'descripcion_publica' => $descripcion_publica,

    // Guarda la descripción privada del objeto
    'descripcion_privada' => $descripcion_privada,

    // Guarda el color del objeto
    'color' => $color,

    // Guarda el estado del objeto, que puede ser Perdido o Encontrado
    'estado' => $estado,

    // Guarda la fecha en que se perdió o encontró el objeto
    'fecha' => $fecha,

    // Guarda la URL de la imagen almacenada en Supabase
    'imagen' => $imagen_url,

    // Guarda el identificador del usuario que realizó el reporte
    'usuario_id' => $usuario_id,

    // Guarda el identificador de la categoría seleccionada
    'categoria_id' => $categoria_id,

    // Guarda la ubicación relacionada con el objeto
    'ubicacion' => $ubicacion,

    // Guarda el responsable relacionado con el objeto
    'responsable' => $responsable,

    // Guarda el lugar donde se encuentra resguardado el objeto
    'resguardo' => $resguardo

];


/* =========================
   GUARDAR EN SUPABASE
   ========================= */

// Construye la URL de la API REST de Supabase para acceder a la tabla objetos
$url = SUPABASE_URL . '/rest/v1/objetos';

// Define las opciones para realizar la petición HTTP a Supabase
$options = [

    // Define las opciones relacionadas con la comunicación HTTP
    'http' => [

        // Indica que se utilizará el método POST para insertar los datos
        'method' => 'POST',

        // Define las cabeceras necesarias para comunicarse con Supabase
        'header' =>

            // Envía la clave de Supabase mediante la cabecera apikey
            "apikey: " . SUPABASE_KEY . "\r\n" .

            // Envía la clave como autorización Bearer
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .

            // Indica que los datos enviados estarán en formato JSON
            "Content-Type: application/json\r\n" .

            // Solicita que Supabase devuelva la información del registro creado
            "Prefer: return=representation\r\n",

        // Convierte el arreglo de datos a formato JSON
        'content' => json_encode($datos),

        // Permite recibir la respuesta aunque exista un error HTTP
        'ignore_errors' => true

    ]

];

// Crea el contexto HTTP utilizando las opciones anteriores
$context = stream_context_create($options);

// Envía los datos del objeto a la API de Supabase
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


/* =========================
   RESPUESTA
   ========================= */

// Comprueba si ocurrió un error al intentar conectarse con Supabase
if ($response === false) {

    // Muestra un mensaje indicando que no se pudo establecer la conexión
    echo "Error al conectar con Supabase.";

    // Detiene la ejecución del programa
    exit;

}

// Convierte la respuesta JSON de Supabase en un arreglo de PHP
$resultado = json_decode($response, true);

// Comprueba si la respuesta contiene una propiedad llamada "message"
if (isset($resultado['message'])) {

    // Muestra un mensaje general indicando que ocurrió un error al registrar el objeto
    echo "Error al registrar el objeto.";

    // Inserta un salto de línea HTML
    echo "<br>";

    // Muestra el mensaje de error recibido desde Supabase de forma segura
    echo htmlspecialchars($resultado['message']);

    // Detiene la ejecución del programa
    exit;

}

// Muestra un encabezado indicando que el objeto fue registrado correctamente
echo "<h1>Objeto registrado correctamente</h1>";

// Inicia un párrafo HTML
echo "<p>";

// Muestra el mensaje de confirmación
echo "El objeto fue guardado en el sistema.";

// Cierra el párrafo HTML
echo "</p>";

// Abre un enlace hacia la página principal
echo '<a href="../frontend/index.php">';

// Muestra el texto del enlace
echo "Volver al inicio";

// Cierra el enlace
echo '</a>';

// Finaliza el código PHP
?>