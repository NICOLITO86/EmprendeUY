const formulario=document.getElementById("formulario")

formulario.addEventListener ("submit",(e)=> {
    e.preventDefault()

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

