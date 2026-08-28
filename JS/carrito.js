const productos = document.getElementById("productos-carrito");

fetch("../PHP/mostrarcarrito.php")
    .then(res => res.json()) 
    .then(datos => {

        console.log(datos);

        productos.innerHTML = "";

        if (!Array.isArray(datos) || datos.length === 0) {
            productos.innerHTML = "<p>No hay productos en el carrito</p>";
            return;
        }

       
        datos.forEach(p => {
            productos.innerHTML += `
                <div class="carrito-item">
                    <img src="../PHP/mostrar_imagen.php?id=${p.id}">
                    <h3>${p.titulo}</h3>
                    <p>${p.descripcion}</p>
                    <p><strong>$${p.precio}</strong></p>
                    <p>${p.categoria}</p>
                </div>
            `;
        });

    })
    .catch(err => {
        console.error("Error al cargar el carrito:", err);
        productos.innerHTML = "<p>Ocurrio un error al cargar el carrito</p>";
    });