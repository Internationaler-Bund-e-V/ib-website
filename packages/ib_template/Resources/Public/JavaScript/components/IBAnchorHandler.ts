/**
 * check for get parameter ibAnchor for scrolling and opening contentelements
 * usage: ?ibAnchor = cXXX or XXX
 * c -> content element id
 * XXX -> accordion element id
 */

class IBAnchorHandler {
    constructor() {
        const ibAnchor:string = this.getUrlParameter('ibAnchor');

        if (ibAnchor == '') {
            return;
        }

        let anchorElement:HTMLElement|null;

        if (!ibAnchor.match("^c")) {
            anchorElement = document.getElementById('ib-collapsible-' + ibAnchor);

            if (anchorElement !== null) {
                $(anchorElement).find('.small-1.ib-collapsible-trigger').trigger('click');
            }
        } else {
            anchorElement = document.getElementById(ibAnchor);
        }

        if (anchorElement === null) {
            return;
        }

        $('html, body').animate({
            scrollTop: anchorElement.offsetTop - 100
        }, 100);
    }

    private getUrlParameter(name:string):string {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        let regex:RegExp = new RegExp('[\\?&]' + name + '=([^&#]*)');
        let results:Array<string>|null = regex.exec(location.search);

        if (results === null) {
            return '';
        }
        return decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
}

export default IBAnchorHandler;
