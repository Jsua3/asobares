/*
 * La campana del panel: abrir un aviso lleva a su registro y cierra el panel
 * de avisos.
 *
 * La campana vive en la zona derecha de la barra superior, que Filament
 * conserva entre páginas (`x-persist` en `.fi-topbar-end`). Con la navegación
 * SPA, abrir un aviso cambiaba de página y dejaba el panel de avisos abierto
 * encima, y había que cerrarlo a mano. Al empezar cada navegación se le pide
 * que se cierre con el mismo evento que usan sus propios botones.
 */
document.addEventListener('livewire:navigate', () => {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'database-notifications' } }));
});

/*
 * Y se abre con un clic en cualquier parte del aviso. Filament solo navega
 * desde su botón («Revisar»); un clic en el título o en el texto no hacía
 * nada. El clic en la tarjeta se delega en ese mismo botón, que ya marca el
 * aviso como leído. Los botones y enlaces de la tarjeta conservan su clic.
 */
document.addEventListener('click', (evento) => {
    const tarjeta = evento.target.closest('.fi-no-database .fi-no-notification');

    if (!tarjeta || evento.target.closest('a, button, input, label, [role="button"]')) {
        return;
    }

    tarjeta.querySelector('a[href]')?.click();
});
