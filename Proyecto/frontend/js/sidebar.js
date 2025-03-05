/**
 * Módulo: Gestión de la Barra Lateral (Sidebar)
 * 
 * Este script permite alternar la visibilidad de la barra lateral en la interfaz de usuario,
 * guardando el estado de colapso en `localStorage` para mantener la preferencia del usuario.
 *
 * Ejemplo de llamada:
 * -------------------
 * toggleSidebar(); // Alterna la visibilidad de la barra lateral.
 *
 * Funcionalidades principales:
 * ----------------------------
 * - `toggleSidebar()`: Alterna entre colapsar y expandir la barra lateral.
 * - `DOMContentLoaded (evento)`: Aplica el estado guardado de la barra lateral al cargar la página.
 *
 * Dependencias:
 * -------------
 * - `localStorage` → Se utiliza para almacenar y recuperar el estado de la barra lateral.
 * - HTML con elementos:
 *   - `#sidebar` → Contenedor de la barra lateral.
 *   - `#toggleArrow` → Flecha indicadora del estado de la barra lateral.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. **Alternar barra lateral (`toggleSidebar`)**:
 *    - Agrega o elimina la clase `"collapsed"` en `#sidebar` para cambiar su estado.
 *    - Cambia el texto de `#toggleArrow` para reflejar el estado actual.
 *    - Guarda el estado en `localStorage` (`true` para colapsado, `false` para expandido`).
 * 2. **Aplicar estado guardado (`DOMContentLoaded`)**:
 *    - Al cargar la página, recupera el estado de `localStorage`.
 *    - Aplica el estado (`collapsed` o expandido) a la barra lateral.
 *    - Ajusta la dirección de la flecha en `#toggleArrow` según el estado.
 */

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
