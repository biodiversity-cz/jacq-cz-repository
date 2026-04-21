import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import regexValidation from "./js/settings";
import importForm from "./js/import";
import joinLinks from "./js/specimen";
import {initCopyButtons} from "./js/copyButton";
import setSettings from "./js/herbarium";
import '@contributte/datagrid/dist/datagrid-full.css';
import '@contributte/datagrid/dist/datagrid-full.js';
import drawBoxplot from "./js/stats";
import Dropdown from 'bootstrap/js/dist/dropdown'; //has to be present to keep dropdowns - probably datagrid is breaking it..
import "@andypf/json-viewer"
import swagger from "./js/swagger"; //keep the import toi run the JSON viewer



document.addEventListener("DOMContentLoaded", function (event) {

    regexValidation();
    importForm();
    joinLinks();
    initCopyButtons();
    setSettings();
    drawBoxplot();
    swagger();
    if (document.querySelector('#mirador')) {
        import('./js/mirador').then((module) => {
            module.default();
        });
    }

});
