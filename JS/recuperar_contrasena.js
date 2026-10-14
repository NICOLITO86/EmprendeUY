// Recuperación de contraseña en dos pasos: 1) pedir código por correo, 2) código + contraseña nueva.
const formSolicitar = document.getElementById("formSolicitar");
const formConfirmar = document.getElementById("formConfirmar");
const correoSol     = document.getElementById("correoSolicitar");
const correoConf    = document.getElementById("correoConfirmar");
const inputCodigo   = document.getElementById("codigo");
const btnSolicitar  = document.getElementById("btnSolicitar");
const btnConfirmar  = document.getElementById("btnConfirmar");
const btnReenviar   = document.getElementById("btnReenviar");
const btnYaTengo    = document.getElementById("btnYaTengo");
const mensaje       = document.getElementById("mensaje");

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

function irAlPaso2(correo) {
    formSolicitar.classList.add("oculto");
    formConfirmar.classList.remove("oculto");
    if (correo) correoConf.value = correo;
    inputCodigo.focus();
}

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

async function pedirCodigo(correo) {
    const token = await obtenerToken();
    const datos = new FormData();
    datos.append("correo", correo);
    datos.append("csrf_token", token);
    const res = await fetch("../PHP/recuperar_solicitar.php", { method: "POST", body: datos, credentials: "same-origin" });
    return res.json();
}

inputCodigo.addEventListener("input", () => {
    inputCodigo.value = inputCodigo.value.replace(/\D/g, "").slice(0, 6);
});

btnYaTengo.addEventListener("click", () => {
    mostrar("", "");
    irAlPaso2(correoSol.value.trim());
});

formSolicitar.addEventListener("submit", async (e) => {
    e.preventDefault();
    btnSolicitar.disabled = true;
    try {
        const r = await pedirCodigo(correoSol.value.trim());
        if (r.success) {
            mostrar(r.mensaje, "ok");
            irAlPaso2(correoSol.value.trim());
            iniciarEspera(r.espera || 60);
        } else {
            mostrar(r.mensaje, "error");
        }
    } catch (err) {
        mostrar("Error de conexión. Intentá de nuevo.", "error");
    } finally {
        btnSolicitar.disabled = false;
    }
});

btnReenviar.addEventListener("click", async () => {
    btnReenviar.disabled = true;
    try {
        const r = await pedirCodigo(correoConf.value.trim());
        mostrar(r.mensaje, r.success ? "ok" : "error");
        if (r.success) iniciarEspera(r.espera || 60);
        else btnReenviar.disabled = false;
    } catch (err) {
        mostrar("Error de conexión. Intentá de nuevo.", "error");
        btnReenviar.disabled = false;
    }
});

formConfirmar.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nueva = document.getElementById("nueva").value;
    const confirmar = document.getElementById("confirmar").value;

    if (!/^\d{6}$/.test(inputCodigo.value.trim())) {
        mostrar("El código debe tener 6 dígitos.", "error");
        return;
    }
    if (nueva.length < 8) {
        mostrar("La contraseña debe tener al menos 8 caracteres.", "error");
        return;
    }
    if (nueva !== confirmar) {
        mostrar("Las contraseñas no coinciden.", "error");
        return;
    }

    btnConfirmar.disabled = true;
    try {
        const token = await obtenerToken();
        const datos = new FormData(formConfirmar);
        datos.append("csrf_token", token);

        const res = await fetch("../PHP/recuperar_confirmar.php", { method: "POST", body: datos, credentials: "same-origin" });
        const r = await res.json();

        if (r.success) {
            mostrar(r.mensaje, "ok");
            setTimeout(() => (window.location.href = r.redirect), 1500);
            return;
        }
        mostrar(r.mensaje, "error");
    } catch (err) {
        mostrar("Error de conexión. Intentá de nuevo.", "error");
    } finally {
        btnConfirmar.disabled = false;
    }
});
