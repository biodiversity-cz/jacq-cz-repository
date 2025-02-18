import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import '@contributte/datagrid/assets/datagrid'
import regexValidation from "./js/settings";

// require('ublaboo-datagrid/assets/datagrid');
// require('ublaboo-datagrid/assets/datagrid-spinners');
// require('ublaboo-datagrid/assets/datagrid-instant-url-refresh');
require('@contributte/datagrid/assets/datagrid.css');
require('@contributte/datagrid/assets/datagrid-spinners.css');

document.addEventListener("DOMContentLoaded", function (event) {

regexValidation();

    // disable button to block multiple resend of request
    if (document.getElementById("importButton")) {
        document.getElementById("importButton").addEventListener("click", function () {
            let button = document.getElementById("importButton");
            button.classList.add("disabled");
            button.onclick = function (event) {
                event.preventDefault();
            }
            document.body.style.cursor = "wait";
        });
    }

    if (document.getElementById("cleanupButton")) {
        document.getElementById("cleanupButton").addEventListener("click", function (event) {
            if (!confirm("Do you really want to delete all files with import error from your institution bucket?")) {
                event.preventDefault();
            }
        });
    }

    //autorefresh page
    const element = document.getElementById('autorefresh');
    if (element) {
        setInterval(function() {
            location.reload();
        }, 30000); // 20 000 ms = 20 sec
    }


});
