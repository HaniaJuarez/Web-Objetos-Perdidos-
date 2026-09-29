<?php

// Incluye el archivo que verifica que la sesión del usuario esté activa antes de continuar.
require_once '../backend/verificar_sesion.php';
// Incluye el archivo de configuración general con las constantes del sistema (como SUPABASE_URL y SUPABASE_KEY).
require_once '../backend/config.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR ROL
|--------------------------------------------------------------------------
*/

// Valida si no está definida la variable de sesión 'rol' o si su valor es distinto de 'docente'.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'docente'
) {

    // Imprime el mensaje HTML de acceso denegado.
    echo "<h1>Acceso denegado</h1>";
    // Muestra la explicación del motivo del bloqueo.
    echo "<p>Esta sección solamente está disponible para docentes.</p>";
    // Proporciona un enlace para regresar a la página de inicio.
    echo '<a href="index.php">Volver al inicio</a>';

    // Detiene la ejecución del script para evitar acceso no autorizado.
    exit;
}


// Almacena en la variable local el ID del usuario proveniente de la sesión.
$usuario_id = $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE SUPABASE
|--------------------------------------------------------------------------
*/

// Define las opciones predeterminadas de contexto HTTP para las peticiones a la API de Supabase.
$options = [
    'http' => [
        // Establece el método HTTP como GET para obtener datos.
        'method' => 'GET',
        // Define los encabezados de autenticación apikey, Bearer token y el tipo de contenido JSON.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        // Permite capturar la respuesta del servidor incluso ante códigos de estado de error (4xx/5xx).
        'ignore_errors' => true
    ]
];

// Crea el recurso de contexto HTTP utilizando el arreglo de opciones previamente configurado.
$context =
    stream_context_create($options);


/*
|--------------------------------------------------------------------------
| RECLAMACIONES PENDIENTES
|--------------------------------------------------------------------------
|
| Se cuentan las reclamaciones que todavía están pendientes.
|
*/

// Construye la URL de Supabase para consultar los IDs de las reclamaciones con estado 'Pendiente'.
$url_pendientes =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?estado=eq.Pendiente&select=id';


// Ejecuta la petición GET a la API REST de Supabase para obtener las reclamaciones pendientes.
$response_pendientes =
    file_get_contents(
        $url_pendientes,
        false,
        $context
    );


// Inicializa el arreglo para guardar el listado de reclamaciones pendientes.
$pendientes = [];

// Comprueba si la llamada HTTP devolvió una respuesta válida diferente de false.
if ($response_pendientes !== false) {

    // Decodifica la cadena JSON devuelta por el servidor a un arreglo asociativo de PHP.
    $datos =
        json_decode(
            $response_pendientes,
            true
        );

    // Valida que el resultado decodificado sea efectivamente un arreglo antes de asignarlo.
    if (is_array($datos)) {
        $pendientes = $datos;
    }
}


/*
|--------------------------------------------------------------------------
| OBJETOS EN RESGUARDO
|--------------------------------------------------------------------------
*/

// Construye la URL de Supabase para consultar los IDs de los objetos con estado 'En resguardo'.
$url_resguardo =
    SUPABASE_URL .
    '/rest/v1/objetos?estado=eq.En%20resguardo&select=id';


// Realiza la consulta HTTP a la API de Supabase para traer los objetos en resguardo.
$response_resguardo =
    file_get_contents(
        $url_resguardo,
        false,
        $context
    );


// Inicializa el arreglo para guardar los objetos en resguardo.
$resguardo = [];

// Verifica que la llamada a file_get_contents no haya fallado.
if ($response_resguardo !== false) {

    // Convierte el cuerpo de la respuesta JSON a un arreglo de PHP.
    $datos =
        json_decode(
            $response_resguardo,
            true
        );

    // Valida que los datos procesados correspondan a un arreglo válido.
    if (is_array($datos)) {
        $resguardo = $datos;
    }
}


/*
|--------------------------------------------------------------------------
| RECLAMACIONES APROBADAS
|--------------------------------------------------------------------------
*/

// Construye la URL de Supabase para consultar los IDs de las reclamaciones con estado 'Aprobada'.
$url_aprobadas =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?estado=eq.Aprobada&select=id';


// Realiza la petición HTTP GET para obtener las reclamaciones que han sido aprobadas.
$response_aprobadas =
    file_get_contents(
        $url_aprobadas,
        false,
        $context
    );


// Inicializa el arreglo de reclamaciones aprobadas.
$aprobadas = [];

// Evalúa que la respuesta devuelta por la API sea distinta de false.
if ($response_aprobadas !== false) {

    // Decodifica la respuesta JSON recibida a un arreglo PHP.
    $datos =
        json_decode(
            $response_aprobadas,
            true
        );

    // Verifica que el contenido sea un arreglo y actualiza la variable.
    if (is_array($datos)) {
        $aprobadas = $datos;
    }
}

?>

<!-- Declaración del tipo de documento HTML5 -->
<!DOCTYPE html>

<!-- Elemento raíz HTML con la especificación de idioma en español -->
<html lang="es">

<!-- Sección de cabecera con metadatos, título y hojas de estilo -->
<head>

    <!-- Codificación de caracteres UTF-8 para garantizar el soporte de tildes y caracteres especiales -->
    <meta charset="UTF-8">

    <!-- Configuración para un diseño adaptativo a pantallas móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título de la pestaña dentro del navegador -->
    <title>
        Panel docente
    </title>


    <!-- Enlace a la hoja de estilos CSS específica del panel docente -->
    <link
        rel="stylesheet"
        href="css/docente.css"
    >

</head>


<!-- Inicio del cuerpo del documento HTML -->
<body>


<!-- Encabezado principal superior de la interfaz -->
<header class="encabezado">

    <!-- Contenedor del título y del saludo personalizado -->
    <div>

        <!-- Título principal de la vista -->
        <h1>
            Panel docente
        </h1>

        <!-- Mensaje de bienvenida imprimiendo el nombre del usuario de la sesión de forma segura -->
        <p>
            Bienvenido,
            <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION['nombre']
                );
                ?>
            </strong>
        </p>

    </div>


    <!-- Contenedor de los enlaces de navegación del encabezado -->
    <div class="acciones-header">

        <!-- Enlace de regreso al portal de inicio -->
        <a href="index.php">
            Inicio
        </a>

        <!-- Enlace para finalizar y destruir la sesión actual del docente -->
        <a href="../backend/logout.php">
            Cerrar sesión
        </a>

    </div>

</header>


<!-- Contenedor principal para las tarjetas de información y métricas del panel -->
<main class="contenedor">


    <!-- RECLAMACIONES -->

    <!-- Tarjeta informativa orientada a la gestión de reclamaciones -->
    <section class="tarjeta">

        <!-- Icono representativo de lista/hoja -->
        <div class="icono">
            📋
        </div>


        <!-- Contenido principal dentro de la tarjeta de reclamaciones -->
        <div class="contenido">

            <!-- Subtítulo de la sección -->
            <h2>
                Reclamaciones
            </h2>

            <!-- Texto descriptivo explicativo -->
            <p>
                Consulta y gestiona las solicitudes
                de reclamación de objetos.
            </p>

            <!-- Contenedor numérico que despliega el conteo total de reclamaciones pendientes -->
            <div class="numero">

                <?php
                echo count($pendientes);
                ?>

            </div>

            <!-- Etiqueta del estado correspondiente al número -->
            <span>
                pendientes
            </span>


            <!-- Botón de acceso directo a la vista detallada de gestión de reclamaciones -->
            <a
                href="reclamaciones_docente.php"
                class="boton"
            >
                Ver reclamaciones
            </a>

        </div>

    </section>



    <!-- OBJETOS EN RESGUARDO -->

    <!-- Tarjeta informativa orientada a los objetos almacenados en resguardo -->
    <section class="tarjeta">

        <!-- Icono representativo de caja/paquete -->
        <div class="icono">
            📦
        </div>


        <!-- Contenido de la tarjeta de objetos en resguardo -->
        <div class="contenido">

            <!-- Subtítulo de la tarjeta -->
            <h2>
                Objetos en resguardo
            </h2>

            <!-- Texto explicativo de la tarjeta -->
            <p>
                Consulta los objetos que actualmente
                se encuentran bajo resguardo.
            </p>


            <!-- Contenedor numérico que muestra la cantidad de elementos en resguardo -->
            <div class="numero">

                <?php
                echo count($resguardo);
                ?>

            </div>

            <!-- Etiqueta del contador -->
            <span>
                objetos
            </span>


            <!-- Enlace en forma de botón para ir a la vista de inventario en resguardo -->
            <a
                href="resguardo_docente.php"
                class="boton"
            >
                Ver objetos
            </a>

        </div>

    </section>



    <!-- RECLAMACIONES APROBADAS -->

    <!-- Tarjeta informativa de métricas de solicitudes ya aprobadas -->
    <section class="tarjeta">

        <!-- Icono representativo de marca de verificación -->
        <div class="icono">
            ✓
        </div>


        <!-- Contenido informativo del contador de aprobadas -->
        <div class="contenido">

            <!-- Subtítulo de la tarjeta -->
            <h2>
                Reclamaciones aprobadas
            </h2>

            <!-- Texto explicativo de la sección -->
            <p>
                Consulta las reclamaciones que
                ya fueron aprobadas.
            </p>


            <!-- Contenedor numérico que imprime el total de solicitudes aprobadas -->
            <div class="numero">

                <?php
                echo count($aprobadas);
                ?>

            </div>

            <!-- Etiqueta explicativa del número -->
            <span>
                aprobadas
            </span>

        </div>

    </section>


<!-- Cierre del contenedor principal -->
</main>


<!-- Pie de página de la interfaz con derechos e institución -->
<footer>

    <!-- Texto informativo institucional -->
    <p>
        Universidad Veracruzana - Facultad
    </p>

</footer>


<!-- Cierre del cuerpo del documento -->
</body>

<!-- Cierre del elemento raíz HTML -->
</html>