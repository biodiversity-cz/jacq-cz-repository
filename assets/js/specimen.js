export default async function joinLinks() {
    // makeJACQLinks();
    // makeGbifLink();
}



async function makeJACQLinks() {

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
            console.error("makeJACQLinks: JSON parse error:", error);
            return;
        }

        const data = await response.json().catch(err => {
            console.error("makeJACQLinks: JSON parse error:", err);
            return null;
        });

        if (!data) return;

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
        console.error("makeJACQLinks: unexpected error:", error);
    }

}

async function makeGbifLink() {

    const gbifElement = document.getElementById("GBIF");
    const gbifInfoElement = document.getElementById("copyGbif");
    if (!gbifElement) {
        return;
    }

    const apiUrl = gbifElement.getAttribute("data-url");
    if (!apiUrl) {
        return;
    }

    try {
        const response = await fetch(apiUrl, {
            headers: {"Accept": "application/json"}
        });

        if (!response.ok) {
            console.error("makeJACQLinks: JSON parse error:", error);
            return;
        }

        const data = await response.json().catch(err => {
            console.error("makeJACQLinks: JSON parse error:", err);
            return null;
        });

        if (!data) return;

        if (Array.isArray(data.results) && data.results.length > 0) {
            let firstKey = data.results[0].key;            gbifElement.href = `https://gbif.org/occurrence/${firstKey}`;
            gbifInfoElement.textContent = `https://gbif.org/occurrence/${firstKey}`;
          }

    } catch (error) {
        console.error("makeGbifLinks: unexpected error:", error);
    }

}
