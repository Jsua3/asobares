<div
    x-data="{
        theme: 'light',
        syncTopbar() {
            this.$root
                .closest('.fi-topbar-ctn')
                ?.classList.toggle('asb-topbar--scrolled', Math.max(window.scrollY, 0) > 8);
        },
        setTheme(value) {
            this.theme = value === 'dark' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.theme }));
        },
        init() {
            const savedTheme = localStorage.getItem('theme');

            this.setTheme(savedTheme === 'dark' ? 'dark' : 'light');
            this.syncTopbar();
        },
    }"
    x-on:scroll.window.passive="syncTopbar()"
    class="asb-panel-theme-switcher"
    role="group"
    aria-label="Cambiar apariencia del panel"
>
    <button
        type="button"
        class="asb-panel-theme-switcher__button"
        x-bind:class="{ 'is-active': theme === 'light' }"
        x-on:click="setTheme('light')"
        title="Modo claro"
        aria-label="Activar modo claro"
    >
        <x-filament::icon icon="heroicon-o-sun" class="asb-panel-theme-switcher__icon" />
    </button>

    <button
        type="button"
        class="asb-panel-theme-switcher__button"
        x-bind:class="{ 'is-active': theme === 'dark' }"
        x-on:click="setTheme('dark')"
        title="Modo oscuro"
        aria-label="Activar modo oscuro"
    >
        <x-filament::icon icon="heroicon-o-moon" class="asb-panel-theme-switcher__icon" />
    </button>
</div>
