const formulario=document.getElementById("formulario2")
const div=document.getElementById("contenedor")

formulario2.addEventListener ("submit",(f)=> {
    f.preventDefault()

let form=new FormData(formulario2)
  fetch("../PHP/login.php", {
    method: "POST",
    body: form
})
.then(res => res.json())
.then(datos => {
    if(datos.success){
        window.location.href = datos.redirect;
    }else{
        alert(datos.mensaje);
    }

});
})
 