function toggleSidebar() {
    let sidebar = document.getElementById("sidebar");
    let toggleArrow = document.getElementById("toggleArrow");

    // Alternar la clase "collapsed"
    sidebar.classList.toggle("collapsed");

    // Cambiar la dirección de la flecha
    if (sidebar.classList.contains("collapsed")) {
        toggleArrow.textContent = "→"; // Flecha apuntando a la derecha cuando la barra está cerrada
    } else {
        toggleArrow.textContent = "←"; // Flecha apuntando a la izquierda cuando la barra está abierta
    }

    // Guardar el estado en localStorage
    let isCollapsed = sidebar.classList.contains("collapsed");
    localStorage.setItem("sidebarCollapsed", isCollapsed);
}

// Aplicar el estado guardado en localStorage al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    let sidebar = document.getElementById("sidebar");
    let toggleArrow = document.getElementById("toggleArrow");
    let isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

    if (isCollapsed) {
        sidebar.classList.add("collapsed");
        toggleArrow.textContent = "→";
    } else {
        toggleArrow.textContent = "←";
    }
});
