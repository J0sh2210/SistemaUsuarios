// ===== LOGIN CON CONTRASENA =====
const formulario = document.getElementById("login-form-conventional");

formulario.addEventListener("submit", function (event) {
    event.preventDefault();
    const correo = document.getElementById("correo").value;
    const contrasena = document.getElementById("contrasena").value;

    fetch("../api/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo: correo, contrasena: contrasena }),
        credentials: "include"
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = "dashboard.php";
        } else {
            mostrarMensaje(data.message, "error");
        }
    })
    .catch(() => {
        mostrarMensaje("Error de conexion", "error");
    });
});

// ===== LOGIN CON TOKEN =====
const formulariotoken = document.getElementById("login-form-alternative");

formulariotoken.addEventListener("submit", function (event) {
    event.preventDefault();
    const correotoken = document.getElementById("correo-alt").value;

    fetch("../api/auth/solicitar_token.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo: correotoken }),
        credentials: "include"
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarMensaje("Revisa tu correo electronico para iniciar sesion", "success");
        } else {
            mostrarMensaje(data.message, "error");
        }
    })
    .catch(() => {
        mostrarMensaje("Error de conexion", "error");
    });
});

// ===== MENSAJE =====
function mostrarMensaje(texto, tipo) {
    const div = document.querySelector(".message");
    div.textContent = texto;
    div.className = "message " + tipo;
    setTimeout(() => { div.className = "message"; }, 4000);
}
