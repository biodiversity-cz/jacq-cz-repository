export default function setSettings() {

        if (document.getElementById("setSettings")) {
            init();
        }

}

function init() {

    document.querySelectorAll(".form-check-input").forEach(function (el) {
        el.addEventListener("change", function () {
            const targetUrl = el.dataset.target;
            const id = el.id;
            const value = el.checked;
            window.location.href = `${targetUrl}&value=${encodeURIComponent(value)}`;
        });
    });
}
