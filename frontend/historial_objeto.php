<?php

// ============================================================
// VERIFICACIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
// ============================================================

// Incluye el archivo para validar que exista una sesión activa; redirecciona si no hay sesión.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes globales (por ejemplo: SUPABASE_URL y SUPABASE_KEY).
require_once '../backend/config.php';


/*
|--------------------------------------------------------------------------
| Obtener ID del objeto
|--------------------------------------------------------------------------
*/

// Recupera el identificador del objeto enviado vía URL mediante el método GET.
// Utiliza el operador de fusión de nulos (??) para asignar una cadena vacía si no existe.
$id = $_GET['id'] ?? '';


// Valida que el identificador del objeto no esté vacío.
if (empty($id)) {

    // Detiene la ejecución y notifica que se requiere un ID válido.
    echo "Objeto no especificado.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Consultar información del objeto
|--------------------------------------------------------------------------
*/

// Construye la URL para solicitar a la API REST de Supabase los datos específicos del objeto.
$url_objeto =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?id=eq.' .
    urlencode($id) .
    '&select=id,nombre,imagen,estado';


// Define los encabezados de autenticación HTTP y la configuración de la petición para Supabase.
$options_objeto = [

    'http' => [

        // Método HTTP para consultar información (GET).
        'method' => 'GET',

        // Encabezados requeridos con las credenciales de API Key y Bearer Token.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        // Permite capturar la respuesta del servidor incluso si ocurren códigos de error HTTP (4xx/5xx).
        'ignore_errors' => true

    ]

];


// Crea el contexto de flujo HTTP con los encabezados estructurados anteriormente.
$context_objeto =
    stream_context_create($options_objeto);


// Realiza la petición HTTP a la API de Supabase para obtener la información del objeto.
$response_objeto =
    file_get_contents(
        $url_objeto,
        false,
        $context_objeto
    );


// Comprueba si ocurrió un error al realizar la llamada HTTP.
if ($response_objeto === false) {

    echo "No se pudo consultar el objeto.";
    exit;

}


// Decodifica la respuesta JSON recibida desde Supabase a un arreglo asociativo de PHP.
$objetos =
    json_decode(
        $response_objeto,
        true
    );


// Comprueba que el resultado decodificado sea un arreglo válido y que contenga al menos un objeto.
if (
    !is_array($objetos) ||
    empty($objetos)
) {

    echo "Objeto no encontrado.";
    exit;

}


// Asigna el primer elemento del arreglo como la información del objeto a mostrar.
$objeto = $objetos[0];


/*
|--------------------------------------------------------------------------
| Consultar historial
|--------------------------------------------------------------------------
*/

// Construye la URL para consultar el historial de movimientos asociado al ID del objeto.
// Ordena los registros de manera cronológica ascendente por el campo 'fecha'.
$url_historial =
    SUPABASE_URL .
    '/rest/v1/historial_objetos' .
    '?objeto_id=eq.' .
    urlencode($id) .
    '&select=*' .
    '&order=fecha.asc';


// Configura las opciones del contexto HTTP para la consulta de la tabla 'historial_objetos'.
$options_historial = [

    'http' => [

        // Método HTTP a utilizar (GET).
        'method' => 'GET',

        // Encabezados HTTP requeridos por Supabase.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",

        // Ignora errores para no interrumpir la ejecución en fallos del servidor.
        'ignore_errors' => true

    ]

];


// Crea el contexto de recursos HTTP para la consulta del historial.
$context_historial =
    stream_context_create(
        $options_historial
    );


// Realiza la petición HTTP a Supabase para traer los eventos de la tabla de historial.
$response_historial =
    file_get_contents(
        $url_historial,
        false,
        $context_historial
    );


// Procesa la respuesta obtenida del historial.
if ($response_historial === false) {

    // Asigna un arreglo vacío si la consulta falló.
    $historial = [];

} else {

    // Convierte el cuerpo en formato JSON a un arreglo asociativo de PHP.
    $historial =
        json_decode(
            $response_historial,
            true
        );

    // Valida que los datos decodificados tengan una estructura de arreglo válida.
    if (!is_array($historial)) {

        $historial = [];

    }

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- Define la codificación de caracteres en UTF-8 -->
    <meta charset="UTF-8">

    <!-- Garantiza la responsividad en dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Historial del objeto
    </title>

    <!-- Estilos CSS específicos de la vista del historial -->
    <link
        rel="stylesheet"
        href="css/historial_objeto.css"
    >

    <!-- Script de interacciones cliente diferido -->
    <script
        src="js/historial_objeto.js"
        defer
    ></script>

</head>


<body>


<!-- ENCABEZADO PRINCIPAL DE LA VISTA -->
<header class="encabezado">

    <div class="encabezado-contenido">

        <h1>
            Historial del objeto
        </h1>

        <!-- Enlace para retornar a la vista detallada del objeto específico -->
        <a
            href="objeto.php?id=<?php echo urlencode($id); ?>"
            class="boton-regresar"
        >
            ← Volver al objeto
        </a>

    </div>

</header>


<!-- CONTENEDOR DE CONTENIDO PRINCIPAL -->
<main class="contenedor">


    <!-- Información general del objeto consultado -->

    <section class="objeto">

        <!-- Muestra la fotografía del objeto si la propiedad 'imagen' no está vacía -->
        <?php if (!empty($objeto['imagen'])): ?>

            <img
                src="<?php echo htmlspecialchars($objeto['imagen']); ?>"
                alt="Fotografía del objeto"
                class="imagen-objeto"
            >

        <?php endif; ?>


        <div class="informacion-objeto">

            <h2>

                <?php

                // Imprime el nombre sanitizado del objeto o una etiqueta por defecto
                echo htmlspecialchars(
                    $objeto['nombre'] ?? 'Sin nombre'
                );

                ?>

            </h2>


            <p>

                <strong>
                    Estado actual:
                </strong>

                <span class="estado">

                    <?php

                    // Imprime el estado actual del objeto
                    echo htmlspecialchars(
                        $objeto['estado'] ?? 'Sin estado'
                    );

                    ?>

                </span>

            </p>

        </div>

    </section>


    <!-- Sección de línea de tiempo e historial de cambios -->

    <section class="seccion-historial">

        <h2>
            Historial de movimientos
        </h2>


        <!-- Mensaje alternativo cuando no existen registros en el historial -->
        <?php if (empty($historial)): ?>

            <div class="mensaje-vacio">

                <h3>
                    No hay movimientos registrados
                </h3>

                <p>
                    Este objeto todavía no tiene cambios
                    registrados en el historial.
                </p>

            </div>

        <?php else: ?>


            <!-- Línea de tiempo visual con el recorrido del objeto -->
            <div class="timeline">


                <!-- Itera cada registro histórico obtenido -->
                <?php foreach ($historial as $registro): ?>

                    <article class="movimiento">


                        <!-- Marcador visual del punto en la línea de tiempo -->
                        <div class="punto">
                        </div>


                        <div class="contenido-movimiento">


                            <!-- Fecha y hora formateadas del evento registrado -->
                            <div class="fecha">

                                <?php

                                // Formatea y muestra la fecha en patrón día/mes/año hora:minuto
                                if (!empty($registro['fecha'])) {

                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $registro['fecha']
                                        )
                                    );

                                }

                                ?>

                            </div>


                            <!-- Transición del estado previo al estado nuevo del objeto -->
                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $registro['estado_anterior']
                                    ?? 'Sin estado'
                                );

                                ?>

                                →

                                <?php

                                echo htmlspecialchars(
                                    $registro['estado_nuevo']
                                    ?? 'Sin estado'
                                );

                                ?>

                            </h3>


                            <!-- Descripción detallada de la acción realizada -->
                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $registro['descripcion']
                                    ?? 'Sin descripción'
                                );

                                ?>

                            </p>


                            <!-- Identificador del usuario involucrado si está disponible -->
                            <?php if (!empty($registro['usuario_id'])): ?>

                                <small>

                                    Usuario responsable:
                                    <?php

                                    echo htmlspecialchars(
                                        $registro['usuario_id']
                                    );

                                    ?>

                                </small>

                            <?php endif; ?>


                        </div>

                    </article>

                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </section>


</main>


<!-- PIE DE PÁGINA -->
<footer>

    <p>
        Universidad Veracruzana - Facultad
    </p>

</footer>


</body>

</html>