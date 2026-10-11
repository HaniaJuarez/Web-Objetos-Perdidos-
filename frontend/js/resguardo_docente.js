
// Espera a que la estructura del árbol DOM esté completamente cargada en el navegador
// antes de ejecutar cualquier lógica o manipulación de elementos en la página.
document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | OBTENER TODAS LAS TARJETAS DE OBJETOS
    |--------------------------------------------------------------------------
    | Selecciona todas las tarjetas del DOM que tengan la clase ".tarjeta-objeto"
    | y las almacena en una lista de nodos (NodeList) para su posterior iteración.
    |
    */

    const tarjetas = document.querySelectorAll(".tarjeta-objeto");


    /*
    |--------------------------------------------------------------------------
    | ANIMACIÓN SENCILLA AL CARGAR (EFECTO FADE-IN EN CASCADA)
    |--------------------------------------------------------------------------
    | Recorre cada tarjeta seleccionada aplicando un retraso progresivo basado
    | en su índice para lograr un efecto de aparición secuencial de arriba a abajo.
    |
    */

    tarjetas.forEach((tarjeta, indice) => {

        // Oculta inicialmente la tarjeta estableciendo su opacidad en 0
        tarjeta.style.opacity = "0";

        // Asigna un temporizador individual para iniciar la animación de cada tarjeta
        setTimeout(() => {

            // Configura la propiedad de transición para suavizar la animación del cambio de opacidad
            tarjeta.style.transition = "opacity 0.4s";

            // Hace visible la tarjeta cambiando la opacidad a 1
            tarjeta.style.opacity = "1";

        // Calcula el tiempo de espera multiplicando el índice del elemento por 100 milisegundos
        }, indice * 100);

    });

});