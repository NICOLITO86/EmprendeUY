const productos = document.getElementById("productos-carrito");

// SEC-04: evita insertar texto de usuario (título, descripción, categoría) tal
// cual dentro de innerHTML, para no quedar expuestos a XSS.
function escapeHtml(texto) {
    const div = document.createElement("div");
    div.textContent = texto ?? "";
    return div.innerHTML;
}

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
                    <h3>${escapeHtml(p.titulo)}</h3>
                    <p>${escapeHtml(p.descripcion)}</p>
                    <p><strong>$${p.precio}</strong> &times; ${p.cantidad}</p>
                    <p>${escapeHtml(p.categoria)}</p>
                </div>
            `;
        });

    })
    .catch(err => {
        console.error("Error al cargar el carrito:", err);
        productos.innerHTML = "<p>Ocurrio un error al cargar el carrito</p>";
    });