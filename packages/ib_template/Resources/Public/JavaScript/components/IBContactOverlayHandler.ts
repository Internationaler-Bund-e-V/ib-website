/**
 * IB Contact Overlay Handler
 * 
 * Opens the contact ocerlay on event click on trigger elements
 * 
 * @author Martin Jahn <martin.jahn@ib.de>
 * @class IBContactOverlayHandler
 */
class IBContactOverlayHandler {
    constructor() {
        const triggerElements = document.querySelectorAll('.toggleContactOverlay');
        triggerElements.forEach((triggerElement:Element) => {
            triggerElement.addEventListener('click', () => {
                $(document.getElementById('dbContactOverlay')!).toggle();
            });
        });
    }
}

export default IBContactOverlayHandler;