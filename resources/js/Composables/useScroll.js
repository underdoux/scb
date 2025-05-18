import { ref, onMounted, onUnmounted } from 'vue';

export function useScroll(options = {}) {
    const scrollY = ref(0);
    const scrollDirection = ref('up');
    const lastScrollY = ref(0);
    const isScrolling = ref(false);
    const scrollTimeout = ref(null);
    const elements = ref(new Map());

    // Scroll direction detection
    const updateScrollDirection = () => {
        const direction = scrollY.value > lastScrollY.value ? 'down' : 'up';
        if (direction !== scrollDirection.value) {
            scrollDirection.value = direction;
        }
        lastScrollY.value = scrollY.value;
    };

    // Scroll position tracking
    const handleScroll = () => {
        scrollY.value = window.scrollY;
        updateScrollDirection();
        
        // Update scrolling state
        isScrolling.value = true;
        if (scrollTimeout.value) {
            clearTimeout(scrollTimeout.value);
        }
        
        scrollTimeout.value = setTimeout(() => {
            isScrolling.value = false;
        }, 150);

        // Update scroll-based animations
        updateScrollAnimations();
    };

    // Scroll-based animations
    const registerScrollAnimation = (element, config) => {
        if (!element) return;

        const id = Math.random().toString(36).substr(2, 9);
        elements.value.set(id, { element, config });

        // Initial check
        checkElementVisibility(id);

        return id;
    };

    const unregisterScrollAnimation = (id) => {
        elements.value.delete(id);
    };

    const checkElementVisibility = (id) => {
        const item = elements.value.get(id);
        if (!item) return;

        const { element, config } = item;
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const threshold = config.threshold || 0.1;

        const isVisible = (
            rect.top <= windowHeight * (1 - threshold) &&
            rect.bottom >= windowHeight * threshold
        );

        if (isVisible) {
            element.classList.add(...(config.visibleClasses || []));
            element.classList.remove(...(config.hiddenClasses || []));
            
            if (config.onVisible) {
                config.onVisible(element);
            }

            if (config.once) {
                unregisterScrollAnimation(id);
            }
        } else if (!config.once) {
            element.classList.remove(...(config.visibleClasses || []));
            element.classList.add(...(config.hiddenClasses || []));
            
            if (config.onHidden) {
                config.onHidden(element);
            }
        }
    };

    const updateScrollAnimations = () => {
        elements.value.forEach((_, id) => checkElementVisibility(id));
    };

    // Smooth scroll helper
    const scrollTo = (target, duration = 500) => {
        const targetPosition = typeof target === 'number' 
            ? target 
            : target.getBoundingClientRect().top + window.scrollY;
        const startPosition = window.scrollY;
        const distance = targetPosition - startPosition;
        let startTime = null;

        const animation = (currentTime) => {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / duration, 1);
            
            // Easing function
            const ease = t => t < 0.5 
                ? 4 * t * t * t 
                : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;

            window.scrollTo(0, startPosition + distance * ease(progress));

            if (timeElapsed < duration) {
                requestAnimationFrame(animation);
            }
        };

        requestAnimationFrame(animation);
    };

    // Lifecycle hooks
    onMounted(() => {
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll(); // Initial check
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll);
        if (scrollTimeout.value) {
            clearTimeout(scrollTimeout.value);
        }
    });

    return {
        scrollY,
        scrollDirection,
        isScrolling,
        registerScrollAnimation,
        unregisterScrollAnimation,
        scrollTo
    };
}
