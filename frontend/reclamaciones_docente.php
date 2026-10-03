<?php

// ============================================================
// VERIFICAR SESIÓN
// ============================================================

// Incluye el archivo encargado de comprobar que exista
// una sesión de usuario activa.
require_once '../backend/verificar_sesion.php';


// ============================================================
// CONFIGURACIÓN DE SUPABASE
// ============================================================

// Incluye las variables de configuración necesarias
// para comunicarse con Supabase.
require_once '../backend/config.php';


// ============================================================
// VERIFICAR ROL DEL USUARIO
// ============================================================

// Comprueba que el usuario tenga el rol de docente.
// Esta validación evita que un alumno pueda acceder
// directamente escribiendo la dirección de esta página.
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
// CONFIGURAR CONSULTA A SUPABASE
// ============================================================

// Obtiene el ID del docente que tiene la sesión iniciada.
$docente_id = $_SESSION['usuario_id'];


// Configura la petición HTTP que se utilizará
// para consultar la tabla reclamaciones.
$options = [

    'http' => [

        // Método utilizado para consultar información.
        'method' => 'GET',

        // Encabezados necesarios para autenticar
        // la petición ante Supabase.
        'header' =>

            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n",

        // Permite recibir la respuesta aunque Supabase
        // devuelva un código de error HTTP.
        'ignore_errors' => true
    ]
];


// Crea el contexto HTTP con las opciones anteriores.
$context =
    stream_context_create($options);


// ============================================================
// CONSULTAR RECLAMACIONES
// ============================================================

// Consulta todas las reclamaciones registradas.
//
// Por ahora no filtramos por docente_id.
// Primero comprobaremos que las reclamaciones existentes
// se muestran correctamente.
$url =

    SUPABASE_URL .

    '/rest/v1/reclamaciones' .

    '?select=id,objeto_id,objeto_perdido_id,usuario_id,descripcion,estado,ticket,fecha,docente_id' .

    '&order=id.desc';


// Ejecuta la consulta en Supabase.
$response =

    file_get_contents(

        $url,

        false,

        $context

    );


// Inicializa el arreglo donde se almacenarán
// las reclamaciones.
$reclamaciones = [];


// Comprueba si Supabase respondió correctamente.
if ($response !== false) {

    // Convierte la respuesta JSON en un arreglo PHP.
    $datos =
        json_decode(
            $response,
            true
        );

    // Comprueba que la respuesta sea un arreglo.
    if (is_array($datos)) {

        $reclamaciones = $datos;

    }
}

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

</head>

<body>


    <!-- =====================================================
         ENCABEZADO
         ===================================================== -->

    <header>

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


        <!-- Enlace para regresar al panel docente. -->

        <a href="docente.php">

            ← Volver al panel docente

        </a>

    </header>


    <hr>


    <!-- =====================================================
         CONTENIDO PRINCIPAL
         ===================================================== -->

    <main>

        <h2>
            Solicitudes de reclamación
        </h2>


        <?php if (empty($reclamaciones)): ?>

            <!-- Se muestra cuando no existen reclamaciones. -->

            <p>
                No existen reclamaciones registradas.
            </p>


        <?php else: ?>


            <!-- =================================================
                 MOSTRAR CADA RECLAMACIÓN
                 ================================================= -->

            <?php foreach ($reclamaciones as $reclamacion): ?>


                <article>


                    <!-- -------------------------------------------------
                         TICKET
                         ------------------------------------------------- -->

                    <h3>

                        Ticket:

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['ticket'] ?? ''
                        );

                        ?>

                    </h3>


                    <!-- -------------------------------------------------
                         ESTADO
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Estado:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['estado'] ?? ''
                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         FECHA
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Fecha:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['fecha'] ?? ''
                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         USUARIO
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Usuario:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['usuario_id'] ?? ''
                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         OBJETO ENCONTRADO
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Objeto encontrado:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['objeto_id'] ?? ''
                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         OBJETO PERDIDO
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Objeto perdido relacionado:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $reclamacion['objeto_perdido_id'] ?? ''
                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         DESCRIPCIÓN PRIVADA
                         ------------------------------------------------- -->

                    <p>

                        <strong>
                            Información proporcionada por el alumno:
                        </strong>

                    </p>


                    <p>

                        <?php

                        echo nl2br(

                            htmlspecialchars(

                                $reclamacion['descripcion'] ?? ''

                            )

                        );

                        ?>

                    </p>


                    <!-- -------------------------------------------------
                         DOCENTE ASIGNADO
                         ------------------------------------------------- -->

                    <?php if (
                        !empty($reclamacion['docente_id'])
                    ): ?>

                        <p>

                            <strong>
                                Docente asignado:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $reclamacion['docente_id']
                            );

                            ?>

                        </p>

                    <?php endif; ?>


                    <hr>


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