document.documentElement.classList.add('js');

const observador = new IntersectionObserver((entradas) => {
    entradas.forEach((entrada) => {
        if (entrada.isIntersecting) {
            entrada.target.classList.add('visible');
        } else {
            entrada.target.classList.remove('visible'); // se reinicia al salir
        }
    });
}, { threshold: 0.2 });

document.querySelectorAll('.revelar').forEach((el) => observador.observe(el));