function openNav() {
    const nav = document.getElementById("Navigation");
    if (nav) {
        nav.style.width = "250px";
    }
}

function closeNav() {
    const nav = document.getElementById("Navigation");
    if (nav) {
        nav.style.width = "0";
        nav.style.overflow = "hidden";
    }
}

function openAction(evt, name) {
    let i;
    const acontent = document.getElementsByClassName("acontent");
    for (i = 0; i < acontent.length; i++) {
        acontent[i].style.display = "none";
    }

    if (evt) {
        const alink = document.getElementsByClassName("alink");
        for (i = 0; i < alink.length; i++) {
            alink[i].className = alink[i].className.replace(" active", "");
        }
        evt.currentTarget.className += " active";
    }

    const target = document.getElementById(name);
    if (target) {
        target.style.display = "block";
    }
}

window.onload = function () {
    openAction(null, 'Home');
}

document.addEventListener("DOMContentLoaded", function () {
    const popoverElements = document.querySelectorAll('[data-toggle="popover"]');
    popoverElements.forEach(el => {
        // Initialize your popover logic or library here
    });
});


