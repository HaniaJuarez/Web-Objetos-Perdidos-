<?php

// ============================================================
// VERIFICACIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
// ============================================================

// Incluye el archivo para validar que la sesión del usuario esté activa.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes de configuración (URL y API Key de Supabase).
require_once '../backend/config.php';

/*
|--------------------------------------------------------------------------
| Verificar administrador
|--------------------------------------------------------------------------
*/

// Comprueba que el usuario tenga el rol de 'administrador'.
// Si la sesión no tiene el rol adecuado, interrumpe el acceso y detiene el script.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo "Acceso no autorizado.";
    exit;
}


/*
|--------------------------------------------------------------------------
| Consultar historial en Supabase
|--------------------------------------------------------------------------
*/

// Define la URL para obtener todos los registros del historial de objetos ordenados por fecha descendente.
$url_historial =
    SUPABASE_URL .
    '/rest/v1/historial_objetos' .
    '?select=*' .
    '&order=fecha.desc';

// Configura las opciones y cabeceras de la petición HTTP GET hacia Supabase.
$options = [
    'http' => [
        'method' => 'GET',
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        'ignore_errors' => true
    ]
];

// Crea el flujo de contexto para la solicitud HTTP.
$context = stream_context_create($options);

// Ejecuta la petición para obtener los datos del historial.
$response =
    file_get_contents(
        $url_historial,
        false,
        $context
    );

// Valida si la petición devolvió un error de conexión o lectura.
if ($response === false) {
    echo "No se pudo consultar el historial.";
    exit;
}

// Convierte la respuesta JSON enviada por Supabase en un arreglo asociativo de PHP.
$historial = json_decode($response, true);

// Garantiza que $historial sea un arreglo aunque la respuesta sea nula o inválida.
if (!is_array($historial)) {
    $historial = [];
}


/*
|--------------------------------------------------------------------------
| Función para consultar Supabase
|--------------------------------------------------------------------------
*/

/**
 * Realiza peticiones auxiliares HTTP GET a la API REST de Supabase.
 *
 * @param string $url Endpoint completo a consultar.
 * @return array Arreglo asociativo con los resultados o vacío en caso de fallo.
 */
function consultarDatos($url)
{
    $options = [
        'http' => [
            'method' => 'GET',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: application/json\r\n",
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);

    $response =
        file_get_contents(
            $url,
            false,
            $context
        );

    // Retorna un arreglo vacío si la petición falla.
    if ($response === false) {
        return [];
    }

    $datos = json_decode($response, true);

    // Retorna los datos decodificados siempre que sean un arreglo válido.
    return is_array($datos) ? $datos : [];
}


/*
|--------------------------------------------------------------------------
| Contadores y métricas de estado
|--------------------------------------------------------------------------
*/

// Almacena el número total de movimientos de la consulta.
$total_movimientos = count($historial);

// Inicializa contadores específicos por cada nuevo estado alcanzado.
$en_resguardo = 0;
$recuperados = 0;

// Recorre cada registro para contar según el valor de 'estado_nuevo'.
foreach ($historial as $movimiento) {

    $estado_nuevo =
        $movimiento['estado_nuevo'] ?? '';

    if ($estado_nuevo === 'En resguardo') {
        $en_resguardo++;
    }

    if ($estado_nuevo === 'Recuperado') {
        $recuperados++;
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- Codificación estándar de caracteres UTF-8 -->
    <meta charset="UTF-8">

    <!-- Escalado para un diseño adaptable a dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Historial - Administrador</title>

    <!-- Hoja de estilos específica para la vista del historial de administrador -->
    <link
        rel="stylesheet"
        href="css/admin_historial.css"
    >

    <!-- Archivo JS diferido para las funciones interactivas de filtrado -->
    <script
        src="js/admin_historial.js"
        defer
    ></script>

</head>


<body>


<!-- ENCABEZADO DE LA VISTA -->
<header class="encabezado">

    <div class="encabezado-contenido">

        <div>

            <h1>
                Historial de objetos
            </h1>

            <p>
                Seguimiento de movimientos del sistema
            </p>

        </div>


        <!-- Botón para regresar a la página principal del administrador -->
        <a
            href="administrador.php"
            class="boton-regresar"
        >
            ← Volver al panel
        </a>

    </div>

</header>


<main class="contenedor">


    <!-- =====================================================
         TARJETAS DE RESUMEN Y ESTADÍSTICAS
         ===================================================== -->

    <section class="resumen">


        <!-- Total global de registros en el historial -->
        <div class="tarjeta-resumen">

            <span class="numero">
                <?php echo $total_movimientos; ?>
            </span>

            <span class="texto">
                Movimientos registrados
            </span>

        </div>


        <!-- Cantidad de movimientos en estado 'En resguardo' -->
        <div class="tarjeta-resumen resguardo">

            <span class="numero">
                <?php echo $en_resguardo; ?>
            </span>

            <span class="texto">
                En resguardo
            </span>

        </div>


        <!-- Cantidad de movimientos en estado 'Recuperado' -->
        <div class="tarjeta-resumen recuperado">

            <span class="numero">
                <?php echo $recuperados; ?>
            </span>

            <span class="texto">
                Recuperados
            </span>

        </div>


    </section>


    <!-- =====================================================
         SECCIÓN DE FILTROS Y BÚSQUEDA
         ===================================================== -->

    <section class="filtros">


        <!-- Búsqueda dinámica por texto libre -->
        <div class="campo">

            <label for="buscar">
                Buscar
            </label>

            <input
                type="text"
                id="buscar"
                placeholder="Objeto, estado o descripción..."
            >

        </div>


        <!-- Filtro desplegable por estado nuevo del movimiento -->
        <div class="campo">

            <label for="filtro-estado">
                Estado nuevo
            </label>

            <select id="filtro-estado">

                <option value="">
                    Todos
                </option>

                <option value="En resguardo">
                    En resguardo
                </option>

                <option value="Recuperado">
                    Recuperado
                </option>

            </select>

        </div>


        <!-- Botón para restablecer todos los filtros aplicados -->
        <button
            type="button"
            id="limpiar-filtros"
        >
            Limpiar filtros
        </button>


    </section>


    <!-- =====================================================
         LISTADO DEL HISTORIAL DE MOVIMIENTOS
         ===================================================== -->

    <section class="seccion-historial">


        <div class="titulo-seccion">

            <h2>
                Movimientos registrados
            </h2>

            <!-- Indicador dinámico de total de movimientos -->
            <span id="contador-resultados">
                <?php echo $total_movimientos; ?>
                movimientos
            </span>

        </div>


        <!-- Muestra un mensaje si no hay movimientos en la base de datos -->
        <?php if (empty($historial)): ?>


            <div class="mensaje-vacio">

                <h3>
                    No hay movimientos registrados
                </h3>

                <p>
                    Todavía no existen movimientos en el historial.
                </p>

            </div>


        <?php else: ?>


            <div class="lista-historial">


                <!-- Iteración principal sobre los registros de historial -->
                <?php foreach ($historial as $movimiento): ?>


                    <?php

                    // Extracción de campos del registro actual
                    $objeto_id =
                        $movimiento['objeto_id'] ?? '';

                    $estado_anterior =
                        $movimiento['estado_anterior'] ?? '';

                    $estado_nuevo =
                        $movimiento['estado_nuevo'] ?? '';

                    $descripcion =
                        $movimiento['descripcion'] ?? '';

                    $usuario_id =
                        $movimiento['usuario_id'] ?? '';

                    $fecha =
                        $movimiento['fecha'] ?? '';


                    /*
                    |--------------------------------------------------
                    | Consultar datos del objeto relacionado
                    |--------------------------------------------------
                    */

                    $objeto = [];

                    if (!empty($objeto_id)) {

                        $url_objeto =
                            SUPABASE_URL .
                            '/rest/v1/objetos' .
                            '?id=eq.' .
                            urlencode($objeto_id) .
                            '&select=id,nombre,imagen,estado';

                        $objetos =
                            consultarDatos($url_objeto);

                        if (!empty($objetos)) {
                            $objeto = $objetos[0];
                        }
                    }


                    /*
                    |--------------------------------------------------
                    | Consultar datos del usuario que realizó la acción
                    |--------------------------------------------------
                    */

                    $usuario = [];

                    if (!empty($usuario_id)) {

                        $url_usuario =
                            SUPABASE_URL .
                            '/rest/v1/usuarios' .
                            '?id=eq.' .
                            urlencode($usuario_id) .
                            '&select=id,nombre,correo';

                        $usuarios =
                            consultarDatos($url_usuario);

                        if (!empty($usuarios)) {
                            $usuario = $usuarios[0];
                        }
                    }

                    ?>


                    <!-- Tarjeta contenedora de movimiento con atributos data-* para filtrado cliente -->
                    <article
                        class="tarjeta-movimiento"

                        data-estado="<?php
                            echo htmlspecialchars(
                                $estado_nuevo
                            );
                        ?>"

                        data-busqueda="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    ($objeto['nombre'] ?? '') .
                                    ' ' .
                                    $estado_anterior .
                                    ' ' .
                                    $estado_nuevo .
                                    ' ' .
                                    $descripcion
                                )
                            );
                        ?>"
                    >


                        <!-- CABECERA DE LA TARJETA DE MOVIMIENTO -->

                        <div class="cabecera-movimiento">


                            <div>

                                <span class="id-movimiento">
                                    Movimiento #<?php
                                    echo htmlspecialchars(
                                        $movimiento['id'] ?? ''
                                    );
                                    ?>
                                </span>


                                <h3>

                                    <?php

                                    echo htmlspecialchars(
                                        $objeto['nombre']
                                        ?? 'Objeto no disponible'
                                    );

                                    ?>

                                </h3>

                            </div>


                            <!-- Indicador de estado nuevo estilizado según su valor -->
                            <span
                                class="estado estado-<?php
                                    echo strtolower(
                                        str_replace(
                                            ' ',
                                            '-',
                                            $estado_nuevo
                                        )
                                    );
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $estado_nuevo
                                );
                                ?>

                            </span>


                        </div>


                        <!-- CONTENIDO DE DETALLE DEL MOVIMIENTO -->

                        <div class="contenido-movimiento">


                            <!-- Representación gráfica de la transición de estado -->
                            <div class="cambio-estado">

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $estado_anterior
                                    );
                                    ?>
                                </span>

                                <strong>
                                    →
                                </strong>

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $estado_nuevo
                                    );
                                    ?>
                                </span>

                            </div>


                            <!-- Datos asociativos del movimiento -->
                            <div class="datos-movimiento">


                                <div>

                                    <strong>
                                        Objeto:
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $objeto['nombre']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div>

                                    <strong>
                                        ID del objeto:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $objeto_id
                                    );
                                    ?>

                                </div>


                                <div>

                                    <strong>
                                        Fecha:
                                    </strong>

                                    <?php

                                    if (!empty($fecha)) {

                                        echo date(
                                            'd/m/Y H:i',
                                            strtotime($fecha)
                                        );

                                    } else {

                                        echo 'Sin fecha';

                                    }

                                    ?>

                                </div>


                                <div>

                                    <strong>
                                        Usuario responsable:
                                    </strong>

                                    <?php

                                    if (!empty($usuario)) {

                                        echo htmlspecialchars(
                                            $usuario['nombre']
                                            ?? 'Sin nombre'
                                        );

                                    } else {

                                        echo 'No disponible';

                                    }

                                    ?>

                                </div>


                            </div>


                            <!-- Descripción o notas asociadas al movimiento -->
                            <div class="descripcion">

                                <strong>
                                    Descripción:
                                </strong>

                                <p>

                                    <?php

                                    echo nl2br(
                                        htmlspecialchars(
                                            $descripcion
                                        )
                                    );

                                    ?>

                                </p>

                            </div>


                        </div>


                        <!-- BOTONES Y ENLACES DE ACCIÓN -->

                        <div class="acciones">

                            <a
                                href="objeto.php?id=<?php echo urlencode($objeto_id); ?>"
                                class="boton"
                            >
                                Ver objeto
                            </a>


                            <a
                                href="historial_objeto.php?id=<?php echo urlencode($objeto_id); ?>"
                                class="boton secundario"
                            >
                                Ver historial completo
                            </a>

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