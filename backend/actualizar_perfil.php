<?php

require_once 'verificar_sesion.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Solicitud no válida.";
    exit;
}


/*
|--------------------------------------------------------------------------
| PROTECCIÓN CSRF
|--------------------------------------------------------------------------
*/

$csrf =
    $_POST['csrf_token'] ?? '';

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

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "El correo no es válido.";
    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR CONTRASEÑA
|--------------------------------------------------------------------------
*/

if ($password !== '') {

    if (strlen($password) < 8) {
        echo "La contraseña debe tener al menos 8 caracteres.";
        exit;
    }

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

$context_get =
    stream_context_create($options_get);

$url_usuario =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id) .
    '&select=id,nombre,correo,foto';

$response_usuario =
    file_get_contents(
        $url_usuario,
        false,
        $context_get
    );

$usuarios =
    json_decode(
        $response_usuario,
        true
    );

if (empty($usuarios)) {
    echo "No se encontró el usuario.";
    exit;
}

$usuario_actual =
    $usuarios[0];

$foto_actual =
    $usuario_actual['foto'] ?? '';


/*
|--------------------------------------------------------------------------
| PREPARAR DATOS
|--------------------------------------------------------------------------
*/

$datos = [
    'nombre' => $nombre,
    'correo' => $correo
];

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

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES['foto']['error'] !== UPLOAD_ERR_OK
    ) {
        echo "Error al subir la fotografía.";
        exit;
    }


    if (
        $_FILES['foto']['size'] > 2 * 1024 * 1024
    ) {
        echo "La fotografía no puede superar 2 MB.";
        exit;
    }


    $tmp =
        $_FILES['foto']['tmp_name'];


    $finfo =
        new finfo(FILEINFO_MIME_TYPE);

    $mime =
        $finfo->file($tmp);


    $tipos_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];


    if (
        !isset($tipos_permitidos[$mime])
    ) {
        echo "Tipo de imagen no permitido.";
        exit;
    }


    $extension =
        $tipos_permitidos[$mime];


    $nombre_archivo =
        'perfil_' .
        $usuario_id .
        '_' .
        bin2hex(random_bytes(8)) .
        '.' .
        $extension;


    $contenido =
        file_get_contents($tmp);


    $url_storage =
        SUPABASE_URL .
        '/storage/v1/object/objetos/' .
        $nombre_archivo;


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


    $respuesta_storage =
        file_get_contents(
            $url_storage,
            false,
            $context_storage
        );


    if ($respuesta_storage === false) {
        echo "No se pudo subir la fotografía.";
        exit;
    }


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

$url_update =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id);


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


$response_update =
    file_get_contents(
        $url_update,
        false,
        $context_update
    );


if ($response_update === false) {
    echo "No se pudieron guardar los cambios.";
    exit;
}


$resultado =
    json_decode(
        $response_update,
        true
    );


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

$_SESSION['nombre'] =
    $nombre;

$_SESSION['correo'] =
    $correo;


/*
|--------------------------------------------------------------------------
| REDIRECCIÓN
|--------------------------------------------------------------------------
*/

header(
    "Location: ../frontend/perfil.php"
);

exit;

?>