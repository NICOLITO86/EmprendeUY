// validaciones.js
// Funciones de validación reutilizables para los formularios de EmprendeUY.
// Se cargan como script global (sin módulos) para poder usarlas directo
// en cualquier página con: <script src="../JS/validaciones.js"></script>

const EDAD_MINIMA = 16;

/**
 * La cédula uruguaya (sin dígito verificador separado en este sistema)
 * debe tener exactamente 8 dígitos numéricos. Ni más, ni menos.
 */
function validarCedula(valor) {
    const limpio = (valor ?? "").trim();
    return /^\d{8}$/.test(limpio);
}

/**
 * El número de teléfono debe tener exactamente 9 dígitos numéricos.
 */
function validarTelefono(valor) {
    const limpio = (valor ?? "").trim();
    return /^\d{9}$/.test(limpio);
}

/**
 * Calcula la edad en años a partir de una fecha de nacimiento (string "YYYY-MM-DD",
 * tal como la entrega un <input type="date">). Devuelve null si la fecha no es válida.
 */
function calcularEdad(fechaNacimientoStr) {
    if (!fechaNacimientoStr) return null;

    const nacimiento = new Date(fechaNacimientoStr);
    if (isNaN(nacimiento.getTime())) return null;

    const hoy = new Date();
    if (nacimiento > hoy) return null; // fecha futura, no es válida

    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const aunNoCumpleEsteAnio =
        hoy.getMonth() < nacimiento.getMonth() ||
        (hoy.getMonth() === nacimiento.getMonth() && hoy.getDate() < nacimiento.getDate());

    if (aunNoCumpleEsteAnio) {
        edad--;
    }

    return edad;
}

/**
 * true si, con esa fecha de nacimiento, la persona ya tiene la edad mínima
 * requerida para registrarse (EDAD_MINIMA años).
 */
function cumpleEdadMinima(fechaNacimientoStr) {
    const edad = calcularEdad(fechaNacimientoStr);
    return edad !== null && edad >= EDAD_MINIMA;
}
