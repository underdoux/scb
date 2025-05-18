import { ref, onMounted, onUnmounted } from 'vue';

export function useBreakpoints() {
    const screens = {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px'
    };

    const breakpoints = ref({
        sm: false,
        md: false,
        lg: false,
        xl: false,
        '2xl': false
    });

    const currentBreakpoint = ref('');

    const getBreakpoint = (width) => {
        if (width >= 1536) return '2xl';
        if (width >= 1280) return 'xl';
        if (width >= 1024) return 'lg';
        if (width >= 768) return 'md';
        if (width >= 640) return 'sm';
        return 'xs';
    };

    const updateBreakpoints = () => {
        const width = window.innerWidth;
        breakpoints.value = {
            sm: width >= 640,
            md: width >= 768,
            lg: width >= 1024,
            xl: width >= 1280,
            '2xl': width >= 1536
        };
        currentBreakpoint.value = getBreakpoint(width);
    };

    const isMobile = () => !breakpoints.value.md;
    const isTablet = () => breakpoints.value.md && !breakpoints.value.lg;
    const isDesktop = () => breakpoints.value.lg;
    const isLargeScreen = () => breakpoints.value.xl;

    // Media Query Helpers
    const mediaQueries = {};
    const mediaQueryLists = {};
    const mediaQueryCallbacks = new Map();

    const createMediaQuery = (breakpoint) => {
        const query = `(min-width: ${screens[breakpoint]})`;
        mediaQueries[breakpoint] = query;
        mediaQueryLists[breakpoint] = window.matchMedia(query);
    };

    const onMediaQueryChange = (breakpoint, callback) => {
        const mql = mediaQueryLists[breakpoint];
        if (!mql) {
            createMediaQuery(breakpoint);
        }

        const handler = (e) => callback(e.matches);
        mediaQueryCallbacks.set(callback, handler);
        mediaQueryLists[breakpoint].addEventListener('change', handler);

        // Initial call
        callback(mediaQueryLists[breakpoint].matches);

        // Return cleanup function
        return () => {
            mediaQueryLists[breakpoint].removeEventListener('change', handler);
            mediaQueryCallbacks.delete(callback);
        };
    };

    // Lifecycle hooks
    let resizeObserver;
    onMounted(() => {
        updateBreakpoints();
        window.addEventListener('resize', updateBreakpoints);

        // Initialize media queries
        Object.keys(screens).forEach(createMediaQuery);

        // Optional: Use ResizeObserver for more accurate size tracking
        if (window.ResizeObserver) {
            resizeObserver = new ResizeObserver(updateBreakpoints);
            resizeObserver.observe(document.documentElement);
        }
    });

    onUnmounted(() => {
        window.removeEventListener('resize', updateBreakpoints);
        if (resizeObserver) {
            resizeObserver.disconnect();
        }
        // Cleanup media query listeners
        mediaQueryCallbacks.clear();
    });

    return {
        breakpoints,
        currentBreakpoint,
        screens,
        isMobile,
        isTablet,
        isDesktop,
        isLargeScreen,
        onMediaQueryChange
    };
}
