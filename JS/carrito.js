const productos = document.getElementById("productos-carrito");
const carritoVacio = document.getElementById("carrito-vacio");
const carritoResumen = document.getElementById("carrito-resumen");
const totalCarritoEl = document.getElementById("total-carrito");
const btnVaciar = document.getElementById("vaciar-carrito");

// Pide el token a csrf_token.php y lo cachea para no pedirlo en cada request.
let csrfTokenCache = null;

function obtenerCsrfToken() {
    if (csrfTokenCache) {
        return Promise.resolve(csrfTokenCache);
    }
    return fetch("../PHP/csrf_token.php")
        .then(res => res.json())
        .then(data => {
            csrfTokenCache = data.token;
            return csrfTokenCache;
        });
}

// SEC-04: evita insertar texto de usuario (título, descripción, categoría) tal
// cual dentro de innerHTML, para no quedar expuestos a XSS.
function escapeHtml(texto) {
    const div = document.createElement("div");
    div.textContent = texto ?? "";
    return div.innerHTML;
}

function formatearPrecio(valor) {
    return `$${Number(valor).toLocaleString("es-UY")}`;
}

function calcularTotal(datos) {
    return datos.reduce((acc, p) => acc + Number(p.precio) * Number(p.cantidad), 0);
}

function renderizarCarrito(datos) {
    productos.innerHTML = "";

    if (!Array.isArray(datos) || datos.length === 0) {
        carritoVacio.hidden = false;
        carritoResumen.hidden = true;
        return;
    }

    carritoVacio.hidden = true;
    carritoResumen.hidden = false;

    datos.forEach(p => {
        productos.innerHTML += `
            <div class="carrito-item">
                <img src="../PHP/mostrar_imagen.php?id=${p.id}" alt="${escapeHtml(p.titulo)}">
                <div class="carrito-info">
                    <p class="carrito-categoria">${escapeHtml(p.categoria)}</p>
                    <h3>${escapeHtml(p.titulo)}</h3>
                    <p class="carrito-descripcion">${escapeHtml(p.descripcion)}</p>
                </div>
                <p class="carrito-precio">${formatearPrecio(p.precio)} &times; ${p.cantidad}</p>
                <button class="carrito-eliminar" data-id="${p.id}" title="Eliminar" aria-label="Eliminar producto">&times;</button>
            </div>
        `;
    });

    totalCarritoEl.textContent = formatearPrecio(calcularTotal(datos));
}

function cargarCarrito() {
    fetch("../PHP/mostrarcarrito.php")
        .then(res => res.json())
        .then(datos => {
            renderizarCarrito(datos);
        })
        .catch(err => {
            console.error("Error al cargar el carrito:", err);
            productos.innerHTML = "<p>Ocurrio un error al cargar el carrito</p>";
        });
}

function eliminarDelCarrito(id) {
    obtenerCsrfToken()
        .then(token => {
            const formData = new FormData();
            formData.append("idp", id);
            formData.append("csrf_token", token);

            return fetch("../PHP/EliminarCarrito.php", {
                method: "POST",
                body: formData
            });
        })
        .then(res => res.json())
        .then(respuesta => {
            if (!respuesta.exito) {
                console.error(respuesta.mensaje || "No se pudo eliminar el producto");
                return;
            }
            cargarCarrito();
        })
        .catch(err => console.error("Error al eliminar del carrito:", err));
}

function vaciarCarrito() {
    obtenerCsrfToken()
        .then(token => {
            const formData = new FormData();
            formData.append("csrf_token", token);

            return fetch("../PHP/VaciarCarrito.php", {
                method: "POST",
                body: formData
            });
        })
        .then(res => res.json())
        .then(respuesta => {
            if (!respuesta.exito) {
                console.error(respuesta.mensaje || "No se pudo vaciar el carrito");
                return;
            }
            cargarCarrito();
        })
        .catch(err => console.error("Error al vaciar el carrito:", err));
}

// Delegación de eventos: los .carrito-eliminar se crean dinámicamente
productos.addEventListener("click", (evento) => {
    const boton = evento.target.closest(".carrito-eliminar");
    if (!boton) return;
    eliminarDelCarrito(boton.dataset.id);
});

if (btnVaciar) {
    btnVaciar.addEventListener("click", vaciarCarrito);
}

cargarCarrito();
