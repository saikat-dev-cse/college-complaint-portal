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

// ==========================================
// 60FPS JAVASCRIPT ANIMATION ENGINE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    document.body.style.backgroundSize = "400% 400%";
    
    // Dynamically inject the liquid blobs into the glass cards via JS
    document.querySelectorAll('.glass-card').forEach(card => {
        const blob = document.createElement('div');
        blob.className = 'js-liquid-blob';
        card.insertBefore(blob, card.firstChild);
    });

    let startTime = performance.now();

    function renderEngine(currentTime) {
        const elapsed = currentTime - startTime;
        const isDark = document.body.classList.contains('dark-mode');

        // 1. Smooth Background Panning Loop (15 seconds)
        const bgProgress = (elapsed % 15000) / 15000;
        const bgPos = (Math.sin(bgProgress * Math.PI * 2) + 1) / 2 * 100;
        document.body.style.backgroundPosition = `${bgPos}% 50%`;

        // 2. Logout Button Target: Perfect Dark Blue Pulse (3 seconds loop)
        const logoutProgress = (elapsed % 3000) / 3000;
        const pulse = (Math.sin(logoutProgress * Math.PI * 2) + 1) / 2; // Scales 0.0 to 1.0

        document.querySelectorAll('.logout').forEach(btn => {
            if (!isDark) {
                // Morph from Light Red (239, 68, 68) to Pure Dark Blue (0, 0, 139)
                const r = Math.round(239 - (239 * pulse));
                const g = Math.round(68 - (68 * pulse));
                const b = Math.round(68 + (71 * pulse)); // 68 up to 139
                
                btn.style.backgroundColor = `rgb(${r}, ${g}, ${b})`;
                btn.style.color = 'white';
                btn.style.borderColor = `rgb(${r}, ${g}, ${b})`;
                btn.style.boxShadow = `0 6px 15px rgba(${r}, ${g}, ${b}, 0.4)`;
            } else {
                // Dark Mode subtle glow
                const alpha = 0.1 + (0.2 * pulse);
                btn.style.backgroundColor = `rgba(239, 68, 68, ${alpha})`;
                btn.style.color = '#fca5a5';
                btn.style.borderColor = `rgba(239, 68, 68, 0.3)`;
                btn.style.boxShadow = 'none';
            }
        });

        // 3. 3D Liquid Math Simulation
        document.querySelectorAll('.js-liquid-blob').forEach((blob, index) => {
            if (isDark) {
                blob.style.opacity = '0'; // Hide in dark mode
            } else {
                blob.style.opacity = '1';
                // Offset math for different blobs
                const blobProgress = ((elapsed + index * 2000) % 10000) / 10000;
                const angle = blobProgress * Math.PI * 2;
                
                // Calculate physical morphing coordinates
                const scale = 1 + Math.sin(angle * 2) * 0.15;
                const tx = Math.cos(angle) * 15;
                const ty = Math.sin(angle * 2) * 15;
                const rot = angle * (180 / Math.PI);
                
                blob.style.transform = `translate(${tx}%, ${ty}%) scale(${scale}) rotate(${rot}deg)`;
                
                // Morphing Border Radius (The Liquid effect)
                const br1 = 50 + Math.sin(angle) * 20;
                const br2 = 50 + Math.cos(angle) * 20;
                const br3 = 50 + Math.sin(angle + Math.PI) * 20;
                const br4 = 50 + Math.cos(angle + Math.PI) * 20;
                blob.style.borderRadius = `${br1}% ${br2}% ${br3}% ${br4}%`;
            }
        });

        // Loop the engine at screen refresh rate (60 FPS)
        requestAnimationFrame(renderEngine);
    }

    // Start the Engine
    requestAnimationFrame(renderEngine);
});
