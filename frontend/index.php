<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Objetos Perdidos - Facultad</title>

    <link rel="stylesheet" href="css/home.css">
    <script src="js/home.js" defer></script>
    <script src="js/buscar.js" defer></script>
    <script src="js/menu_usuario.js" defer></script>

</head>

<body>

    <header>

        <div class="header-superior">

            <div>
                <h1>Objetos Perdidos - Facultad</h1>
            </div>

            <div class="usuario-menu">

                <button
                    type="button"
                    id="boton-avatar"
                    class="boton-avatar"
                    aria-label="Abrir menú de usuario"
                >

                    <?php

                    require_once '../backend/config.php';

                    $url_foto =
                        SUPABASE_URL .
                        '/rest/v1/usuarios?id=eq.' .
                        urlencode($_SESSION['usuario_id']) .
                        '&select=foto';

                    $options_foto = [
                        'http' => [
                            'method' => 'GET',
                            'header' =>
                                "apikey: " . SUPABASE_KEY . "\r\n" .
                                "Authorization: Bearer " . SUPABASE_KEY . "\r\n",
                            'ignore_errors' => true
                        ]
                    ];

                    $context_foto =
                        stream_context_create(
                            $options_foto
                        );

                    $response_foto =
                        file_get_contents(
                            $url_foto,
                            false,
                            $context_foto
                        );

                    $datos_foto =
                        json_decode(
                            $response_foto,
                            true
                        );

                    $foto_usuario =
                        $datos_foto[0]['foto'] ?? '';

                    ?>


                    <?php if (!empty($foto_usuario)): ?>

                        <img
                            src="<?php echo htmlspecialchars($foto_usuario); ?>"
                            alt="Avatar"
                        >

                    <?php else: ?>

                        <span class="avatar-letra">
                            <?php
                            echo strtoupper(
                                substr(
                                    $_SESSION['nombre'],
                                    0,
                                    1
                                )
                            );
                            ?>
                        </span>

                    <?php endif; ?>

                </button>

                
                <div
                    id="menu-usuario"
                    class="menu-usuario"
                >

                    <a href="perfil.php">
                        Perfil
                    </a>

                    <?php if (
                        isset($_SESSION['rol']) &&
                        $_SESSION['rol'] === 'docente'
                    ): ?>

                        <a href="docente.php">
                            Panel docente
                        </a>

                    <?php endif; ?>


                    <button
                        type="button"
                        id="boton-configuracion"
                    >
                        Settings and privacy
                        <span>›</span>
                    </button>


                    <div class="separador"></div>


                    <a
                        href="../backend/logout.php"
                        class="cerrar-sesion"
                    >
                        Cerrar sesión
                    </a>

                </div>


                <!-- SUBMENÚ DE CONFIGURACIÓN -->

                <div
                    id="menu-configuracion"
                    class="menu-configuracion"
                >

                    <button
                        type="button"
                        id="volver-menu"
                        class="volver-menu"
                    >
                        ← Settings and privacy
                    </button>


                    <div class="separador"></div>


                    <a href="editar_perfil.php">

                        <strong>
                            Perfil
                        </strong>

                        <span>
                            Editar perfil
                        </span>

                    </a>

                </div>

            </div>

        </div>


        <nav>

            <a href="index.php">
                HOME
            </a>

            <a href="perdido.html">
                ¿Perdiste algo?
            </a>

            <a href="encontrado.html">
                ¿Encontraste algo?
            </a>

        </nav>

    </header>


    <main>

        <section id="contenido-inicial">

            <h2>Objetos perdidos y encontrados</h2>

            <p>
                Encuentra o reporta objetos perdidos dentro de la Facultad.
            </p>

        </section>

        <section>

            <h3>Buscar un objeto</h3>

            <form id="form-busqueda">

                <input
                    type="text"
                    id="busqueda"
                    placeholder="¿Qué estás buscando?"
                    required
                >

                <button type="submit">
                    Buscar
                </button>
            </form>

        </section>

        <section id="seccion-resultados" style="display: none;">
            
            <h2>Resultados de búsqueda</h2>
            
            <div id="resultados"></div>

        </section>


        <section id="seccion-acciones">

            <h3>¿Qué deseas hacer?</h3>

                <a href="perdido.html">
                    <button type="button">
                        Reportar objeto perdido
                    </button>
                </a>

                <a href="encontrado.html">
                    <button type="button">
                        Reportar objeto encontrado
                    </button>
                </a>

        </section>

        <section id="seccion-perdidos">

            <h2>Objetos perdidos</h2>
            <div id="objetos-perdidos"></div>

        </section>

        <section id="seccion-encontrados">

            <h2>Objetos encontrados</h2>
            <div id="objetos-encontrados"></div>

        </section>

    </main>


    <footer>

        <p>
            Universidad Veracruzana - Facultad
        </p>

    </footer>

</body>

</html>