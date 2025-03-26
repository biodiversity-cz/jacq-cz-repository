import Mirador from 'mirador/dist/mirador.min';

export default function waitForManifest() {
    let initialized = false;
    let miradorElement = document.querySelector('#mirador');

    let intervalId = setInterval(() => {
        // Pokud atribut data-manifest není prázdný, inicializujeme Mirador a zastavíme interval
        let manifest = miradorElement ? miradorElement.getAttribute('data-manifest') : '';
        if (manifest && manifest.trim() !== '') {
            clearInterval(intervalId);  // Zastavíme interval, když je manifest k dispozici
            initializeMirador(manifest);
            initialized = true;
        }
    }, 500); // Zkontrolujeme každých 500 ms

    // Po uplynutí určitého času  zkusíme interval zrušit, pokud atribut neexistuje
    setTimeout(() => {
        clearInterval(intervalId);
        if (!initialized) {
            let manifest = miradorElement ? miradorElement.getAttribute('data-manifest-raw') : '';
            if (manifest && manifest.trim() !== '') {
                initializeMirador(manifest);
            }
        }
    }, 2000);
}


function initializeMirador(manifestUrl) {
    const config = {
        id: 'mirador', windows: [{
            manifestId: manifestUrl,
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

    Mirador.viewer(config);

}
