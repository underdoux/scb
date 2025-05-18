import { ref, computed } from 'vue';
import { useToast } from '@/Composables/useToast';

export function useForm(initialData = {}, options = {}) {
    const toast = useToast();
    const form = ref({ ...initialData });
    const errors = ref({});
    const processing = ref(false);
    const wasSuccessful = ref(false);
    const recentlySuccessful = ref(false);
    let recentlySuccessfulTimeoutId = null;

    const data = computed(() => Object.keys(form.value).reduce((carry, key) => {
        carry[key] = form.value[key];
        return carry;
    }, {}));

    const hasErrors = computed(() => Object.keys(errors.value).length > 0);

    const reset = () => {
        form.value = { ...initialData };
        errors.value = {};
        processing.value = false;
        wasSuccessful.value = false;
        recentlySuccessful.value = false;
    };

    const setError = (key, value) => {
        errors.value[key] = value;
    };

    const clearErrors = (...keys) => {
        if (keys.length === 0) {
            errors.value = {};
        } else {
            keys.forEach(key => delete errors.value[key]);
        }
    };

    const onSuccess = () => {
        wasSuccessful.value = true;
        recentlySuccessful.value = true;

        if (recentlySuccessfulTimeoutId) {
            clearTimeout(recentlySuccessfulTimeoutId);
        }

        recentlySuccessfulTimeoutId = setTimeout(() => {
            recentlySuccessful.value = false;
        }, 2000);
    };

    const submit = async (method, url, config = {}) => {
        processing.value = true;
        clearErrors();

        try {
            const response = await axios[method](url, data.value, config);
            
            if (options.resetOnSuccess) {
                reset();
            }

            onSuccess();

            if (options.showSuccessToast) {
                toast.success(
                    options.successMessage || 'Success',
                    options.successDescription || 'Operation completed successfully'
                );
            }

            return response.data;
        } catch (error) {
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
                
                if (options.showErrorToast) {
                    const firstError = Object.values(error.response.data.errors)[0][0];
                    toast.error('Error', firstError);
                }
            } else {
                if (options.showErrorToast) {
                    toast.error(
                        'Error',
                        error.response?.data?.message || 'An unexpected error occurred'
                    );
                }
            }

            throw error;
        } finally {
            processing.value = false;
        }
    };

    return {
        form,
        errors,
        processing,
        wasSuccessful,
        recentlySuccessful,
        hasErrors,
        data,
        reset,
        setError,
        clearErrors,
        submit,
        post: (url, config) => submit('post', url, config),
        put: (url, config) => submit('put', url, config),
        patch: (url, config) => submit('patch', url, config),
        delete: (url, config) => submit('delete', url, config),
    };
}
