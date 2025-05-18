import { ref, computed } from 'vue';

export function useColors() {
    const primaryColor = ref('#2563eb'); // Blue-600

    // Color manipulation helpers
    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };

    const rgbToHex = (r, g, b) => {
        return '#' + [r, g, b].map(x => {
            const hex = x.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
    };

    const adjustBrightness = (hex, percent) => {
        const rgb = hexToRgb(hex);
        if (!rgb) return hex;

        const adjust = (value) => {
            return Math.min(255, Math.max(0, Math.round(value * (1 + percent))));
        };

        return rgbToHex(
            adjust(rgb.r),
            adjust(rgb.g),
            adjust(rgb.b)
        );
    };

    // Generate color variations
    const generateColorPalette = (baseColor) => {
        return {
            50: adjustBrightness(baseColor, 0.95),
            100: adjustBrightness(baseColor, 0.85),
            200: adjustBrightness(baseColor, 0.65),
            300: adjustBrightness(baseColor, 0.45),
            400: adjustBrightness(baseColor, 0.25),
            500: baseColor,
            600: adjustBrightness(baseColor, -0.15),
            700: adjustBrightness(baseColor, -0.25),
            800: adjustBrightness(baseColor, -0.35),
            900: adjustBrightness(baseColor, -0.45),
            950: adjustBrightness(baseColor, -0.55),
        };
    };

    // Theme color getters
    const colors = computed(() => ({
        primary: generateColorPalette(primaryColor.value),
        background: {
            primary: '#020817',
            secondary: '#0B0F1A',
        },
        border: {
            primary: '#1f2937', // gray-800
        },
        text: {
            primary: '#ffffff',
            secondary: '#9ca3af', // gray-400
            muted: '#6b7280', // gray-500
        }
    }));

    // CSS Variable generators
    const generateCssVariables = (colorObj, prefix = '') => {
        return Object.entries(colorObj).reduce((vars, [key, value]) => {
            if (typeof value === 'object') {
                return {
                    ...vars,
                    ...generateCssVariables(value, `${prefix}${key}-`)
                };
            }
            return {
                ...vars,
                [`--${prefix}${key}`]: value
            };
        }, {});
    };

    const cssVariables = computed(() => generateCssVariables(colors.value));

    // Color utility functions
    const getContrastColor = (hex) => {
        const rgb = hexToRgb(hex);
        if (!rgb) return '#ffffff';

        const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
        return brightness > 128 ? '#000000' : '#ffffff';
    };

    const isColorLight = (hex) => {
        return getContrastColor(hex) === '#000000';
    };

    // Dynamic theme generation
    const generateThemeFromColor = (color) => {
        primaryColor.value = color;
        return {
            colors: colors.value,
            cssVariables: cssVariables.value,
            isLight: isColorLight(color)
        };
    };

    return {
        colors,
        cssVariables,
        generateThemeFromColor,
        getContrastColor,
        isColorLight,
        adjustBrightness,
        generateColorPalette
    };
}
