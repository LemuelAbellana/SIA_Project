function sendMail() {
    let params = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        subject: document.getElementById("subject").value,
        message: document.getElementById("message").value,
    };

    emailjs.send("service_7k06keg", "template_vqnm8qa", params)
        .then(
            function(response) {
                alert("Email sent successfully!");
                console.log("SUCCESS!", response.status, response.text);
            },
            function(error) {
                alert("Failed to send email. Please try again.");
                console.error("FAILED...", error);
            }
        );
}
