export default function importForm() {
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
}
