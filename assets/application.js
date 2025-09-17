import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import '@contributte/datagrid/assets/datagrid'
import regexValidation from "./js/settings";
import importForm from "./js/import";
import joinLinks from "./js/specimen";
import {initCopyButtons} from "./js/copyButton";
import mirador from "./js/mirador";
import setSettings from "./js/herbarium";

import '@contributte/datagrid/assets/datagrid.css';
import '@contributte/datagrid/assets/datagrid-spinners.css';

document.addEventListener("DOMContentLoaded", function (event) {

    regexValidation();
    importForm();
    joinLinks();
    initCopyButtons();
    setSettings();

    if (document.querySelector('#mirador')) {
        import('./js/mirador').then((module) => {
            module.default();
        });
    }

});
