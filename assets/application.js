import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import '@contributte/datagrid/assets/datagrid'
import regexValidation from "./js/settings";
import importForm from "./js/import";

require('@contributte/datagrid/assets/datagrid.css');
require('@contributte/datagrid/assets/datagrid-spinners.css');

document.addEventListener("DOMContentLoaded", function (event) {

    regexValidation();
    importForm();

});
