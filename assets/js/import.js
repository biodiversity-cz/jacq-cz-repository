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


    // //autorefresh page
    // const element = document.getElementById('autorefresh');
    // if (element) {
    //     setInterval(function() {
    //         location.reload();
    //     }, 30000); // 20 000 ms = 20 sec
    // }
}
