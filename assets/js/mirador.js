import Mirador from 'mirador/dist/mirador.min';

export default function waitForManifest() {
    const intervalId = setInterval(() => {
        const miradorElement = document.querySelector('#mirador');

        // Pokud atribut data-manifest není prázdný, inicializujeme Mirador a zastavíme interval
        const manifest = miradorElement ? miradorElement.getAttribute('data-manifest') : '';
        if (manifest && manifest.trim() !== '') {
            clearInterval(intervalId);  // Zastavíme interval, když je manifest k dispozici
            initializeMirador();
        }
    }, 500); // Zkontrolujeme každých 500 ms

    // Po uplynutí určitého času  zkusíme interval zrušit, pokud atribut neexistuje
    setTimeout(() => {
        clearInterval(intervalId);
    }, 3000);
}


function initializeMirador() {
    const miradorElement = document.querySelector('#mirador');
    const config = {
        id: 'mirador', windows: [{
            manifestId: document.getElementById("mirador").getAttribute("data-manifest"),
            thumbnailNavigationPosition: 'far-right',
        }], window: {
            allowClose: false,
            allowMaximize: false,
            allowFullscreen: true,
            allowTopMenuButton: true,
            defaultSideBarPanel: 'info',
            sideBarOpenByDefault: false,
            views: [{key: 'single'}, {key: 'gallery'}]
        }, workspace: {
            showZoomControls: true, type: 'mosaic'
        }, workspaceControlPanel: {
            enabled: false
        }
    };

    const manifest = miradorElement ? miradorElement.getAttribute('data-manifest') : '';
    if (manifest && manifest.trim() !== '') {
        Mirador.viewer(config);
    }

}
