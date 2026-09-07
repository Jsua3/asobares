{{--
    El control de tema del panel (D-L23, 7 sep 2026).

    Deja de ser un segmentado de dos botones y pasa a tener la misma forma que
    el del sitio público: un botón redondo que abre un popover con las TRES
    preferencias. El segmentado no ofrecía «sistema», que hasta hoy solo se
    alcanzaba desde el menú de usuario de Filament, y ese menú se retiró al
    bajar la cuenta al pie de la barra (D-L21).

    Es un «disclosure», no un menú ARIA: botón con aria-expanded más el panel
    que controla. Sin aria-haspopup ni role="menu", que anunciarían navegación
    con flechas que este popover no implementa.

    `syncTopbar()` se queda tal cual: es el único escritor de estado por scroll
    del panel y su guardia lo pinza. Unificarlo con el de la barra lateral es
    otra decisión, y está anotada como fuera de alcance.
--}}
<div
    x-data="{
        abierto: false,
        preferencia: 'system',
        get esOscuro() {
            return this.preferencia === 'dark'
                || (this.preferencia === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        },
        syncTopbar() {
            this.$root
                .closest('.fi-topbar-ctn')
                ?.classList.toggle('asb-topbar--scrolled', Math.max(window.scrollY, 0) > 8);
        },
        pintar() {
            document.documentElement.classList.toggle('dark', this.esOscuro);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.esOscuro ? 'dark' : 'light' }));
        },
        elegir(valor) {
            this.preferencia = ['light', 'dark', 'system'].includes(valor) ? valor : 'system';

            if (this.preferencia === 'system') {
                localStorage.removeItem('theme');
            } else {
                localStorage.setItem('theme', this.preferencia);
            }

            this.pintar();
            this.abierto = false;
            this.$refs.disparador?.focus();
        },
        init() {
            const guardada = localStorage.getItem('theme');

            this.preferencia = guardada === 'dark' || guardada === 'light' ? guardada : 'system';
            this.pintar();
            this.syncTopbar();

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.preferencia === 'system') {
                    this.pintar();
                }
            });
        },
    }"
    x-on:scroll.window.passive="syncTopbar()"
    x-on:keydown.escape.window="abierto = false; $refs.disparador?.focus()"
    x-on:pointerdown.outside="abierto = false"
    class="asb-panel-tema"
>
    <button
        type="button"
        x-ref="disparador"
        x-on:click="abierto = ! abierto"
        x-bind:aria-expanded="abierto ? 'true' : 'false'"
        aria-controls="asb-popover-tema"
        aria-label="Apariencia del panel"
        class="asb-panel-tema__disparador"
    >
        <x-filament::icon icon="heroicon-o-sun" class="asb-panel-tema__icono" x-show="! esOscuro" />
        <x-filament::icon icon="heroicon-o-moon" class="asb-panel-tema__icono" x-cloak x-show="esOscuro" />
    </button>

    <div
        x-cloak
        x-show="abierto"
        x-transition:enter="transicion-desplegable ease-out duration-(--duracion-entrada)"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        id="asb-popover-tema"
        class="asb-panel-tema__popover"
    >
        @foreach ([
            ['light', 'Claro', 'heroicon-o-sun'],
            ['dark', 'Oscuro', 'heroicon-o-moon'],
            ['system', 'El del sistema', 'heroicon-o-computer-desktop'],
        ] as [$valor, $rotulo, $icono])
            <button
                type="button"
                x-on:click="elegir('{{ $valor }}')"
                x-bind:aria-current="preferencia === '{{ $valor }}' ? 'true' : null"
                class="asb-panel-tema__opcion"
            >
                <x-filament::icon :icon="$icono" class="h-5 w-5 shrink-0" />
                <span>{{ $rotulo }}</span>
            </button>
        @endforeach
    </div>
</div>
