import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import '@contributte/datagrid/assets/datagrid'
import regexValidation from "./js/settings";
import importForm from "./js/import";
import makeJACQLinks from "./js/specimen";
import {initCopyButtons} from "./js/copyButton";
import mirador from "./js/mirador";

require('@contributte/datagrid/assets/datagrid.css');
require('@contributte/datagrid/assets/datagrid-spinners.css');

document.addEventListener("DOMContentLoaded", function (event) {

    regexValidation();
    importForm();
    makeJACQLinks();
    initCopyButtons();

    if (document.querySelector('#mirador')) {
        import('./js/mirador').then((module) => {
            module.default();
        });
    }

});
