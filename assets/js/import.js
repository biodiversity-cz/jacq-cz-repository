import {Tab} from 'bootstrap'

export default function importForm() {

    if (document.getElementById("cleanupButton")) {
        document.getElementById("cleanupButton").addEventListener("click", function (event) {
            if (!confirm("Do you really want to delete all files with import error from your institution bucket?")) {
                event.preventDefault();
            }
        });
    }

    if (document.getElementById("importTabs")) {

        const triggerTabList = document.querySelectorAll('#importTabs button')
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new Tab(triggerEl)

            triggerEl.addEventListener('click', event => {
                event.preventDefault()
                tabTrigger.show()
            })
        })

        let hash = window.location.hash;
        if (hash) {
            let tab = document.querySelector('#importTabs button[data-bs-target="' + hash + '"]');
            if (tab) {
                Tab.getInstance(tab).show()
            }
        }
    }

    document.querySelectorAll(".deleteButton").forEach(button => {
        button.addEventListener('click', function (event) {
            if (!confirm("Do you really want to delete this file? It won't be allowed in the production settings.")) {
                event.preventDefault();
            }
        });
    });

    if (document.getElementById("importRevision")) {

        function validateSpecimen(value) {
            // pouze obyčejné mezery
            if (/[\t\n\r\f\v\u00A0]/.test(value)) {
                return 'Use standard space.';
            }

            // mezera na začátku nebo konci
            if (value !== value.trim()) {
                return 'Space on the begginning/end is not allowed.';
            }

            // více mezer za sebou
            if (/  +/.test(value)) {
                return 'Group of multiple spaces id not allowed.';
            }

            // mezera mezi číslicemi
            if (/\d \d/.test(value)) {
                return 'Space between digits is not allowed';
            }

            return null;
        }

         const submit = document.querySelector('input[type="submit"]');
        const input = document.querySelector('input[name="specimen"]');
        const error = document.getElementById('helpInline');

        function updateValidation() {
            const msg = validateSpecimen(input.value);

            if (msg) {
                error.textContent = msg;
                submit.disabled = true;
            } else {
                error.textContent = '';
                submit.disabled = false;
            }
        }

        input.addEventListener('input', updateValidation);

        // zvalidovat i výchozí hodnotu po načtení stránky
        updateValidation();

    }
}
