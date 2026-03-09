// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved preference
    const darkMode = localStorage.getItem('darkMode') === 'enabled';
    
    // Apply saved mode
    if (darkMode) {
        document.body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    // Create toggle button
    createDarkModeToggle();
});

function createDarkModeToggle() {
    const navbar = document.querySelector('.navbar .container');
    if (!navbar) return;
    
    const toggle = document.createElement('button');
    toggle.id = 'darkModeToggle';
    toggle.innerHTML = '🌙';
    toggle.className = 'dark-mode-btn';
    toggle.setAttribute('aria-label', 'Toggle dark mode');
    
    toggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        
        // Save preference
        localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        
        // Update icon
        updateDarkModeIcon(isDark);
    });
    
    navbar.appendChild(toggle);
}

function updateDarkModeIcon(isDark) {
    const toggle = document.getElementById('darkModeToggle');
    if (toggle) {
        toggle.innerHTML = isDark ? '☀️' : '🌙';
    }
}