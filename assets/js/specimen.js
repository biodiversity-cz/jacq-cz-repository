export default async function makeJACQLinks() {

    const pidElement = document.getElementById("PID");
    if (!pidElement) {
        return;
    }

    const apiUrl = pidElement.getAttribute("data-url");
    if (!apiUrl) {
        return;
    }

    try {
        const response = await fetch(apiUrl, {
            headers: {"Accept": "application/json"}
        });

        if (!response.ok) {
            throw new Error(`Error: ${response.status}`);
        }

        const data = await response.json();

        if (!data.specimenID) {
            console.error("missing key 'specimenID'.");
            return;
        }

        document.querySelectorAll(".specimenDbId").forEach(el => {
            el.textContent = data.specimenID;
        });

        const manifestLinkElement = document.getElementById("manifestUrl");
        const miradorElement = document.getElementById("mirador");
        if (!manifestLinkElement) {
            return;
        }
        const apiUrlManifest = manifestLinkElement.getAttribute("data-url");

        if (apiUrlManifest) {
            manifestLinkElement.href = `${apiUrlManifest}${data.specimenID}`;
            miradorElement.setAttribute('data-manifest', `${apiUrlManifest}${data.specimenID}`);
        }

    } catch (error) {
    }

}
