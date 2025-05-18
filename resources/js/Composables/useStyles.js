import { computed } from 'vue';
import { useColors } from './useColors';
import { useTheme } from './useTheme';

export function useStyles() {
    const { colors } = useColors();
    const { isDark } = useTheme();

    // Dynamic class generation
    const generateClasses = (baseClasses, conditionalClasses = {}) => {
        const activeClasses = Object.entries(conditionalClasses)
            .filter(([_, condition]) => condition)
            .map(([className]) => className);

        return [baseClasses, ...activeClasses].filter(Boolean).join(' ');
    };

    // Common style variants
    const variants = {
        size: {
            sm: 'text-sm',
            md: 'text-base',
            lg: 'text-lg',
        },
        rounded: {
            none: 'rounded-none',
            sm: 'rounded-sm',
            md: 'rounded-md',
            lg: 'rounded-lg',
            full: 'rounded-full',
        },
        shadow: {
            none: 'shadow-none',
            sm: 'shadow-sm',
            md: 'shadow-md',
            lg: 'shadow-lg',
            xl: 'shadow-xl',
        }
    };

    // Common style compositions
    const compositions = computed(() => ({
        input: {
            base: 'block w-full rounded-lg border text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
            default: isDark.value
                ? 'border-border-primary bg-background-primary text-white placeholder-gray-400 focus:border-blue-600 focus:ring-blue-500/20 focus:ring-offset-background-primary'
                : 'border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500/20',
            error: 'border-red-500 focus:border-red-500 focus:ring-red-500/20',
            disabled: 'opacity-50 cursor-not-allowed',
        },
        button: {
            base: 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
            primary: isDark.value
                ? 'bg-gradient-to-b from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-600/20 hover:from-blue-600 hover:to-blue-700 focus:ring-blue-500/20 focus:ring-offset-background-primary'
                : 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500/20',
            secondary: isDark.value
                ? 'border border-border-primary bg-background-secondary text-white hover:bg-background-primary focus:ring-blue-500/20 focus:ring-offset-background-primary'
                : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500/20',
            danger: isDark.value
                ? 'bg-gradient-to-b from-red-500 to-red-600 text-white shadow-lg shadow-red-600/20 hover:from-red-600 hover:to-red-700 focus:ring-red-500/20 focus:ring-offset-background-primary'
                : 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/20',
        },
        card: {
            base: 'overflow-hidden rounded-lg transition-shadow duration-200',
            default: isDark.value
                ? 'border border-border-primary bg-background-secondary shadow-lg'
                : 'border border-gray-200 bg-white shadow-sm',
            hover: 'hover:shadow-lg',
        }
    }));

    // Style utilities
    const utils = {
        truncate: 'overflow-hidden text-ellipsis whitespace-nowrap',
        srOnly: 'sr-only',
        container: 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8',
        overlay: isDark.value
            ? 'fixed inset-0 bg-black/50 backdrop-blur-sm'
            : 'fixed inset-0 bg-black/25 backdrop-blur-sm',
    };

    // Generate dynamic styles
    const getStyles = (type, variant = 'default', options = {}) => {
        const composition = compositions.value[type];
        if (!composition) return '';

        return generateClasses(
            `${composition.base} ${composition[variant]}`,
            options
        );
    };

    return {
        generateClasses,
        variants,
        compositions,
        utils,
        getStyles,
    };
}
