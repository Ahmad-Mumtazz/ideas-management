import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/**
 * Light/dark theme.
 *
 * The choice is persisted in localStorage and re-applied by the inline script
 * in the layout <head>, which runs before first paint so there is no flash of
 * the wrong theme. This store keeps the toggle in sync afterwards.
 */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);

        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) {
            // Private browsing can block storage; the toggle still works for
            // this page view, it just will not be remembered.
        }
    },
});

window.Alpine = Alpine;
Alpine.start();
