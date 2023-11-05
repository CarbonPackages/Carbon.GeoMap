const DOCUMENT = document;

/*jshint -W030 */
const globalSettings = JSON.parse(
    DOCUMENT.currentScript.dataset?.settings || null,
);
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
                element,
                html: element.outerHTML,
                popup: !!element.innerHTML,
                lat: coordinate.lat,
                lng: coordinate.lng,
            };
        })
        .filter((element) => element !== null);
}

function initFrontend({ className, initFunction }) {
    [...DOCUMENT.querySelectorAll(`.carbon-geomap.${className}`)].forEach(
        initFunction,
    );
}

export {
    globalSettings,
    iconSettings,
    getMapCanvas,
    getAddresses,
    initFrontend,
};
