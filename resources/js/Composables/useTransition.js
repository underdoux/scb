import { ref, onMounted, onUnmounted } from 'vue';

export function useTransition(options = {}) {
    const defaults = {
        duration: 200,
        enterFrom: 'opacity-0',
        enterTo: 'opacity-100',
        leaveFrom: 'opacity-100',
        leaveTo: 'opacity-0',
        timing: 'ease-out'
    };

    const config = { ...defaults, ...options };

    // Common transition classes
    const transitions = {
        fade: {
            enter: 'transition-opacity',
            enterFrom: 'opacity-0',
            enterTo: 'opacity-100',
            leave: 'transition-opacity',
            leaveFrom: 'opacity-100',
            leaveTo: 'opacity-0',
        },
        slideDown: {
            enter: 'transition-all transform',
            enterFrom: 'opacity-0 -translate-y-4',
            enterTo: 'opacity-100 translate-y-0',
            leave: 'transition-all transform',
            leaveFrom: 'opacity-100 translate-y-0',
            leaveTo: 'opacity-0 -translate-y-4',
        },
        slideUp: {
            enter: 'transition-all transform',
            enterFrom: 'opacity-0 translate-y-4',
            enterTo: 'opacity-100 translate-y-0',
            leave: 'transition-all transform',
            leaveFrom: 'opacity-100 translate-y-0',
            leaveTo: 'opacity-0 translate-y-4',
        },
        scale: {
            enter: 'transition-all transform',
            enterFrom: 'opacity-0 scale-95',
            enterTo: 'opacity-100 scale-100',
            leave: 'transition-all transform',
            leaveFrom: 'opacity-100 scale-100',
            leaveTo: 'opacity-0 scale-95',
        },
        slideRight: {
            enter: 'transition-all transform',
            enterFrom: 'opacity-0 -translate-x-4',
            enterTo: 'opacity-100 translate-x-0',
            leave: 'transition-all transform',
            leaveFrom: 'opacity-100 translate-x-0',
            leaveTo: 'opacity-0 -translate-x-4',
        },
        slideLeft: {
            enter: 'transition-all transform',
            enterFrom: 'opacity-0 translate-x-4',
            enterTo: 'opacity-100 translate-x-0',
            leave: 'transition-all transform',
            leaveFrom: 'opacity-100 translate-x-0',
            leaveTo: 'opacity-0 translate-x-4',
        }
    };

    // Intersection Observer for entrance animations
    const isVisible = ref(false);
    let observer = null;

    const onIntersect = (entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                if (options.once && observer) {
                    observer.disconnect();
                }
            } else if (!options.once) {
                isVisible.value = false;
            }
        });
    };

    const observe = (element) => {
        if (!element) return;
        
        observer = new IntersectionObserver(onIntersect, {
            root: null,
            threshold: options.threshold || 0.1,
            rootMargin: options.rootMargin || '0px'
        });
        
        observer.observe(element);
    };

    onUnmounted(() => {
        if (observer) {
            observer.disconnect();
        }
    });

    // Helper functions
    const getTransitionClasses = (type = 'fade') => {
        return transitions[type] || transitions.fade;
    };

    const getTransitionStyle = (duration = config.duration) => {
        return {
            transitionProperty: 'all',
            transitionDuration: `${duration}ms`,
            transitionTimingFunction: config.timing
        };
    };

    return {
        isVisible,
        observe,
        transitions,
        getTransitionClasses,
        getTransitionStyle
    };
}
