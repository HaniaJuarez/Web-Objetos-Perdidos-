<?php

// Incluye el archivo de verificación de sesión ubicado en la carpeta backend.
require_once '../backend/verificar_sesion.php';
// Incluye el archivo de configuración global con las credenciales de Supabase.
require_once '../backend/config.php';

// Obtiene el identificador del usuario autenticado guardado en la sesión.
$usuario_id = $_SESSION['usuario_id'];

// Define las opciones de configuración para las peticiones HTTP GET enviadas a la API de Supabase.
$options = [
    'http' => [
        // Método de petición HTTP.
        'method' => 'GET',
        // Encabezados con las llaves de autenticación de Supabase y el tipo de contenido esperado.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        // Ignora errores HTTP para permitir leer el cuerpo de la respuesta aunque devuelva un código de error.
        'ignore_errors' => true
    ]
];

// Crea el contexto de transmisión con las opciones configuradas para reusarlo en las peticiones.
$context = stream_context_create($options);

/*
|--------------------------------------------------------------------------
| OBTENER DATOS ACTUALES DEL USUARIO
|--------------------------------------------------------------------------
*/

// Construye la URL para consultar en Supabase los datos del usuario actual (id, nombre, correo, rol, foto).
$url_usuario =
    SUPABASE_URL .
    '/rest/v1/usuarios?id=eq.' .
    urlencode($usuario_id) .
    '&select=id,nombre,correo,rol,foto';

// Realiza la petición GET a Supabase para obtener el perfil del usuario.
$response_usuario = file_get_contents(
    $url_usuario,
    false,
    $context
);

// Inicializa la variable que contendrá los datos del usuario como un arreglo vacío.
$usuarios = [];

// Si la petición a Supabase fue exitosa, decodifica la respuesta JSON en un arreglo asociativo.
if ($response_usuario !== false) {
    $usuarios = json_decode(
        $response_usuario,
        true
    );
}

// Si la consulta no devuelve ningún usuario, muestra un mensaje de error y cancela la ejecución.
if (empty($usuarios)) {
    echo "No se pudo obtener la información del usuario.";
    exit;
}

// Asigna el primer registro del resultado a la variable del usuario.
$usuario = $usuarios[0];

/*
|--------------------------------------------------------------------------
| OBTENER OBJETOS REPORTADOS POR EL USUARIO
|--------------------------------------------------------------------------
*/

// Construye la URL para traer los objetos reportados por este usuario, ordenados descendentemente por ID.
$url_objetos =
    SUPABASE_URL .
    '/rest/v1/objetos?usuario_id=eq.' .
    urlencode($usuario_id) .
    '&select=id,nombre,descripcion_publica,color,estado,fecha,imagen' .
    '&order=id.desc';

// Ejecuta la petición HTTP a Supabase para consultar los objetos reportados.
$response_objetos = file_get_contents(
    $url_objetos,
    false,
    $context
);

// Inicializa el arreglo de objetos del usuario.
$objetos = [];

// Valida si se obtuvo una respuesta válida de la API.
if ($response_objetos !== false) {

    // Decodifica la respuesta JSON devuelta por la API REST.
    $datos_objetos =
        json_decode(
            $response_objetos,
            true
        );

    // Asegura que el resultado decodificado sea un arreglo válido antes de asignarlo.
    if (is_array($datos_objetos)) {
        $objetos = $datos_objetos;
    }
}

/*
|--------------------------------------------------------------------------
| OBTENER RECLAMACIONES DEL USUARIO
|--------------------------------------------------------------------------
*/

// Construye la URL para obtener las reclamaciones creadas por el usuario, ordenadas descendentemente por ID.
$url_reclamaciones =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?usuario_id=eq.' .
    urlencode($usuario_id) .
    '&select=id,objeto_id,objeto_perdido_id,estado,ticket,fecha,descripcion' .
    '&order=id.desc';

// Realiza la petición GET para consultar el listado de reclamaciones.
$response_reclamaciones = file_get_contents(
    $url_reclamaciones,
    false,
    $context
);

// Inicializa la variable que almacenará las reclamaciones.
$reclamaciones = [];

// Comprueba que la consulta no haya fallado a nivel de red/transmisión.
if ($response_reclamaciones !== false) {

    // Convierte el JSON recibido en un arreglo de PHP.
    $datos_reclamaciones =
        json_decode(
            $response_reclamaciones,
            true
        );

    // Si la estructura decodificada es un arreglo, asigna la lista de reclamaciones.
    if (is_array($datos_reclamaciones)) {
        $reclamaciones = $datos_reclamaciones;
    }
}

/*
|--------------------------------------------------------------------------
| FOTO
|--------------------------------------------------------------------------
*/

// Extrae la ruta o URL de la foto del usuario; si no tiene una asignada, establece una cadena vacía.
$foto = $usuario['foto'] ?? '';

// Si el usuario no cuenta con una fotografía, asigna la ruta de la imagen avatar por defecto.
if (empty($foto)) {
    $foto = 'img/avatar-default.png';
}

?>

<!-- Especifica la versión de HTML como HTML5 -->
<!DOCTYPE html>
<!-- Define el idioma principal del documento como español -->
<html lang="es">

<!-- Sección de cabecera con metadatos, fuentes e inclusión de hoja de estilos -->
<head>

    <!-- Configura la codificación de caracteres a UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configura la vista previa responsiva para adaptarse a dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define el título de la página concatenando dinámicamente el nombre del usuario escapado para evitar XSS -->
    <title>
        Perfil - <?php echo htmlspecialchars($usuario['nombre']); ?>
    </title>

    <!-- Vincula el archivo de estilos CSS correspondiente al perfil -->
    <link
        rel="stylesheet"
        href="css/perfil.css"
    >

</head>

<!-- Inicio del cuerpo visual de la página -->
<body>

<!-- Cabecera del perfil con la navegación de retorno -->
<header class="perfil-header">

    <!-- Enlace para volver a la página principal -->
    <a
        href="index.php"
        class="volver"
    >
        ← Volver al inicio
    </a>

</header>


<!-- Contenedor principal del perfil del usuario -->
<main class="perfil-contenedor">

    <!-- INFORMACIÓN PRINCIPAL DEL USUARIO -->

    <section class="perfil-principal">

        <!-- Contenedor de la foto de perfil del usuario -->
        <div class="perfil-avatar">

            <!-- Imprime de forma segura la ruta de la fotografía del usuario -->
            <img
                src="<?php echo htmlspecialchars($foto); ?>"
                alt="Foto de perfil"
            >

        </div>


        <!-- Contenedor con los datos personales del usuario -->
        <div class="perfil-datos">

            <!-- Muestra el nombre completo del usuario -->
            <h1>
                <?php echo htmlspecialchars($usuario['nombre']); ?>
            </h1>

            <!-- Muestra la dirección de correo electrónico registrada -->
            <p>
                <?php echo htmlspecialchars($usuario['correo']); ?>
            </p>

            <!-- Muestra la etiqueta o rol del usuario en la plataforma -->
            <span class="rol">

                <?php echo htmlspecialchars($usuario['rol']); ?>

            </span>

        </div>

    </section>


    <!-- SECCIÓN DE ACTIVIDAD Y ESTADÍSTICAS -->

    <section class="actividad">

        <!-- Título principal del bloque de actividad -->
        <h2>
            Actividad
        </h2>


        <!-- Galería de contadores de estadísticas -->
        <div class="estadisticas">

            <!-- Caja con la cantidad de objetos reportados -->
            <div class="estadistica">

                <!-- Cuenta e imprime el total de elementos del arreglo de objetos -->
                <strong>
                    <?php echo count($objetos); ?>
                </strong>

                <!-- Etiqueta descriptiva del contador -->
                <span>
                    Objetos reportados
                </span>

            </div>


            <!-- Caja con la cantidad de reclamaciones realizadas -->
            <div class="estadistica">

                <!-- Cuenta e imprime el total de elementos del arreglo de reclamaciones -->
                <strong>
                    <?php echo count($reclamaciones); ?>
                </strong>

                <!-- Etiqueta descriptiva del contador -->
                <span>
                    Reclamaciones
                </span>

            </div>

        </div>


        <!-- Subtítulo de la sección de objetos reportados -->
        <h2>
            Objetos reportados
        </h2>


        <!-- Evalúa si el arreglo de objetos está vacío para mostrar un mensaje o la lista -->
        <?php if (empty($objetos)): ?>

            <!-- Mensaje mostrado si no se han reportado objetos -->
            <p class="sin-datos">
                Todavía no has reportado objetos.
            </p>

        <?php else: ?>

            <!-- Contenedor de la lista de tarjetas de objetos reportados -->
            <div class="lista-objetos">

                <!-- Bucle que recorre cada objeto reportado por el usuario -->
                <?php foreach ($objetos as $objeto): ?>

                    <!-- Tarjeta individual para presentar la información del objeto -->
                    <article class="tarjeta">

                        <!-- Si el objeto cuenta con una imagen asociada, la despliega -->
                        <?php if (!empty($objeto['imagen'])): ?>

                            <!-- Muestra la imagen del objeto sanitizando su ruta -->
                            <img
                                src="<?php echo htmlspecialchars($objeto['imagen']); ?>"
                                alt="Fotografía del objeto"
                            >

                        <?php endif; ?>


                        <!-- Contenedor con los textos descriptivos del objeto -->
                        <div>

                            <!-- Título con el nombre del objeto -->
                            <h3>
                                <?php
                                echo htmlspecialchars(
                                    $objeto['nombre']
                                );
                                ?>
                            </h3>


                            <!-- Párrafo con la descripción pública del objeto -->
                            <p>
                                <?php
                                echo htmlspecialchars(
                                    $objeto['descripcion_publica'] ?? ''
                                );
                                ?>
                            </p>


                            <!-- Muestra el estado actual en el sistema del objeto -->
                            <p>

                                <strong>Estado:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['estado'] ?? ''
                                );
                                ?>

                            </p>


                            <!-- Muestra la fecha en que fue registrado el objeto -->
                            <p>

                                <strong>Fecha:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['fecha'] ?? ''
                                );
                                ?>

                            </p>


                            <!-- Enlace para ir a la vista de detalle completa del objeto -->
                            <a
                                href="objeto.php?id=<?php echo $objeto['id']; ?>"
                            >
                                Ver objeto
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- Subtítulo de la sección de reclamaciones -->
        <h2>
            Mis reclamaciones
        </h2>


        <!-- Verifica si el arreglo de reclamaciones no contiene elementos -->
        <?php if (empty($reclamaciones)): ?>

            <!-- Mensaje indicativo cuando no hay reclamaciones hechas -->
            <p class="sin-datos">
                Todavía no has realizado reclamaciones.
            </p>

        <?php else: ?>

            <!-- Lista de tarjetas de reclamaciones realizadas -->
            <div class="lista-reclamaciones">

                <!-- Bucle para iterar las reclamaciones del usuario -->
                <?php foreach ($reclamaciones as $reclamacion): ?>

                    <!-- Tarjeta contenedora de la reclamación individual -->
                    <article class="reclamacion">

                        <!-- Muestra el número de ticket de la reclamación -->
                        <h3>

                            Ticket:
                            <?php
                            echo htmlspecialchars(
                                $reclamacion['ticket'] ?? ''
                            );
                            ?>

                        </h3>


                        <!-- Muestra el estado en que se encuentra la solicitud de reclamación -->
                        <p>

                            <strong>Estado:</strong>

                            <?php
                            echo htmlspecialchars(
                                $reclamacion['estado'] ?? ''
                            );
                            ?>

                        </p>


                        <!-- Muestra la fecha de generación de la reclamación -->
                        <p>

                            <strong>Fecha:</strong>

                            <?php
                            echo htmlspecialchars(
                                $reclamacion['fecha'] ?? ''
                            );
                            ?>

                        </p>


                        <!-- Enlace para consultar el comprobante/ticket generado enviándolo mediante la URL -->
                        <a
                            href="ticket.php?ticket=<?php echo urlencode($reclamacion['ticket']); ?>"
                        >
                            Ver ticket
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<!-- Cierre del cuerpo -->
</body>

<!-- Cierre del HTML -->
</html>