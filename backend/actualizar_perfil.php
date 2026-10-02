<?php

/**
 * Script de Actualización de Perfil de Usuario
 *
 * Este script procesa las solicitudes POST para actualizar los datos personales,
 * la contraseña y la foto de perfil de un usuario autenticado en el sistema,
 * interactuando con la API REST y el Storage de Supabase.
 */

// Inclusión de scripts de verificación de sesión y archivo de configuración básica (credenciales Supabase)
require_once 'verificar_sesion.php';
require_once 'config.php';

/**
 * Validar método de solicitud
 * Solo se permiten peticiones mediante el método HTTP POST.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Solicitud no válida.";
    exit;
}


/*
|--------------------------------------------------------------------------
| PROTECCIÓN CSRF
|--------------------------------------------------------------------------
*/

// Obtención del token CSRF enviado desde el formulario
$csrf =
    $_POST['csrf_token'] ?? '';

// Verificación de la presencia del token en la sesión y comparación mediante hash seguro
if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $csrf
    )
) {
    echo "Solicitud no válida.";
    exit;
}


/*
|--------------------------------------------------------------------------
| DATOS
|--------------------------------------------------------------------------
*/

// Asignación de variables desde la sesión y saneamiento básico de entradas POST
$usuario_id =
    $_SESSION['usuario_id'];

$nombre =
    trim($_POST['nombre'] ?? '');

$correo =
    trim($_POST['correo'] ?? '');

$password =
    $_POST['password'] ?? '';

$password_confirmacion =
    $_POST['password_confirmacion'] ?? '';


// Validar que los campos obligatorios (nombre y correo) no estén vacíos
if (
    $nombre === '' ||
    $correo === ''
) {
    echo "El nombre y el correo son obligatorios.";
    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR CORREO
|--------------------------------------------------------------------------
*/

// Comprobar el formato correcto de la dirección de correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "El correo no es válido.";
    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR CONTRASEÑA
|--------------------------------------------------------------------------
*/

// Si el usuario ingresó una contraseña nueva, se realizan las verificaciones
if ($password !== '') {

    // Validar longitud mínima de 8 caracteres
    if (strlen($password) < 8) {
        echo "La contraseña debe tener al menos 8 caracteres.";
        exit;
    }

    // Validar que la contraseña y su confirmación coincidan
    if ($password !== $password_confirmacion) {
        echo "Las contraseñas no coinciden.";
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| OBTENER DATOS ACTUALES
|--------------------------------------------------------------------------
*/

// Configuración de encabezados HTTP para la consulta de datos del usuario en Supabase mediante GET
$options_get = [
    'http' => [
        'method' => 'GET',
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        'ignore_errors' => true
    ]
];

// Creación del contexto de transmisión HTTP
$context_get =
    stream_context_create($options_get);

// URL de consulta de la tabla 'usuarios' filtrado por ID
$url_usuario =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id) .
    '&select=id,nombre,correo,foto';

// Ejecución de la petición HTTP GET
$response_usuario =
    file_get_contents(
        $url_usuario,
        false,
        $context_get
    );

// Decodificación de la respuesta JSON a un arreglo asociativo
$usuarios =
    json_decode(
        $response_usuario,
        true
    );

// Verificar si existe el registro del usuario
if (empty($usuarios)) {
    echo "No se encontró el usuario.";
    exit;
}

// Extracción de datos del usuario
$usuario_actual =
    $usuarios[0];

$foto_actual =
    $usuario_actual['foto'] ?? '';


/*
|--------------------------------------------------------------------------
| PREPARAR DATOS
|--------------------------------------------------------------------------
*/

// Estructuración de datos base que serán actualizados en la base de datos
$datos = [
    'nombre' => $nombre,
    'correo' => $correo
];

// Si se especificó una nueva contraseña, generar el hash seguro y adjuntarlo a los datos
if ($password !== '') {

    $datos['password'] =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );
}


/*
|--------------------------------------------------------------------------
| SUBIR FOTO
|--------------------------------------------------------------------------
*/

// Comprobar si se ha adjuntado un archivo de fotografía para subir
if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    // Validar que no existan errores en la subida del archivo
    if (
        $_FILES['foto']['error'] !== UPLOAD_ERR_OK
    ) {
        echo "Error al subir la fotografía.";
        exit;
    }


    // Comprobar que el tamaño del archivo no exceda los 2 MB
    if (
        $_FILES['foto']['size'] > 2 * 1024 * 1024
    ) {
        echo "La fotografía no puede superar 2 MB.";
        exit;
    }


    // Obtención de la ruta temporal del archivo subido
    $tmp =
        $_FILES['foto']['tmp_name'];


    // Inspección segura del tipo MIME mediante finfo
    $finfo =
        new finfo(FILEINFO_MIME_TYPE);

    $mime =
        $finfo->file($tmp);


    // Mapeo de formatos de imagen permitidos
    $tipos_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];


    // Verificar que el tipo MIME del archivo sea permitido
    if (
        !isset($tipos_permitidos[$mime])
    ) {
        echo "Tipo de imagen no permitido.";
        exit;
    }


    // Obtener la extensión adecuada según el tipo MIME
    $extension =
        $tipos_permitidos[$mime];


    // Generar un nombre único de archivo utilizando un hash aleatorio
    $nombre_archivo =
        'perfil_' .
        $usuario_id .
        '_' .
        bin2hex(random_bytes(8)) .
        '.' .
        $extension;


    // Leer el contenido binario del archivo temporal
    $contenido =
        file_get_contents($tmp);


    // Definir la URL de destino en el Storage de Supabase
    $url_storage =
        SUPABASE_URL .
        '/storage/v1/object/objetos/' .
        $nombre_archivo;


    // Configurar la petición HTTP POST para cargar la imagen en Supabase Storage
    $options_storage = [
        'http' => [
            'method' => 'POST',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: " . $mime . "\r\n",
            'content' => $contenido,
            'ignore_errors' => true
        ]
    ];


    $context_storage =
        stream_context_create(
            $options_storage
        );


    // Enviar el archivo binario a Supabase Storage
    $respuesta_storage =
        file_get_contents(
            $url_storage,
            false,
            $context_storage
        );


    // Verificar si falló la subida de la imagen
    if ($respuesta_storage === false) {
        echo "No se pudo subir la fotografía.";
        exit;
    }


    // Asignar la URL pública de la foto guardada al arreglo de datos
    $datos['foto'] =
        SUPABASE_URL .
        '/storage/v1/object/public/objetos/' .
        $nombre_archivo;
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR USUARIO
|--------------------------------------------------------------------------
*/

// Endpoint para actualizar el registro del usuario en la REST API de Supabase via PATCH
$url_update =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id);


// Configuración de la petición HTTP PATCH
$options_update = [
    'http' => [
        'method' => 'PATCH',
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Prefer: return=representation\r\n",
        'content' =>
            json_encode($datos),
        'ignore_errors' => true
    ]
];


$context_update =
    stream_context_create(
        $options_update
    );


// Ejecución de la petición PATCH de actualización
$response_update =
    file_get_contents(
        $url_update,
        false,
        $context_update
    );


// Validar la correcta ejecución de la petición
if ($response_update === false) {
    echo "No se pudieron guardar los cambios.";
    exit;
}


// Decodificación de la respuesta obtenida
$resultado =
    json_decode(
        $response_update,
        true
    );


// Verificar si la API devolvió un mensaje de error
if (
    isset($resultado['message'])
) {

    echo "Error al actualizar el perfil.";
    echo "<br>";
    echo htmlspecialchars(
        $resultado['message']
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SESIÓN
|--------------------------------------------------------------------------
*/

// Sincronizar las variables de la sesión activa con los datos actualizados
$_SESSION['nombre'] =
    $nombre;

$_SESSION['correo'] =
    $correo;


/*
|--------------------------------------------------------------------------
| REDIRECCIÓN
|--------------------------------------------------------------------------
*/

// Redirigir al usuario de vuelta al panel de perfil
header(
    "Location: ../frontend/perfil.php"
);

exit;

?>