<?php

// ============================================================
// VERIFICAR SESIÓN
// ============================================================

// Verifica que exista una sesión activa.
require_once '../backend/verificar_sesion.php';

// Carga la configuración de Supabase.
require_once '../backend/config.php';


// ============================================================
// VERIFICAR ROL DEL USUARIO
// ============================================================

// Comprueba que el usuario tenga el rol de docente.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'docente'
) {

    echo "<h1>Acceso denegado</h1>";

    echo "<p>";
    echo "Esta sección solamente está disponible para docentes.";
    echo "</p>";

    echo '<a href="index.php">Volver al inicio</a>';

    exit;
}


// ============================================================
// CONFIGURACIÓN DE SUPABASE
// ============================================================

// Configura la petición que se realizará a Supabase.
$options = [

    'http' => [

        // Método utilizado para consultar información.
        'method' => 'GET',

        // Encabezados necesarios para Supabase.
        'header' =>

            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n",

        // Permite recibir la respuesta aunque exista un error HTTP.
        'ignore_errors' => true
    ]
];


// Crea el contexto para las peticiones.
$context =
    stream_context_create($options);


// ============================================================
// FUNCIÓN PARA CONSULTAR SUPABASE
// ============================================================

// Esta función realiza una consulta a Supabase
// y devuelve los datos como un arreglo PHP.
function consultarSupabase($url, $context)
{

    // Realiza la petición.
    $response =
        file_get_contents(
            $url,
            false,
            $context
        );


    // Si no se pudo realizar la petición,
    // devuelve un arreglo vacío.
    if ($response === false) {

        return [];

    }


    // Convierte la respuesta JSON en un arreglo PHP.
    $datos =
        json_decode(
            $response,
            true
        );


    // Comprueba que el resultado sea un arreglo.
    if (!is_array($datos)) {

        return [];

    }


    // Devuelve los datos obtenidos.
    return $datos;
}


// ============================================================
// OBTENER RECLAMACIONES
// ============================================================

// Consulta las reclamaciones registradas.
$url_reclamaciones =

    SUPABASE_URL .

    '/rest/v1/reclamaciones' .

    '?select=id,objeto_id,objeto_perdido_id,usuario_id,descripcion,estado,ticket,fecha,docente_id' .

    '&order=id.desc';


// Ejecuta la consulta.
$reclamaciones =
    consultarSupabase(
        $url_reclamaciones,
        $context
    );


// ============================================================
// OBTENER INFORMACIÓN RELACIONADA
// ============================================================

// Recorre todas las reclamaciones.
foreach ($reclamaciones as &$reclamacion) {


    // ========================================================
    // INFORMACIÓN DEL ALUMNO
    // ========================================================

    // Obtiene el ID del alumno.
    $usuario_id =
        $reclamacion['usuario_id'] ?? '';


    // Inicializa el espacio para los datos.
    $reclamacion['usuario'] = null;


    // Comprueba que exista un ID.
    if (!empty($usuario_id)) {

        // Consulta la tabla usuarios.
        $url_usuario =

            SUPABASE_URL .

            '/rest/v1/usuarios' .

            '?id=eq.' .
            urlencode($usuario_id) .

            '&select=id,nombre,correo,rol,foto';


        // Ejecuta la consulta.
        $usuarios =
            consultarSupabase(
                $url_usuario,
                $context
            );


        // Guarda la información del alumno.
        if (!empty($usuarios)) {

            $reclamacion['usuario'] =
                $usuarios[0];

        }
    }


    // ========================================================
    // INFORMACIÓN DEL OBJETO ENCONTRADO
    // ========================================================

    // Obtiene el ID del objeto encontrado.
    $objeto_id =
        $reclamacion['objeto_id'] ?? '';


    // Inicializa el espacio para los datos.
    $reclamacion['objeto'] = null;


    // Comprueba que exista el ID.
    if (!empty($objeto_id)) {

        // Consulta la tabla objetos.
        $url_objeto =

            SUPABASE_URL .

            '/rest/v1/objetos' .

            '?id=eq.' .
            urlencode($objeto_id) .

            '&select=*';


        // Ejecuta la consulta.
        $objetos =
            consultarSupabase(
                $url_objeto,
                $context
            );


        // Guarda la información del objeto.
        if (!empty($objetos)) {

            $reclamacion['objeto'] =
                $objetos[0];

        }
    }


    // ========================================================
    // INFORMACIÓN DEL OBJETO PERDIDO
    // ========================================================

    // Obtiene el ID del objeto perdido relacionado.
    $objeto_perdido_id =
        $reclamacion['objeto_perdido_id'] ?? '';


    // Inicializa el espacio para los datos.
    $reclamacion['objeto_perdido'] = null;


    // Comprueba que exista el ID.
    if (!empty($objeto_perdido_id)) {

        // Consulta la tabla objetos.
        $url_objeto_perdido =

            SUPABASE_URL .

            '/rest/v1/objetos' .

            '?id=eq.' .
            urlencode($objeto_perdido_id) .

            '&select=*';


        // Ejecuta la consulta.
        $objetos_perdidos =
            consultarSupabase(
                $url_objeto_perdido,
                $context
            );


        // Guarda la información del objeto perdido.
        if (!empty($objetos_perdidos)) {

            $reclamacion['objeto_perdido'] =
                $objetos_perdidos[0];

        }
    }

}


// Elimina la referencia utilizada por foreach.
unset($reclamacion);

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reclamaciones - Panel docente
    </title>


    <!-- Hoja de estilos específica de reclamaciones -->
    <link
        rel="stylesheet"
        href="css/reclamaciones_docente.css"
    >


    <!-- JavaScript específico de reclamaciones -->
    <script
        src="js/reclamaciones_docente.js"
        defer
    ></script>

</head>


<body>


    <!-- =====================================================
         ENCABEZADO
         ===================================================== -->

    <header class="encabezado">

        <h1>
            Reclamaciones
        </h1>


        <p>

            Docente:

            <strong>

                <?php

                echo htmlspecialchars(
                    $_SESSION['nombre']
                );

                ?>

            </strong>

        </p>


        <a href="docente.php">

            ← Volver al panel docente

        </a>

    </header>


    <!-- =====================================================
         CONTENIDO PRINCIPAL
         ===================================================== -->

    <main class="contenedor">

        <h2>
            Solicitudes de reclamación
        </h2>


        <?php if (empty($reclamaciones)): ?>

            <p class="sin-datos">

                No existen reclamaciones registradas.

            </p>


        <?php else: ?>


            <!-- Recorre las reclamaciones. -->

            <?php foreach ($reclamaciones as $reclamacion): ?>


                <article class="reclamacion">


                    <!-- =================================================
                         INFORMACIÓN DE LA RECLAMACIÓN
                         ================================================= -->

                    <h2>

                        Ticket:

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['ticket'] ?? ''
                        );

                        ?>

                    </h2>


                    <div class="datos">


                        <div class="dato">

                            <strong>
                                Estado
                            </strong>

                            <span class="estado">

                                <?php

                                echo htmlspecialchars(
                                    $reclamacion['estado'] ?? ''
                                );

                                ?>

                            </span>

                        </div>


                        <div class="dato">

                            <strong>
                                Fecha
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $reclamacion['fecha'] ?? ''
                            );

                            ?>

                        </div>


                        <div class="dato">

                            <strong>
                                ID de reclamación
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $reclamacion['id'] ?? ''
                            );

                            ?>

                        </div>

                    </div>


                    <!-- =================================================
                         INFORMACIÓN DEL ALUMNO
                         ================================================= -->

                    <section class="seccion">

                        <h3>
                            Información del alumno
                        </h3>


                        <?php if (
                            !empty(
                                $reclamacion['usuario']
                            )
                        ): ?>


                            <div class="datos">


                                <div class="dato">

                                    <strong>
                                        Nombre
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'usuario'
                                        ]['nombre']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Correo
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'usuario'
                                        ]['correo']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        ID de usuario
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'usuario'
                                        ]['id']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                            </div>


                        <?php else: ?>


                            <p class="sin-datos">

                                No se pudo obtener la información
                                del alumno.

                            </p>


                        <?php endif; ?>

                    </section>


                    <!-- =================================================
                         OBJETO ENCONTRADO
                         ================================================= -->

                    <section class="seccion">

                        <h3>
                            Objeto encontrado
                        </h3>


                        <?php if (
                            !empty(
                                $reclamacion['objeto']
                            )
                        ): ?>


                            <div class="datos">


                                <div class="dato">

                                    <strong>
                                        Nombre
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['nombre']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Color
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['color']
                                        ?? 'No especificado'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Estado
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['estado']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Ubicación
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['ubicacion']
                                        ?? 'No especificada'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Responsable
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['responsable']
                                        ?? 'No especificado'
                                    );

                                    ?>

                                </div>


                            </div>


                            <?php if (
                                !empty(
                                    $reclamacion[
                                        'objeto'
                                    ]['imagen']
                                )
                            ): ?>


                                <img
                                    class="foto"
                                    src="<?php echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto'
                                        ]['imagen']
                                    ); ?>"
                                    alt="Fotografía del objeto encontrado"
                                >


                            <?php endif; ?>


                        <?php else: ?>


                            <p class="sin-datos">

                                No se pudo obtener la información
                                del objeto encontrado.

                            </p>


                        <?php endif; ?>

                    </section>


                    <!-- =================================================
                         OBJETO PERDIDO
                         ================================================= -->

                    <section class="seccion">

                        <h3>
                            Objeto perdido relacionado
                        </h3>


                        <?php if (
                            !empty(
                                $reclamacion[
                                    'objeto_perdido'
                                ]
                            )
                        ): ?>


                            <div class="datos">


                                <div class="dato">

                                    <strong>
                                        Nombre
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['nombre']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Color
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['color']
                                        ?? 'No especificado'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Estado
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['estado']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Ubicación
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['ubicacion']
                                        ?? 'No especificada'
                                    );

                                    ?>

                                </div>


                                <div class="dato">

                                    <strong>
                                        Fecha
                                    </strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['fecha']
                                        ?? 'No disponible'
                                    );

                                    ?>

                                </div>


                            </div>


                            <?php if (
                                !empty(
                                    $reclamacion[
                                        'objeto_perdido'
                                    ]['imagen']
                                )
                            ): ?>


                                <img
                                    class="foto"
                                    src="<?php echo htmlspecialchars(
                                        $reclamacion[
                                            'objeto_perdido'
                                        ]['imagen']
                                    ); ?>"
                                    alt="Fotografía del objeto perdido"
                                >


                            <?php endif; ?>


                        <?php else: ?>


                            <p class="sin-datos">

                                No se pudo obtener la información
                                del objeto perdido relacionado.

                            </p>


                        <?php endif; ?>

                    </section>


                    <!-- =================================================
                         INFORMACIÓN PRIVADA
                         ================================================= -->

                    <section class="seccion">

                        <h3>
                            Información para verificar la propiedad
                        </h3>


                        <div class="descripcion-privada">

                            <?php

                            echo nl2br(

                                htmlspecialchars(

                                    $reclamacion[
                                        'descripcion'
                                    ] ?? ''

                                )

                            );

                            ?>

                        </div>

                    </section>

                    <!-- =================================================
                        ACCIONES DEL DOCENTE
                        ================================================= -->

                    <?php if (
                        ($reclamacion['estado'] ?? '') === 'Pendiente'
                    ): ?>

                        <section class="seccion">

                            <h3>
                                Acción del docente
                            </h3>

                            <div class="acciones-reclamacion">

                                <!-- FORMULARIO PARA APROBAR -->

                                <form
                                    action="../backend/gestionar_reclamacion.php"
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="reclamacion_id"
                                        value="<?php echo htmlspecialchars(
                                            $reclamacion['id']
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="aprobar"
                                    >

                                    <button
                                        type="submit"
                                        class="boton-aprobar"
                                    >
                                        Aprobar reclamación
                                    </button>

                                </form>


                                <!-- FORMULARIO PARA RECHAZAR -->

                                <form
                                    action="../backend/gestionar_reclamacion.php"
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="reclamacion_id"
                                        value="<?php echo htmlspecialchars(
                                            $reclamacion['id']
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="rechazar"
                                    >

                                    <button
                                        type="submit"
                                        class="boton-rechazar"
                                    >
                                        Rechazar reclamación
                                    </button>

                                </form>

                            </div>

                        </section>

                    <?php endif; ?>


                    <!-- =================================================
                         DOCENTE ASIGNADO
                         ================================================= -->

                    <?php if (
                        !empty(
                            $reclamacion['docente_id']
                        )
                    ): ?>


                        <section class="seccion">

                            <h3>
                                Docente asignado
                            </h3>


                            <p>

                                ID del docente:

                                <?php

                                echo htmlspecialchars(
                                    $reclamacion[
                                        'docente_id'
                                    ]
                                );

                                ?>

                            </p>

                        </section>


                    <?php endif; ?>


                </article>


            <?php endforeach; ?>


        <?php endif; ?>

    </main>


    <!-- =====================================================
         PIE DE PÁGINA
         ===================================================== -->

    <footer>

        <p>
            Universidad Veracruzana - Facultad
        </p>

    </footer>


</body>

</html>