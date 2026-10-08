<?php

// ============================================================
// CONFIGURACIÓN E INICIALIZACIÓN DE SESIÓN
// ============================================================

// Incluye el archivo encardo de validar que la sesión del usuario esté iniciada.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes globales de configuración (URL y API Key de Supabase).
require_once '../backend/config.php';


// ============================================================
// VERIFICAR ROL DE ADMINISTRADOR
// ============================================================

// Comprueba que el usuario conectado tenga asignado el rol de 'administrador' en $_SESSION.
// De lo contrario, detiene la ejecución del script y muestra un mensaje de acceso denegado.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'administrador'
) {
    echo "Acceso no autorizado.";
    exit;
}


// ============================================================
// CONSULTAR OBJETOS EN SUPABASE
// ============================================================

// Construye la URL para consultar todos los campos de la tabla 'objetos', 
// incluyendo el nombre de su categoría mediante un JOIN implícito, ordenados descendentemente por ID.
$url =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?select=*,categorias(nombre)' .
    '&order=id.desc';


// Configura los encabezados HTTP necesarios para realizar la petición a la API REST de Supabase.
$options = [
    'http' => [
        // Establece el método HTTP a GET.
        'method' => 'GET',

        // Encabezados con las credenciales de autenticación (apikey y Bearer token) y tipo de contenido.
        'header' =>
            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n",

        // Permite capturar la respuesta del servidor sin interrumpir el flujo ante códigos de error HTTP.
        'ignore_errors' => true
    ]
];


// Crea el flujo de contexto HTTP con la configuración definida.
$context =
    stream_context_create($options);


// Realiza la petición GET a la API de Supabase para traer los registros de los objetos.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// Comprueba si ocurrió una falla en la conexión o en la lectura de la respuesta.
if ($response === false) {

    echo "Error al consultar los objetos.";

    exit;
}


// Decodifica la respuesta en formato JSON a un arreglo asociativo de PHP.
$objetos =
    json_decode(
        $response,
        true
    );


// Valida que el resultado sea un arreglo estructurado; asigna un arreglo vacío si el JSON es inválido.
if (!is_array($objetos)) {

    $objetos = [];

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- Define la codificación de caracteres en UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configuración para asegurar que la interfaz sea responsiva en dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Administración de objetos
    </title>

    <!-- Hoja de estilos de la interfaz de administración de objetos -->
    <link
        rel="stylesheet"
        href="css/admin_objetos.css"
    >

    <!-- Script JavaScript diferido con la lógica de filtrado y búsqueda dinámicos -->
    <script
        src="js/admin_objetos.js"
        defer
    ></script>

</head>


<body>


<!-- ============================================================
     ENCABEZADO PRINCIPAL DE LA VISTA
     ============================================================ -->

<header class="encabezado">

    <div>

        <h1>
            Administración de objetos
        </h1>

        <p>
            Consulta y administra los objetos registrados.
        </p>

    </div>


    <div class="acciones-encabezado">

        <!-- Enlace para regresar al panel general del administrador -->
        <a
            href="administrador.php"
            class="boton"
        >
            ← Panel administrador
        </a>

    </div>

</header>


<!-- ============================================================
     CONTENIDO PRINCIPAL
     ============================================================ -->

<main class="contenedor">


    <!-- ========================================================
         RESUMEN Y CONTEO
         ======================================================== -->

    <section class="resumen">

        <h2>
            Objetos registrados
        </h2>

        <p>

            Total de objetos:

            <strong>
                <?php 
                // Imprime la cantidad total de objetos recuperados de la base de datos
                echo count($objetos); 
                ?>
            </strong>

        </p>

    </section>


    <!-- ========================================================
         BARRA DE FILTROS Y BÚSQUEDA
         ======================================================== -->

    <section class="filtros">

        <!-- Campo de texto para la búsqueda por término -->
        <div class="campo">

            <label for="buscar">
                Buscar objeto
            </label>

            <input
                type="text"
                id="buscar"
                placeholder="Nombre, color o descripción..."
            >

        </div>


        <!-- Menú desplegable para filtrar los objetos por su estado -->
        <div class="campo">

            <label for="filtro-estado">
                Estado
            </label>

            <select id="filtro-estado">

                <option value="">
                    Todos
                </option>

                <option value="Perdido">
                    Perdido
                </option>

                <option value="Encontrado">
                    Encontrado
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
            class="boton-secundario"
        >
            Limpiar filtros
        </button>

    </section>


    <!-- ========================================================
         LISTADO Y RENDERIZADO DE OBJETOS
         ======================================================== -->

    <section class="lista">


        <!-- Condicional que verifica si el arreglo de objetos está vacío -->
        <?php if (empty($objetos)): ?>

            <!-- Mensaje alternativo mostrado si no existen registros en Supabase -->
            <div class="sin-resultados">

                <h3>
                    No hay objetos registrados.
                </h3>

                <p>
                    Actualmente no existen objetos en el sistema.
                </p>

            </div>


        <?php else: ?>


            <!-- Rejilla contenedora para desplegar la lista de objetos -->
            <div
                class="grid-objetos"
                id="lista-objetos"
            >


                <!-- Bucle para iterar sobre cada objeto del arreglo -->
                <?php foreach ($objetos as $objeto): ?>


                    <?php

                    // Asigna el nombre de la categoría relacionada o un texto por defecto
                    $categoria =
                        $objeto['categorias']['nombre']
                        ?? 'Sin categoría';

                    // Asigna el estado actual del objeto o un valor por defecto
                    $estado =
                        $objeto['estado']
                        ?? 'Sin estado';

                    ?>


                    <!-- Tarjeta individual de objeto con atributos data-* para el filtrado en el cliente -->
                    <article
                        class="tarjeta-objeto"

                        data-nombre="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    $objeto['nombre'] ?? ''
                                )
                            );
                        ?>"

                        data-color="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    $objeto['color'] ?? ''
                                )
                            );
                        ?>"

                        data-descripcion="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    $objeto['descripcion_publica'] ?? ''
                                )
                            );
                        ?>"

                        data-estado="<?php
                            echo htmlspecialchars(
                                $estado
                            );
                        ?>"
                    >


                        <!-- CONTENEDOR DE LA IMAGEN DEL OBJETO -->

                        <div class="imagen">

                            <?php if (!empty($objeto['imagen'])): ?>

                                <!-- Fotografía del objeto en caso de existir la URL -->
                                <img
                                    src="<?php
                                        echo htmlspecialchars(
                                            $objeto['imagen']
                                        );
                                    ?>"
                                    alt="Imagen del objeto"
                                >

                            <?php else: ?>

                                <!-- Bloque placeholder para objetos que no disponen de imagen -->
                                <div class="sin-imagen">

                                    Sin imagen

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- INFORMACIÓN Y DETALLES DEL OBJETO -->

                        <div class="informacion">

                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $objeto['nombre']
                                    ?? 'Sin nombre'
                                );

                                ?>

                            </h3>


                            <!-- Badge indicadora del estado actual -->
                            <span
                                class="estado"
                                data-estado-visual="<?php
                                    echo htmlspecialchars(
                                        $estado
                                    );
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $estado
                                );
                                ?>

                            </span>


                            <p>

                                <strong>
                                    Categoría:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $categoria
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Color:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['color']
                                    ?? 'No especificado'
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Fecha:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['fecha']
                                    ?? 'No especificada'
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Ubicación:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['ubicacion']
                                    ?? 'No especificada'
                                );
                                ?>

                            </p>


                            <p class="descripcion">

                                <?php
                                echo htmlspecialchars(
                                    $objeto['descripcion_publica']
                                    ?? 'Sin descripción'
                                );
                                ?>

                            </p>


                            <!-- ACCIONES DISPONIBLES PARA EL OBJETO -->

                            <div class="botones-objeto">

                                <!-- Enlace a la vista detallada del objeto -->
                                <a
                                    href="objeto.php?id=<?php
                                        echo urlencode(
                                            $objeto['id']
                                        );
                                    ?>"
                                    class="boton-ver"
                                >
                                    Ver objeto
                                </a>


                                <!-- Enlace a la vista del historial de movimientos del objeto -->
                                <a
                                    href="historial_objeto.php?id=<?php
                                        echo urlencode(
                                            $objeto['id']
                                        );
                                    ?>"
                                    class="boton-historial"
                                >
                                    Ver historial
                                </a>

                            </div>

                        </div>

                    </article>


                <?php endforeach; ?>


            </div>


            <!-- Bloque de mensaje oculto por defecto que se activa vía JS al no encontrar coincidencias en los filtros -->
            <div
                id="mensaje-sin-resultados"
                class="sin-resultados"
                style="display:none;"
            >

                <h3>
                    No se encontraron objetos.
                </h3>

                <p>
                    Prueba con otro término de búsqueda o estado.
                </p>

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