import { ref, onMounted, watch } from 'vue';

export function useTheme() {
    const isDark = ref(true); // Default to dark theme

    // Initialize theme from system preference or stored preference
    const initTheme = () => {
        const stored = localStorage.getItem('theme');
        if (stored) {
            isDark.value = stored === 'dark';
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();

        // Watch for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                isDark.value = e.matches;
            }
        });
    };

    // Watch for changes and apply them
    watch(isDark, () => {
        applyTheme();
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    });

    // Apply theme to document
    const applyTheme = () => {
        if (typeof document !== 'undefined') {
            document.documentElement.classList.toggle('dark', isDark.value);
            document.body.style.backgroundColor = isDark.value ? '#020817' : '#ffffff';
            
            // Update meta theme-color for mobile browsers
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (metaThemeColor) {
                metaThemeColor.setAttribute('content', isDark.value ? '#020817' : '#ffffff');
            }
        }
    };

    // Toggle theme
    const toggleTheme = () => {
        isDark.value = !isDark.value;
    };

    // Get current theme
    const getTheme = () => {
        return isDark.value ? 'dark' : 'light';
    };

    // Initialize if in browser environment
    if (typeof window !== 'undefined') {
        initTheme();
    }

    return {
        isDark,
        toggleTheme,
        getTheme,
        initTheme
    };
}
