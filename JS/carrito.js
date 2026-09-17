const productos = document.getElementById("productos-carrito");
const carritoVacio = document.getElementById("carrito-vacio");
const totalCarritoEl = document.getElementById("total-carrito");
const btnVaciar = document.getElementById("vaciar-carrito");

// SEC-04: evita insertar texto de usuario (título, descripción, categoría) tal
// cual dentro de innerHTML, para no quedar expuestos a XSS.
function escapeHtml(texto) {
    const div = document.createElement("div");
    div.textContent = texto ?? "";
    return div.innerHTML;
}

// Suma precio * cantidad de cada línea del carrito.
function calcularTotal(datos) {
    return datos.reduce((acc, p) => acc + (Number(p.precio) * Number(p.cantidad)), 0);
}

function renderCarrito(datos) {
    productos.innerHTML = "";

    if (!Array.isArray(datos) || datos.length === 0) {
        carritoVacio.hidden = false;
        totalCarritoEl.textContent = "$0";
        return;
    }

    carritoVacio.hidden = true;

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

    totalCarritoEl.textContent = `$${calcularTotal(datos).toFixed(2)}`;
}

function cargarCarrito() {
    fetch("../PHP/mostrarcarrito.php")
        .then(res => res.json())
        .then(datos => {
            console.log(datos);
            renderCarrito(datos);
        })
        .catch(err => {
            console.error("Error al cargar el carrito:", err);
            productos.innerHTML = "<p>Ocurrió un error al cargar el carrito</p>";
        });
}

async function vaciarCarrito() {
    const tokenRes = await fetch("../PHP/csrf_token.php");
    const tokenDatos = await tokenRes.json();

    fetch("../PHP/vaciarcarrito.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `csrf_token=${encodeURIComponent(tokenDatos.token)}`
    })
        .then(res => res.json())
        .then(datos => {
            if (datos.exito) {
                renderCarrito([]);
            } else {
                console.error(datos.mensaje ?? "No se pudo vaciar el carrito.");
                productos.innerHTML = "<p>No se pudo vaciar el carrito.</p>";
            }
        })
        .catch(err => console.error("Error al vaciar el carrito:", err));
}

btnVaciar.addEventListener("click", vaciarCarrito);

cargarCarrito();