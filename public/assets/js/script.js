// Theme Toggle Logic
function toggleTheme() {
    document.body.classList.toggle("dark-mode");
    let theme = document.body.classList.contains("dark-mode") ? "dark" : "light";
    localStorage.setItem("theme", theme);
    
    let btn = document.getElementById("themeBtn");
    if(btn) { btn.innerText = theme === "dark" ? "☀️" : "🌙"; }
}

window.onload = function() {
    if(localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
        let btn = document.getElementById("themeBtn");
        if(btn) { btn.innerText = "☀️"; }
    }
}

// Hamburger Menu Logic (For Mobile Admin Dashboard)
function toggleSidebar() {
    const sidebar = document.getElementById("mobileSidebar");
    const overlay = document.getElementById("sidebarOverlay");
    
    if (sidebar) sidebar.classList.toggle("open");
    if (overlay) overlay.classList.toggle("active");
}
