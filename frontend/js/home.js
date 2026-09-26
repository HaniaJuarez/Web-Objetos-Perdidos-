// Registra un evento para ejecutar la función "cargarObjetos" automáticamente una vez que el DOM esté completamente cargado.
document.addEventListener("DOMContentLoaded", cargarObjetos);

// Función asíncrona encargada de solicitar y procesar los objetos desde el servidor backend.
async function cargarObjetos() {

    try {

        // Realiza una petición HTTP asíncrona (GET) al script PHP en el backend para obtener la lista de objetos.
        const respuesta = await fetch("../backend/listar_objetos.php");

        // Verifica si la respuesta HTTP no fue exitosa (código distinto al rango 200-299).
        if (!respuesta.ok) {
            // Lanza un error indicando el código de estado HTTP recibido.
            throw new Error("Error HTTP: " + respuesta.status);
        }

        // Transforma la respuesta obtenida del servidor a un objeto/arreglo de JavaScript procesando el JSON.
        const objetos = await respuesta.json();

        // Muestra en la consola los objetos recibidos desde el backend para fines de depuración.
        console.log("Objetos recibidos:", objetos);

        // Comprueba si la respuesta JSON contiene una propiedad de error enviada desde el backend.
        if (objetos.error) {
            // Muestra el mensaje de error devuelto por el servidor en la consola.
            console.error(objetos.error);
            // Detiene la ejecución de la función para evitar procesar un arreglo inválido.
            return;
        }

        // Filtra los objetos cuya propiedad "estado" sea "perdido" y llama a la función para renderizarlos en su contenedor correspondiente.
        mostrarObjetos(
            objetos.filter(objeto =>
                String(objeto.estado).toLowerCase() === "perdido"
            ),
            "objetos-perdidos"
        );

        // Filtra los objetos cuya propiedad "estado" sea "encontrado" y llama a la función para renderizarlos en su contenedor correspondiente.
        mostrarObjetos(
            objetos.filter(objeto =>
                String(objeto.estado).toLowerCase() === "encontrado"
            ),
            "objetos-encontrados"
        );

    } catch (error) {

        // Captura y muestra en la consola cualquier error ocurrido durante la petición o el procesamiento.
        console.error(
            "Error al cargar objetos:",
            error
        );

    }
}


// Función encargada de renderizar la lista de objetos en el contenedor HTML especificado.
function mostrarObjetos(objetos, contenedorId) {

    // Obtiene la referencia del elemento contenedor en el DOM utilizando su ID.
    const contenedor =
        document.getElementById(contenedorId);

    // Valida si el contenedor existe en la página actual; si no existe, finaliza la ejecución de la función.
    if (!contenedor) {
        return;
    }

    // Limpia todo el contenido previo que pudiera tener el contenedor.
    contenedor.innerHTML = "";

    // Toma únicamente los primeros 4 elementos del arreglo de objetos recibidos.
    const limite = objetos.slice(0, 4);

    // Comprueba si el arreglo filtrado no tiene objetos para mostrar.
    if (limite.length === 0) {

        // Inserta un mensaje indicando que no hay registros en ese contenedor.
        contenedor.innerHTML =
            "<p>No hay objetos registrados.</p>";

        // Finaliza la ejecución de la función.
        return;
    }

    // Recorre cada uno de los objetos permitidos (hasta 4) para crear e insertar sus tarjetas correspondientes.
    limite.forEach(objeto => {

        // Crea un nuevo elemento dinámico de tipo <div> en la memoria.
        const tarjeta =
            document.createElement("div");

        // Le asigna la clase CSS "tarjeta-objeto" para aplicar los estilos de la tarjeta.
        tarjeta.className = "tarjeta-objeto";


        // Declara la variable para almacenar el bloque HTML de la imagen.
        let imagen = "";

        // Verifica si el objeto cuenta con una URL de imagen definida.
        if (objeto.imagen) {

            // Genera la etiqueta HTML <img> con la URL de la imagen del objeto.
            imagen = `
                <img
                    src="${objeto.imagen}"
                    alt="Fotografía del objeto"
                >
            `;

        }


        // Declara la variable para almacenar el nombre de la categoría.
        let categoria = "";

        // Verifica si existe la propiedad "categorias" dentro del objeto.
        if (objeto.categorias) {

            // Asigna el nombre de la categoría extraído de la relación obtenida.
            categoria =
                objeto.categorias.nombre;

        }


        // Define el contenido HTML interno de la tarjeta mapeando cada uno de los datos del objeto.
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
                ${categoria || "Sin categoría"}
            </p>

            <p>
                <strong>Estado:</strong>
                ${objeto.estado}
            </p>

            <a href="objeto.php?id=${objeto.id}">
                Ver detalles
            </a>

        `;

        // Añade la tarjeta creada como un elemento hijo dentro del contenedor del DOM.
        contenedor.appendChild(tarjeta);

    });

}