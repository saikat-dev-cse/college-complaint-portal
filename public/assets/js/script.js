function toggleTheme() {
    document.body.classList.toggle("dark-mode");
    let theme = document.body.classList.contains("dark-mode") ? "dark" : "light";
    localStorage.setItem("theme", theme);
    
    let btn = document.getElementById("themeBtn");
    if(btn) {
        btn.innerText = theme === "dark" ? "☀️" : "🌙";
    }
}

window.onload = function() {
    if(localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
        let btn = document.getElementById("themeBtn");
        if(btn) { btn.innerText = "☀️"; }
    }
}
