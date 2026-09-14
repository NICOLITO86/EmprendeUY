const formulario=document.getElementById("formulario")

formulario.addEventListener ("submit",(e)=> {
    e.preventDefault()

    const cedula = document.getElementById("cedula").value;
    const telefono = document.getElementById("Num_Telefono").value;
    const fechaNacimiento = document.getElementById("Fecha_Nacimiento").value;

    if (!validarCedula(cedula)) {
        Swal.fire({
            icon: "error",
            title: "Cédula inválida",
            text: "La cédula debe tener exactamente 8 dígitos numéricos (sin puntos ni guiones)."
        });
        return;
    }

    if (!validarTelefono(telefono)) {
        Swal.fire({
            icon: "error",
            title: "Teléfono inválido",
            text: "El número de teléfono debe tener exactamente 9 dígitos."
        });
        return;
    }

    const edad = calcularEdad(fechaNacimiento);

    if (edad === null) {
        Swal.fire({
            icon: "error",
            title: "Fecha inválida",
            text: "Ingresá una fecha de nacimiento válida."
        });
        return;
    }

    if (edad < EDAD_MINIMA) {
        Swal.fire({
            icon: "error",
            title: "No podés registrarte",
            text: `Tenés que tener al menos ${EDAD_MINIMA} años para crear una cuenta en EmprendeUY.`
        });
        return;
    }

    let form=new FormData(formulario)
    fetch("../PHP/registro.php",
    {method:"post",
        body: form})

    

    .then(res=> res.json())
    .then(datos=>{ 
        console.log(datos);
        if(datos.exito){

            Swal.fire({
                title: "Registro exitoso!",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
                }).then(() => {
                    window.location.href = datos.redirect;
                });

        }else{
            
            Swal.fire({
                icon: "error",
                title: "No fue agregado..",
                text: datos.msg,
               
                });
        }

})
})

