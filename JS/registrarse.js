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
                title: "Agregado!",
                icon: "success",
                draggable: true
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

