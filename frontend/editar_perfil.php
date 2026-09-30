<?php

// Requiere el script que verifica si existe una sesión activa de usuario
require_once '../backend/verificar_sesion.php';
// Requiere el archivo de configuración con constantes como SUPABASE_URL y SUPABASE_KEY
require_once '../backend/config.php';

// Genera un token CSRF único de 32 bytes si no existe previamente en la sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}

// Almacena el ID del usuario autenticado proveniente de la sesión
$usuario_id = $_SESSION['usuario_id'];

// Configura las cabeceras HTTP y el método GET para la petición a la API de Supabase
$options = [
    'http' => [
        'method' => 'GET',
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        'ignore_errors' => true // Permite capturar la respuesta incluso si devuelve un código de error HTTP
    ]
];

// Crea el contexto de flujo con las opciones de HTTP configuradas arriba
$context =
    stream_context_create($options);

// Construye la URL del endpoint REST de Supabase filtrando por el ID del usuario logueado
$url =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id) .
    '&select=id,nombre,correo,foto';

// Realiza la petición HTTP GET a Supabase para obtener los datos del usuario
$response =
    file_get_contents(
        $url,
        false,
        $context
    );

// Decodifica la respuesta JSON recibida a un arreglo asociativo de PHP
$usuarios =
    json_decode(
        $response,
        true
    );

// Verifica si la consulta no devolvió ningún registro de usuario
if (empty($usuarios)) {
    echo "No se encontró el usuario.";
    exit; // Detiene la ejecución del script si el usuario no existe
}

// Extrae el primer resultado devuelto por la consulta a la base de datos
$usuario = $usuarios[0];

// Asigna la ruta o URL de la foto de perfil o una cadena vacía en caso de ser nula
$foto = $usuario['foto'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Configuración del juego de caracteres a UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configuración para la adaptabilidad del diseño en dispositivos móviles (Responsive Web Design) -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título de la pestaña de la página -->
    <title>Editar perfil</title>

    <!-- Vinculación de la hoja de estilos CSS externa para el formulario de edición -->
    <link
        rel="stylesheet"
        href="css/editar_perfil.css"
    >

</head>

<body>

<!-- Contenedor principal del formulario de edición de perfil -->
<div class="contenedor">

    <!-- Enlace para regresar a la vista del perfil principal -->
    <a href="perfil.php">
        ← Volver al perfil
    </a>

    <!-- Encabezado principal de la vista -->
    <h1>
        Editar perfil
    </h1>


    <!-- Condicional PHP: muestra la foto actual únicamente si el usuario tiene una registrada -->
    <?php if (!empty($foto)): ?>

        <!-- Contenedor para previsualizar la foto de perfil guardada actualmente -->
        <div class="foto-actual">

            <!-- Muestra la imagen de perfil sanitizando la ruta/URL para evitar XSS -->
            <img
                src="<?php echo htmlspecialchars($foto); ?>"
                alt="Foto actual"
            >

        </div>

    <?php endif; ?>


    <!-- Formulario que envía los datos al backend vía POST y soporta carga de archivos (multipart/form-data) -->
    <form
        action="../backend/actualizar_perfil.php"
        method="POST"
        enctype="multipart/form-data"
    >

        <!-- Campo oculto para enviar el token CSRF y validar la autenticidad de la petición -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
        >


        <!-- Etiqueta para la selección del archivo de la foto de perfil -->
        <label for="foto">
            Foto de perfil
        </label>

        <!-- Campo de entrada para subir una nueva imagen filtrando por tipos MIME permitidos -->
        <input
            type="file"
            id="foto"
            name="foto"
            accept="image/jpeg,image/png,image/webp"
        >

        <!-- Texto de ayuda indicando los formatos aceptados y el peso máximo permitido -->
        <small>
            JPG, PNG o WEBP. Máximo 2 MB.
        </small>


        <!-- Etiqueta para el campo del nombre -->
        <label for="nombre">
            Nombre
        </label>

        <!-- Campo de texto para el nombre, precargado con el valor de la base de datos de forma segura -->
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
            maxlength="100"
            required
        >


        <!-- Etiqueta para el campo del correo electrónico -->
        <label for="correo">
            Correo
        </label>

        <!-- Campo de correo electrónico, precargado con el valor actual sanitizado -->
        <input
            type="email"
            id="correo"
            name="correo"
            value="<?php echo htmlspecialchars($usuario['correo']); ?>"
            maxlength="120"
            required
        >


        <!-- Subtítulo de la sección de seguridad/contraseña -->
        <h2>
            Cambiar contraseña
        </h2>

        <!-- Nota informativa sobre el cambio opcional de clave -->
        <p class="ayuda">
            Déjala vacía si no deseas cambiarla.
        </p>


        <!-- Etiqueta para la nueva contraseña -->
        <label for="password">
            Nueva contraseña
        </label>

        <!-- Campo para ingresar la nueva contraseña opcional -->
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
        >


        <!-- Etiqueta para confirmar la nueva contraseña -->
        <label for="password_confirmacion">
            Confirmar contraseña
        </label>

        <!-- Campo para confirmar la nueva contraseña opcional -->
        <input
            type="password"
            id="password_confirmacion"
            name="password_confirmacion"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
        >


        <!-- Botón para enviar el formulario y guardar la actualización del perfil -->
        <button type="submit">
            Guardar cambios
        </button>

    </form>

</div>

</body>

</html>