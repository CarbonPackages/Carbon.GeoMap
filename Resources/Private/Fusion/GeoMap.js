const DOCUMENT = document;

/*jshint -W030 */
const globalSettings = JSON.parse(DOCUMENT.currentScript.dataset?.settings || null);
/*jshint +W030 */

const iconSettings = {
    markup: `<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 149 178"><path fill="${globalSettings.pinColor}" stroke="#FFF" stroke-width="6" stroke-miterlimit="10" d="M126 23l-6-6A69 69 0 0 0 74 1a69 69 0 0 0-51 22A70 70 0 0 0 1 74c0 21 7 38 22 52l43 47c6 6 11 6 16 0l48-51c12-13 18-29 18-48 0-20-8-37-22-51z"/><circle fill="${globalSettings.pinColor}" cx="74" cy="75" r="61"/><circle fill="#FFF" cx="74" cy="75" r="30"/></svg>`,
    anchor: [12, 32],
    size: [25, 30],
    popupAnchor: [0, -28],
};

function getMapCanvas(element) {
    return element.querySelector(".carbon-geomap__canvas");
}

function getAddresses(canvas) {
    return [...canvas.querySelectorAll(".carbon-geomap-coordinate")]
        .map((element) => {
            const coordinate = JSON.parse(element.dataset?.coordinate || null);
            if (!coordinate) {
                return null;
            }
            return {
                html: element.outerHTML,
                lat: coordinate.lat,
                lng: coordinate.lng,
            };
        })
        .filter((element) => element !== null);
}

function getEditor(element, key) {
    return element.querySelector(`.carbon-geomap-coordinatestable__${key} .neos-inline-editable`)?.firstElementChild;
}

function getLatLngEditors(element) {
    const lat = getEditor(element, "lat");
    const lng = getEditor(element, "lng");

    return lat && lng ? { lat, lng } : null;
}

function updateEditor(editor, value) {
    editor.innerText = value.toString();
}

function updateLatLngEditors(editors, values) {
    updateEditor(editors.lat, values.lat);
    updateEditor(editors.lng, values.lng);
}

function initFrontend({ className, initFunction }) {
    [...DOCUMENT.querySelectorAll(`.carbon-geomap.${className}`)].forEach((element) =>
        initFunction({ element, live: true })
    );
}

function initBackend({ className, initFunction, nodeType }) {
    if (typeof className !== "string" || typeof initFunction !== "function" || typeof nodeType !== "string") {
        console.error(
            "Invalid backend edit initialization: You need to set className, initFunction and nodeType correctly",
            { className, initFunction, nodeType }
        );
        return;
    }

    const liveClassName = `.carbon-geomap--live.${className}`;
    const editClassName = `.carbon-geomap--edit.${className}`;
    const liveNodes = [...DOCUMENT.querySelectorAll(liveClassName)];
    const editContainerNodes = liveNodes.map((element) => element.nextElementSibling);

    // Init the live maps
    liveNodes.forEach((element) => initFunction({ element, live: true }));

    // Observer if something inside the edit view changes
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            const element = mutation.addedNodes[0];
            if (element && element.matches(editClassName)) {
                initFunction({ element, live: false });
                break;
            }
        }
    });

    // Re-initialize the edit map if something inside the edit view changes
    editContainerNodes.forEach((element) => observer.observe(element, { childList: true }));

    // If the user clicks to edit, init the edit map and remove the live map
    DOCUMENT.addEventListener("carbonCBD", (event) => {
        const { type, mode, element } = event.detail;

        if (!type === nodeType || mode === "live") {
            return;
        }

        // Wait for the DOM to be ready
        setTimeout(() => {
            // Initialize the edit map
            [...element.querySelectorAll(editClassName)].forEach((element) => initFunction({ element, live: false }));
        }, 10);
    });
}

export {
    globalSettings,
    iconSettings,
    getMapCanvas,
    getAddresses,
    getLatLngEditors,
    updateLatLngEditors,
    initBackend,
    initFrontend,
};
