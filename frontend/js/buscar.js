// Registra un evento que se ejecuta automáticamente cuando el árbol DOM de la página ha sido cargado por completo.
document.addEventListener("DOMContentLoaded", () => {

    // Obtiene la referencia del elemento formulario de búsqueda por su ID.
    const formulario =
        document.getElementById("form-busqueda");

    // Valida si el formulario no existe en la página actual; si es así, detiene la ejecución.
    if (!formulario) {
        return;
    }

    // Escucha el evento de envío (submit) del formulario y ejecuta la función asíncrona "buscarObjetos".
    formulario.addEventListener(
        "submit",
        buscarObjetos
    );

});


// Función asíncrona encargada de procesar el evento de búsqueda y mostrar los resultados obtenidos.
async function buscarObjetos(evento) {

    // Cancela el comportamiento por defecto del evento submit (evita la recarga de la página).
    evento.preventDefault();

    // Obtiene el valor ingresado en el campo de texto de búsqueda eliminando los espacios en blanco al inicio y final.
    const texto =
        document.getElementById("busqueda").value.trim();

    // Obtiene el contenedor HTML donde se renderizarán las tarjetas de resultados.
    const resultados =
        document.getElementById("resultados");

    // Obtiene la sección contenedora principal de los resultados de búsqueda.
    const seccionResultados =
        document.getElementById("seccion-resultados");


    // Si el texto ingresado está completamente vacío, detiene la ejecución de la función.
    if (texto === "") {
        return;
    }

    // Mostrar la sección de resultados asignándole visibilidad de tipo bloque en el CSS.
    seccionResultados.style.display = "block";

    // Ocultar el contenido inicial cambiándole la propiedad display a "none".
    document.getElementById("contenido-inicial").style.display = "none";


    // Ocultar las acciones fijando su display en "none".
    document.getElementById("seccion-acciones").style.display = "none";


    // Ocultar objetos perdidos cambiando su visibilidad a "none".
    document.getElementById("seccion-perdidos").style.display = "none";


    // Ocultar objetos encontrados modificando su estilo display a "none".
    document.getElementById("seccion-encontrados").style.display = "none";


    // Mostrar mensaje de búsqueda temporal mientras se completa la consulta HTTP.
    resultados.innerHTML =
        "<p>Buscando...</p>";


    try {

        // Realiza una petición asíncrona HTTP GET al backend codificando el término de búsqueda de forma segura en la URL.
        const respuesta =
            await fetch(
                "../backend/buscar_objetos.php?q=" +
                encodeURIComponent(texto)
            );

        // Verifica si la respuesta devuelta por el servidor no tiene un estado de éxito HTTP (rango 200-299).
        if (!respuesta.ok) {

            // Lanza un error indicando el código de estado devuelto.
            throw new Error(
                "Error HTTP: " + respuesta.status
            );

        }


        // Convierte el contenido del cuerpo de la respuesta devuelta por el servidor a formato JSON.
        const objetos =
            await respuesta.json();


        // Limpia el mensaje de "Buscando..." para preparar el contenedor de resultados.
        resultados.innerHTML = "";


        // Verifica si la respuesta JSON contiene una propiedad indicando un error del servidor.
        if (objetos.error) {

            // Despliega un mensaje de error dentro del contenedor de resultados para el usuario.
            resultados.innerHTML =
                "<p>Error al realizar la búsqueda.</p>";

            // Muestra en la consola de desarrollo el mensaje de error recibido.
            console.error(objetos.error);

            // Detiene la ejecución posterior del código.
            return;
        }


        // Evalúa si el arreglo de objetos obtenido está vacío.
        if (objetos.length === 0) {

            // Muestra un mensaje en pantalla informando que no hubo coincidencias.
            resultados.innerHTML =
                "<p>No se encontraron objetos.</p>";

            // Finaliza la ejecución de la función.
            return;
        }


        // Recorre cada uno de los objetos obtenidos en el arreglo de respuestas.
        objetos.forEach(objeto => {

            // Crea un nuevo elemento div en memoria para representar la tarjeta del objeto.
            const tarjeta =
                document.createElement("div");

            // Le asigna la clase CSS "tarjeta-objeto" para dar estilo al contenedor.
            tarjeta.className =
                "tarjeta-objeto";


            // Declara la variable encargada de guardar el marcado de la imagen.
            let imagen = "";

            // Comprueba si el objeto cuenta con una ruta o URL de imagen asociada.
            if (objeto.imagen) {

                // Asigna la etiqueta <img> con los datos de la fotografía del objeto.
                imagen = `
                    <img
                        src="${objeto.imagen}"
                        alt="Fotografía del objeto"
                    >
                `;

            }


            // Inicializa el valor por defecto para la categoría como "Sin categoría".
            let categoria = "Sin categoría";

            // Verifica si el objeto contiene información relativa a su categoría.
            if (objeto.categorias) {

                // Extrae el nombre de la categoría del objeto.
                categoria =
                    objeto.categorias.nombre;

            }


            // Inserta la plantilla de marcado HTML con las propiedades dinámicas extraídas del objeto.
            tarjeta.innerHTML = `

                ${imagen}

                <h3>
                    ${objeto.nombre}
                </h3>

                <p>
                    ${objeto.descripcion_publica || ""}
                </p>

                <p>
                    <strong>Color:</strong>
                    ${objeto.color || "No especificado"}
                </p>

                <p>
                    <strong>Categoría:</strong>
                    ${categoria}
                </p>

                <p>
                    <strong>Estado:</strong>
                    ${objeto.estado}
                </p>

                <a href="objeto.php?id=${objeto.id}">
                    Ver detalles
                </a>

            `;


            // Agrega el elemento tarjeta creado dentro del contenedor principal de resultados en el DOM.
            resultados.appendChild(tarjeta);

        });


    } catch (error) {

        // En caso de capturar una excepción, muestra un mensaje de error informativo dentro del contenedor HTML.
        resultados.innerHTML =
            "<p>Error al realizar la búsqueda.</p>";

        // Imprime en la consola del navegador la información detallada del error ocurrido.
        console.error(
            "Error en la búsqueda:",
            error
        );

    }

}