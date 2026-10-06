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
| Obtener ID del objeto
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? '';


if (empty($id)) {

    echo "Objeto no especificado.";
    exit;

}


/*
|--------------------------------------------------------------------------
| Consultar objeto
|--------------------------------------------------------------------------
*/

$url =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?id=eq.' .
    urlencode($id) .
    '&select=*,categorias(nombre)';


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


$context =
    stream_context_create($options);


$response =
    file_get_contents(
        $url,
        false,
        $context
    );


if ($response === false) {

    echo "No se pudo consultar el objeto.";
    exit;

}


$objetos =
    json_decode(
        $response,
        true
    );


if (
    !is_array($objetos) ||
    empty($objetos)
) {

    echo "Objeto no encontrado.";
    exit;

}


$objeto = $objetos[0];


/*
|--------------------------------------------------------------------------
| Verificar estado
|--------------------------------------------------------------------------
*/

if (($objeto['estado'] ?? '') !== 'En resguardo') {

    echo "Este objeto no se encuentra en resguardo.";
    echo "<br><br>";
    echo '<a href="resguardo_docente.php">Volver</a>';
    exit;

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

    <title>Registrar entrega</title>

    <link
        rel="stylesheet"
        href="css/entrega.css"
    >

    <script
        src="js/entrega.js"
        defer
    ></script>

</head>


<body>

    <header class="encabezado">

        <div class="encabezado-contenido">

            <h1>
                Registrar entrega
            </h1>

            <a
                href="resguardo_docente.php"
                class="boton-regresar"
            >
                ← Volver a resguardo
            </a>

        </div>

    </header>


    <main class="contenedor">

        <section class="tarjeta">

            <h2>
                Entrega del objeto
            </h2>


            <?php if (!empty($objeto['imagen'])): ?>

                <img
                    src="<?php echo htmlspecialchars($objeto['imagen']); ?>"
                    alt="Fotografía del objeto"
                    class="imagen-objeto"
                >

            <?php endif; ?>


            <div class="informacion">

                <p>

                    <strong>
                        Objeto:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $objeto['nombre'] ?? 'Sin nombre'
                    );
                    ?>

                </p>


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
                        Estado actual:
                    </strong>

                    <span class="estado">
                        En resguardo
                    </span>

                </p>

            </div>


            <div class="advertencia">

                <h3>
                    Confirmar entrega
                </h3>

                <p>
                    Antes de registrar la entrega, verifica que el
                    objeto haya sido entregado correctamente al
                    propietario.
                </p>

            </div>


            <form
                action="../backend/registrar_entrega.php"
                method="POST"
                id="form-entrega"
            >

                <input
                    type="hidden"
                    name="objeto_id"
                    value="<?php echo htmlspecialchars($objeto['id']); ?>"
                >


                <label for="observaciones">

                    Observaciones de la entrega:

                </label>


                <textarea
                    id="observaciones"
                    name="observaciones"
                    rows="5"
                    placeholder="Escribe alguna observación sobre la entrega..."
                ></textarea>


                <div class="acciones">

                    <a
                        href="resguardo_docente.php"
                        class="boton-cancelar"
                    >
                        Cancelar
                    </a>


                    <button
                        type="submit"
                        class="boton-confirmar"
                    >
                        Confirmar entrega
                    </button>

                </div>

            </form>

        </section>

    </main>


    <footer>

        <p>
            Universidad Veracruzana - Facultad
        </p>

    </footer>

</body>

</html>