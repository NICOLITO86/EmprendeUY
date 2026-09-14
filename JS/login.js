const formulario=document.getElementById("formulario2")
const div=document.getElementById("contenedor")

formulario2.addEventListener ("submit",(f)=> {
    f.preventDefault()

    const cedula = document.getElementById("Cedula").value;

    if (!validarCedula(cedula)) {
        alert("La cédula debe tener exactamente 8 dígitos numéricos.");
        return;
    }

let form=new FormData(formulario2)
  fetch("../PHP/login.php", {
    method: "POST",
    body: form
})
.then(res => res.json())
.then(datos => {
    console.log(datos)
    if(datos.success){
        window.location.href = datos.redirect;
    }else{
        alert(datos.mensaje);
    }

});
})
 