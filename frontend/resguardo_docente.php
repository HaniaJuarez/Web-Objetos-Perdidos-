<?php

require_once '../backend/verificar_sesion.php';
require_once '../backend/config.php';

/*
|--------------------------------------------------------------------------
| Verificar que el usuario sea docente
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    echo "Acceso denegado.";
    exit;
}

/*
|--------------------------------------------------------------------------
| Consultar objetos que están en resguardo
|--------------------------------------------------------------------------
*/

$url =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?estado=eq.En%20resguardo' .
    '&select=*,categorias(nombre)' .
    '&order=id.desc';

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

$response = file_get_contents($url, false, $context);

if ($response === false) {
    $objetos = [];
    $error = "No se pudieron consultar los objetos en resguardo.";
} else {
    $objetos = json_decode($response, true);

    if (!is_array($objetos)) {
        $objetos = [];
    }

    $error = '';
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

    <title>Objetos en resguardo</title>

    <link
        rel="stylesheet"
        href="css/resguardo_docente.css"
    >

    <script
        src="js/resguardo_docente.js"
        defer
    ></script>

</head>

<body>

    <header class="encabezado">

        <div class="encabezado-contenido">

            <h1>
                Objetos en resguardo
            </h1>

            <a
                href="docente.php"
                class="boton-regresar"
            >
                ← Panel docente
            </a>

        </div>

    </header>


    <main class="contenedor">

        <section class="introduccion">

            <h2>
                Objetos actualmente en resguardo
            </h2>

            <p>
                En esta sección puedes consultar los objetos que se
                encuentran actualmente bajo resguardo de la Facultad.
            </p>

        </section>


        <?php if (!empty($error)): ?>

            <div class="mensaje error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if (empty($objetos)): ?>

            <section class="mensaje vacio">

                <h3>
                    No hay objetos en resguardo
                </h3>

                <p>
                    Actualmente no existen objetos registrados con el
                    estado "En resguardo".
                </p>

            </section>

        <?php else: ?>

            <section class="lista-objetos">

                <?php foreach ($objetos as $objeto): ?>

                    <article class="tarjeta-objeto">

                        <?php if (!empty($objeto['imagen'])): ?>

                            <div class="imagen-contenedor">

                                <img
                                    src="<?php echo htmlspecialchars($objeto['imagen']); ?>"
                                    alt="Fotografía del objeto"
                                >

                            </div>

                        <?php else: ?>

                            <div class="sin-imagen">

                                Sin fotografía

                            </div>

                        <?php endif; ?>


                        <div class="informacion-objeto">

                            <h3>
                                <?php
                                echo htmlspecialchars(
                                    $objeto['nombre'] ?? 'Sin nombre'
                                );
                                ?>
                            </h3>


                            <p>

                                <strong>
                                    Categoría:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['categorias']['nombre']
                                    ?? 'Sin categoría'
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Estado:
                                </strong>

                                <span class="estado">
                                    <?php
                                    echo htmlspecialchars(
                                        $objeto['estado'] ?? 'Sin estado'
                                    );
                                    ?>
                                </span>

                            </p>


                            <p>

                                <strong>
                                    Descripción:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $objeto['descripcion_publica']
                                    ?? 'Sin descripción'
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


                            <?php if (!empty($objeto['fecha'])): ?>

                                <p>

                                    <strong>
                                        Fecha:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $objeto['fecha']
                                    );
                                    ?>

                                </p>

                            <?php endif; ?>


                            <?php if (!empty($objeto['ubicacion'])): ?>

                                <p>

                                    <strong>
                                        Ubicación:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $objeto['ubicacion']
                                    );
                                    ?>

                                </p>

                            <?php endif; ?>


                            <?php if (!empty($objeto['responsable'])): ?>

                                <p>

                                    <strong>
                                        Responsable:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $objeto['responsable']
                                    );
                                    ?>

                                </p>

                            <?php endif; ?>


                            <a
                                href="objeto.php?id=<?php echo urlencode($objeto['id']); ?>"
                                class="boton-detalles"
                            >
                                Ver detalles
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>


    <footer>

        <p>
            Universidad Veracruzana - Facultad
        </p>

    </footer>

</body>

</html>