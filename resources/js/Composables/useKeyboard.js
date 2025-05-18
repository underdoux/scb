import { onMounted, onUnmounted, ref } from 'vue';

export function useKeyboard(options = {}) {
    const focusedElement = ref(null);
    const shortcuts = new Map();
    
    // Focus trap for modals and dropdowns
    const createFocusTrap = (container) => {
        const focusableElements = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        const handleTabKey = (e) => {
            if (e.key !== 'Tab') return;
            
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        };
        
        return {
            activate: () => {
                container.addEventListener('keydown', handleTabKey);
                firstFocusable?.focus();
            },
            deactivate: () => {
                container.removeEventListener('keydown', handleTabKey);
            }
        };
    };

    // Register keyboard shortcuts
    const registerShortcut = (key, callback, meta = false, ctrl = false, shift = false) => {
        const shortcut = `${meta ? 'Meta+' : ''}${ctrl ? 'Ctrl+' : ''}${shift ? 'Shift+' : ''}${key}`;
        shortcuts.set(shortcut, callback);
    };

    // Handle keyboard events
    const handleKeyDown = (e) => {
        const key = e.key.toLowerCase();
        const shortcut = `${e.metaKey ? 'Meta+' : ''}${e.ctrlKey ? 'Ctrl+' : ''}${e.shiftKey ? 'Shift+' : ''}${key}`;
        
        if (shortcuts.has(shortcut)) {
            e.preventDefault();
            shortcuts.get(shortcut)();
        }

        // Handle Escape key for modals
        if (key === 'escape' && options.onEscape) {
            options.onEscape();
        }
    };

    // Focus management
    const setFocus = (element) => {
        if (element) {
            element.focus();
            focusedElement.value = element;
        }
    };

    const restoreFocus = () => {
        if (focusedElement.value) {
            focusedElement.value.focus();
        }
    };

    // Focus indicators
    const handleMouseDown = () => {
        document.body.classList.remove('using-keyboard');
    };

    const handleKeyboardInput = (e) => {
        if (e.key === 'Tab') {
            document.body.classList.add('using-keyboard');
        }
    };

    onMounted(() => {
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('mousedown', handleMouseDown);
        document.addEventListener('keyup', handleKeyboardInput);

        // Add global styles for keyboard focus indicators
        const style = document.createElement('style');
        style.textContent = `
            .using-keyboard :focus {
                outline: 2px solid rgb(37 99 235 / 0.2) !important;
                outline-offset: 2px !important;
            }
            .using-keyboard :focus-visible {
                outline: 2px solid rgb(37 99 235 / 0.2) !important;
                outline-offset: 2px !important;
            }
        `;
        document.head.appendChild(style);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown);
        document.removeEventListener('mousedown', handleMouseDown);
        document.removeEventListener('keyup', handleKeyboardInput);
    });

    return {
        createFocusTrap,
        registerShortcut,
        setFocus,
        restoreFocus,
        focusedElement
    };
}
