/**
 * Enterprise DWH Dashboard - Dark Mode Controller
 */

(function () {
    const getStoredTheme = () => localStorage.getItem('theme');
    const setStoredTheme = theme => localStorage.setItem('theme', theme);

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        if (storedTheme) {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = theme => {
        document.documentElement.setAttribute('data-bs-theme', theme);
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (theme === 'dark') {
                icon.className = 'fa-solid fa-sun';
                toggleBtn.setAttribute('title', 'Aktifkan Mode Terang');
            } else {
                icon.className = 'fa-solid fa-moon';
                toggleBtn.setAttribute('title', 'Aktifkan Mode Gelap');
            }
        }
    };

    // Apply preferred theme immediately to avoid styling flash
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);

    window.addEventListener('DOMContentLoaded', () => {
        setTheme(getPreferredTheme());
        
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
                setStoredTheme(newTheme);
                
                // Trigger event for charts or datatables to reload with new theme configurations
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
            });
        }
    });
})();
