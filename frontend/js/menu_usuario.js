// Espera a que todo el documento HTML esté completamente cargado y parseado antes de ejecutar el código
document.addEventListener("DOMContentLoaded", () => {

    // Obtiene la referencia del botón/avatar del usuario mediante su ID
    const botonAvatar =
        document.getElementById("boton-avatar");

    // Obtiene la referencia del menú desplegable de usuario
    const menuUsuario =
        document.getElementById("menu-usuario");

    // Obtiene la referencia del botón para abrir la sección de configuración
    const botonConfiguracion =
        document.getElementById("boton-configuracion");

    // Obtiene la referencia del submenú o panel de configuración
    const menuConfiguracion =
        document.getElementById("menu-configuracion");

    // Obtiene la referencia del botón para regresar del menú de configuración al menú de usuario
    const volverMenu =
        document.getElementById("volver-menu");


    // Cláusula de guarda: si el botón del avatar no existe en el DOM de la página actual, detiene la ejecución del script
    if (!botonAvatar) {
        return;
    }


    // Asigna el evento de clic al avatar para alternar la visibilidad del menú principal de usuario
    botonAvatar.addEventListener("click", (evento) => {

        // Evita que el evento se propague hacia el 'document', impidiendo que se cierre inmediatamente el menú
        evento.stopPropagation();

        // Asegura que el submenú de configuración permanezca oculto
        menuConfiguracion.style.display = "none";

        // Alterna la visibilidad del menú de usuario (lo oculta si está visible, o lo muestra si está oculto)
        if (
            menuUsuario.style.display === "block"
        ) {

            menuUsuario.style.display = "none";

        } else {

            menuUsuario.style.display = "block";

        }

    });


    // Asigna el evento de clic al botón de configuración para navegar al submenú de configuración
    botonConfiguracion.addEventListener(
        "click",
        (evento) => {

            // Evita que el clic se propague al 'document' y dispare el cierre automático
            evento.stopPropagation();

            // Oculta el menú principal del usuario
            menuUsuario.style.display = "none";

            // Muestra el submenú de configuración
            menuConfiguracion.style.display = "block";

        }
    );


    // Asigna el evento de clic al botón "volver" para regresar del submenú de configuración al menú principal
    volverMenu.addEventListener(
        "click",
        () => {

            // Oculta el menú de configuración
            menuConfiguracion.style.display = "none";

            // Muestra nuevamente el menú principal de usuario
            menuUsuario.style.display = "block";

        }
    );


    // Asigna un controlador global de clic al 'document' para cerrar los menús cuando el usuario hace clic fuera de ellos
    document.addEventListener(
        "click",
        () => {

            // Oculta el menú principal de usuario
            menuUsuario.style.display = "none";

            // Oculta el menú de configuración
            menuConfiguracion.style.display = "none";

        }
    );

});