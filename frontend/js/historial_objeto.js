// Espera a que el documento HTML esté completamente cargado e interpretado
// antes de ejecutar cualquier manipulación del DOM.
document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | OBTENER ELEMENTOS DEL HISTORIAL
    |--------------------------------------------------------------------------
    | Selecciona todos los elementos con la clase ".movimiento" que conforman
    | la línea de tiempo del historial para aplicarles la animación.
    |
    */

    const movimientos =
        document.querySelectorAll(".movimiento");


    /*
    |--------------------------------------------------------------------------
    | ANIMACIÓN DE ENTRADA EN CASCADA
    |--------------------------------------------------------------------------
    | Recorre cada elemento del historial, estableciendo un estado inicial oculto
    | y desplazado para luego revelarlo secuencialmente según su índice.
    |
    */

    movimientos.forEach((movimiento, indice) => {

        // Oculta el elemento haciendo su opacidad invisible
        movimiento.style.opacity = "0";

        // Desplaza el elemento 10px hacia abajo respecto a su posición original
        movimiento.style.transform =
            "translateY(10px)";


        // Programa la ejecución de la animación de forma escalonada
        setTimeout(() => {

            // Define la transición suave para las propiedades de opacidad y transformación
            movimiento.style.transition =
                "opacity 0.4s ease, transform 0.4s ease";

            // Restablece la opacidad para hacer visible el elemento
            movimiento.style.opacity = "1";

            // Devuelve el elemento a su posición vertical original
            movimiento.style.transform =
                "translateY(0)";

        // Multiplica el índice por 120 ms para desfasar la aparición de cada elemento
        }, indice * 120);

    });

});