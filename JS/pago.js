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

function iniciar() {
    document.getElementById("concepto").textContent = `${plan.nombre} (mensual)`;
    document.getElementById("monto").textContent = `${plan.precio} USD`;

    formulario.addEventListener("submit", (e) => {
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

        // Solo viaja el plan: los datos de la tarjeta NO se mandan al servidor.
        // El servidor calcula el monto a partir del plan.
        const datos = new FormData();
        datos.append("plan", planId);

        fetch("../PHP/pago.php", { method: "post", body: datos })
            .then((res) => res.json())
            .then((respuesta) => {
                if (respuesta.exito) {
                    Swal.fire({
                        title: "Pago realizado!",
                        text: "Ahora creá tu emprendimiento.",
                        icon: "success",
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "crearemprendimiento.html";
                    });
                } else {
                    mostrarError("No se pudo realizar el pago", respuesta.msg);
                    boton.disabled = false;
                }
            })
            .catch(() => {
                mostrarError("Algo salió mal", "No pudimos procesar el pago. Probá de nuevo en unos minutos.");
                boton.disabled = false;
            });
    });
}

// Sin un plan válido en la dirección no hay nada que pagar: se vuelve a elegir
if (plan) {
    iniciar();
} else {
    window.location.replace("EmprendeUY.html#planes");
}
