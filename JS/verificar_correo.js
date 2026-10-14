// Pantalla de verificación de correo (después de registrarse o de iniciar sesión sin verificar).
const form        = document.getElementById("formVerificar");
const inputCodigo = document.getElementById("codigo");
const btnVerif    = document.getElementById("btnVerificar");
const btnReenviar = document.getElementById("btnReenviar");
const ayuda       = document.getElementById("ayuda");
const mensaje     = document.getElementById("mensaje");

const PLANES_VALIDOS = ["basico", "destacado", "premium"];

let csrfToken = null;
let temporizador = null;

function mostrar(texto, tipo) {
    mensaje.textContent = texto;
    mensaje.className = tipo || "";
}

function obtenerToken() {
    if (csrfToken) return Promise.resolve(csrfToken);
    return fetch("../PHP/csrf_token.php", { credentials: "same-origin" })
        .then(r => r.json())
        .then(d => (csrfToken = d.token));
}

// Cuenta regresiva para poder volver a pedir el código.
function iniciarEspera(segundos) {
    clearInterval(temporizador);
    let restante = segundos;
    btnReenviar.disabled = true;
    const pintar = () => {
        btnReenviar.textContent = restante > 0 ? `Reenviar código (${restante}s)` : "Reenviar código";
    };
    pintar();
    temporizador = setInterval(() => {
        restante--;
        pintar();
        if (restante <= 0) {
            clearInterval(temporizador);
            btnReenviar.disabled = false;
        }
    }, 1000);
}

// Solo dígitos en el campo del código.
inputCodigo.addEventListener("input", () => {
    inputCodigo.value = inputCodigo.value.replace(/\D/g, "").slice(0, 6);
});

// Al cargar: ¿hay una verificación pendiente? ¿a qué correo se envió?
fetch("../PHP/verificar_correo.php", { credentials: "same-origin" })
    .then(r => r.json())
    .then(d => {
        if (!d.pendiente) {
            window.location.replace(d.redirect || "iniciar_sesion.html");
            return;
        }
        ayuda.textContent = `Te enviamos un código a ${d.correo}. Ingresalo para activar tu cuenta.`;
        iniciarEspera(60);
    })
    .catch(() => {
        ayuda.textContent = "No pudimos cargar la verificación. Recargá la página.";
    });

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const codigo = inputCodigo.value.trim();
    if (!/^\d{6}$/.test(codigo)) {
        mostrar("El código debe tener 6 dígitos.", "error");
        return;
    }

    btnVerif.disabled = true;
    try {
        const token = await obtenerToken();
        const datos = new FormData();
        datos.append("codigo", codigo);
        datos.append("csrf_token", token);

        const res = await fetch("../PHP/verificar_correo.php", { method: "POST", body: datos, credentials: "same-origin" });
        const r = await res.json();

        if (r.success) {
            mostrar(r.mensaje, "ok");
            // Un emprendedor que venía de elegir un plan sigue a pagar (Registrarse.html?plan=...)
            const planParam = new URLSearchParams(location.search).get("plan");
            const destino = (r.rol === "emprendedor" && PLANES_VALIDOS.includes(planParam))
                ? `Pago.html?plan=${planParam}`
                : r.redirect;
            setTimeout(() => (window.location.href = destino), 900);
            return;
        }
        mostrar(r.mensaje, "error");
        if (r.redirect) setTimeout(() => (window.location.href = r.redirect), 1500);
        inputCodigo.select();
    } catch (err) {
        mostrar("Error de conexión. Intentá de nuevo.", "error");
    } finally {
        btnVerif.disabled = false;
    }
});

btnReenviar.addEventListener("click", async () => {
    btnReenviar.disabled = true;
    try {
        const token = await obtenerToken();
        const datos = new FormData();
        datos.append("csrf_token", token);
        const res = await fetch("../PHP/reenviar_codigo.php", { method: "POST", body: datos, credentials: "same-origin" });
        const r = await res.json();

        if (r.success) {
            mostrar(r.mensaje, "ok");
            iniciarEspera(r.espera || 60);
        } else {
            mostrar(r.detalle ? `${r.mensaje} (Detalle técnico: ${r.detalle})` : r.mensaje, "error");
            if (r.redirect) setTimeout(() => (window.location.href = r.redirect), 1500);
            else if (r.espera && r.espera <= 300) iniciarEspera(r.espera);
            else btnReenviar.disabled = false;
        }
    } catch (err) {
        mostrar("Error de conexión. Intentá de nuevo.", "error");
        btnReenviar.disabled = false;
    }
});
