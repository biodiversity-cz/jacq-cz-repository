export default function setSettings() {

        if (document.getElementById("setSettings")) {
            init();
        }

}

function init() {

    document.querySelectorAll(".form-check-input").forEach(function (el) {
        el.addEventListener("change", function () {
            const targetUrl = el.dataset.target;
            const value = el.checked;
            window.location.href = `${targetUrl}&value=${encodeURIComponent(value)}`;
        });
    });

    document.querySelectorAll(".settings").forEach(el => {
        el.addEventListener("change", e => {
            const targetUrl = e.target.dataset.target;
            const value = e.target.value;
            window.location.href = `${targetUrl}&value=${encodeURIComponent(value)}`;
        });
    });
}
