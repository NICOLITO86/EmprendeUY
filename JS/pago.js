const formulario = document.getElementById("formulario");
const inputNumero = document.getElementById("numeroT");
const inputFecha = document.getElementById("fecha");
const inputCvv = document.getElementById("cvv");

/* ----------------------------------------------------------
   Plan elegido (Pago.html?plan=premium)
   Estos precios son SOLO para mostrarlos en pantalla.
   El servidor tiene que calcular el monto con su propia tabla.
   ---------------------------------------------------------- */
const PLANES = {
    basico:    { nombre: "Plan Básico",    precio: 1 },
    destacado: { nombre: "Plan Destacado", precio: 3 },
    premium:   { nombre: "Plan Premium",   precio: 5 }
};

const planId = new URLSearchParams(location.search).get("plan");
const plan = Object.hasOwn(PLANES, planId) ? PLANES[planId] : null;

/* ---------- Validaciones ---------- */

// Algoritmo de Luhn: detecta números de tarjeta mal escritos
function numeroTarjetaValido(numero) {
    if (!/^\d{13,19}$/.test(numero)) return false;

    let suma = 0;
    let duplicar = false;

    for (let i = numero.length - 1; i >= 0; i--) {
        let digito = Number(numero[i]);
        if (duplicar) {
            digito *= 2;
            if (digito > 9) digito -= 9;
        }
        suma += digito;
        duplicar = !duplicar;
    }

    return suma % 10 === 0;
}

// MM/AA: mes entre 01 y 12 y que no esté vencida (vale hasta el último día del mes)
function vencimientoValido(texto) {
    const partes = /^(0[1-9]|1[0-2])\/(\d{2})$/.exec(texto);
    if (!partes) return false;

    const mes = Number(partes[1]);
    const anio = 2000 + Number(partes[2]);

    return new Date(anio, mes, 1) > new Date();
}

function mostrarError(titulo, texto) {
    Swal.fire({ icon: "error", title: titulo, text: texto });
}

/* ---------- Formato mientras se escribe ---------- */

// Número: solo dígitos, con un espacio cada 4
inputNumero.addEventListener("input", () => {
    const digitos = inputNumero.value.replace(/\D/g, "").slice(0, 19);
    inputNumero.value = digitos.replace(/(.{4})/g, "$1 ").trim();
});

// Vencimiento: se agrega la barra sola (1234 -> 12/34)
inputFecha.addEventListener("input", () => {
    let digitos = inputFecha.value.replace(/\D/g, "").slice(0, 4);
    if (digitos.length >= 3) digitos = digitos.slice(0, 2) + "/" + digitos.slice(2);
    inputFecha.value = digitos;
});

// CVV: solo dígitos
inputCvv.addEventListener("input", () => {
    inputCvv.value = inputCvv.value.replace(/\D/g, "").slice(0, 4);
});

/* ---------- Envío ---------- */

const modoCompra = new URLSearchParams(location.search).get("compra") === "carrito";

function obtenerToken() {
    return fetch("../PHP/csrf_token.php", { credentials: "same-origin" })
        .then((res) => res.json())
        .then((d) => d.token);
}

function formatearPrecio(valor) {
    return `$${Number(valor).toLocaleString("es-UY")}`;
}

// Arma el formulario para un tipo de pago.
// config: { concepto, monto, url, campos, exitoTitulo, exitoTexto, destino }
function configurarFormulario(config) {
    document.getElementById("concepto").textContent = config.concepto;
    document.getElementById("monto").textContent = config.monto;

    formulario.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!numeroTarjetaValido(inputNumero.value.replace(/\s/g, ""))) {
            mostrarError("Número de tarjeta inválido", "Revisá que el número esté bien escrito.");
            return;
        }

        if (!vencimientoValido(inputFecha.value)) {
            mostrarError("Fecha de vencimiento inválida", "Usá el formato MM/AA y una fecha que no haya pasado.");
            return;
        }

        if (!/^\d{3,4}$/.test(inputCvv.value)) {
            mostrarError("Código de seguridad inválido", "El CVV tiene 3 o 4 números y está en el dorso de la tarjeta.");
            return;
        }

        const boton = formulario.querySelector('button[type="submit"]');
        boton.disabled = true; // evita que se apriete dos veces y se pague doble

        // Los datos de la tarjeta NO se mandan al servidor. Solo viaja lo mínimo
        // (el plan, o nada para el carrito) y el token de seguridad.
        try {
            const token = await obtenerToken();
            const datos = new FormData();
            Object.entries(config.campos).forEach(([k, v]) => datos.append(k, v));
            datos.append("csrf_token", token);

            const res = await fetch(config.url, { method: "post", body: datos, credentials: "same-origin" });
            const respuesta = await res.json();

            if (respuesta.exito) {
                await Swal.fire({
                    title: config.exitoTitulo,
                    text: config.exitoTexto,
                    icon: "success",
                    timer: 1800,
                    showConfirmButton: false
                });
                window.location.href = config.destino;
            } else {
                mostrarError("No se pudo realizar el pago", respuesta.msg || respuesta.mensaje || "Intentá de nuevo.");
                boton.disabled = false;
            }
        } catch (err) {
            mostrarError("Algo salió mal", "No pudimos procesar el pago. Probá de nuevo en unos minutos.");
            boton.disabled = false;
        }
    });
}

if (modoCompra) {
    // Compra del carrito: el total que se muestra sale del carrito real del usuario.
    fetch("../PHP/mostrarcarrito.php", { credentials: "same-origin" })
        .then((res) => res.json())
        .then((items) => {
            if (!Array.isArray(items) || items.length === 0) {
                window.location.replace("carrito.html");
                return;
            }
            const total = items.reduce((acc, p) => acc + Number(p.precio) * Number(p.cantidad), 0);
            const unidades = items.reduce((acc, p) => acc + Number(p.cantidad), 0);
            configurarFormulario({
                concepto: `Compra de ${unidades} producto${unidades === 1 ? "" : "s"}`,
                monto: formatearPrecio(total),
                url: "../PHP/comprar.php",
                campos: {},
                exitoTitulo: "¡Compra realizada!",
                exitoTexto: "Gracias por tu compra.",
                destino: "tienda.html"
            });
        })
        .catch(() => window.location.replace("carrito.html"));
} else if (plan) {
    configurarFormulario({
        concepto: `${plan.nombre} (mensual)`,
        monto: `${plan.precio} USD`,
        url: "../PHP/pago.php",
        campos: { plan: planId },
        exitoTitulo: "Pago realizado!",
        exitoTexto: "Ahora creá tu emprendimiento.",
        destino: "crearemprendimiento.html"
    });
} else {
    // Ni carrito ni plan: no hay nada que pagar
    window.location.replace("carrito.html");
}
