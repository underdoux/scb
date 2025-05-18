import { ref, computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

export function useLoading(options = {}) {
    const isLoading = ref(false);
    const error = ref(null);
    const data = ref(null);

    const hasError = computed(() => error.value !== null);
    const hasData = computed(() => data.value !== null);
    const isIdle = computed(() => !isLoading.value && !hasError.value && !hasData.value);

    const LoadingSpinner = {
        render() {
            return h(Loader2, {
                class: 'h-5 w-5 animate-spin',
                'aria-hidden': 'true'
            });
        }
    };

    const LoadingState = {
        props: {
            showSpinner: {
                type: Boolean,
                default: true
            },
            text: {
                type: String,
                default: 'Loading...'
            }
        },
        setup(props) {
            return () => h('div', {
                class: 'flex items-center gap-2 text-sm text-gray-400'
            }, [
                props.showSpinner && h(LoadingSpinner),
                props.text && h('span', props.text)
            ]);
        }
    };

    const ErrorState = {
        props: {
            message: {
                type: String,
                default: 'An error occurred'
            }
        },
        setup(props) {
            return () => h('div', {
                class: 'text-sm text-red-500'
            }, props.message);
        }
    };

    const withLoading = async (callback) => {
        try {
            isLoading.value = true;
            error.value = null;
            data.value = await callback();
        } catch (e) {
            error.value = e;
            if (options.onError) {
                options.onError(e);
            }
        } finally {
            isLoading.value = false;
        }

        return data.value;
    };

    const reset = () => {
        isLoading.value = false;
        error.value = null;
        data.value = null;
    };

    return {
        isLoading,
        error,
        data,
        hasError,
        hasData,
        isIdle,
        LoadingSpinner,
        LoadingState,
        ErrorState,
        withLoading,
        reset
    };
}
