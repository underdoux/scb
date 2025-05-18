import { useToast as useVueToast } from 'vue-toastification';
import Toast from '@/Components/Toast.vue';
import { h } from 'vue';

export function useToast() {
    const toast = useVueToast();

    const show = (title, message = '', type = 'default') => {
        toast(
            h(Toast, {
                title,
                message,
                type,
            })
        );
    };

    const success = (title, message = '') => {
        show(title, message, 'success');
    };

    const error = (title, message = '') => {
        show(title, message, 'error');
    };

    const warning = (title, message = '') => {
        show(title, message, 'warning');
    };

    const info = (title, message = '') => {
        show(title, message, 'info');
    };

    return {
        show,
        success,
        error,
        warning,
        info,
    };
}
