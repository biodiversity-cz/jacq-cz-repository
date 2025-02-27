export function initCopyButtons() {
    document.querySelectorAll(".copyIcon").forEach(icon => {
        icon.addEventListener("click", async () => {
            const targetSelector = icon.getAttribute("data-copy-target");
            const targetElement = document.querySelector(targetSelector);

            if (!targetElement) {
                return;
            }

            const textToCopy = targetElement.textContent.trim();
            if (!textToCopy) {
                return;
            }

            try {
                await navigator.clipboard.writeText(textToCopy);
                icon.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => {
                    icon.innerHTML = '<i class="fa-regular fa-copy"></i>';
                }, 1200);
            } catch (err) {
            }
        });
    });
}
