function sendMail () {
    let parms = {
        name : document.getElementById("name").value,
        email : document.getElementById("email").value,
        subject : document.getElementById("subject").value,
        message : document.getElementById("message").value,
    }  

    sendMail.send("service_7k06keg","template_vqnm8qa",parms).then(alert("Email Sent!"))
}