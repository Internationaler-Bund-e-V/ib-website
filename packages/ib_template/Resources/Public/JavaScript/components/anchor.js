/**
 * check for get parameter ibAnchor for scrolling and opening contentelements
 * usage: ?ibAnchor = cXXX or XXX
 * c -> content element id
 * XXX -> accordion element id
 */

var ibAnchor = getUrlParameter('ibAnchor');

function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

$(window).load(function () {
    if (ibAnchor != '') {
        var anchorElement = $('#' + ibAnchor);
        //check for accordion element
        if (!ibAnchor.match("^c")) {
            anchorElement = $('#ib-collapsible-' + ibAnchor);
            anchorElement.find('.small-1.ib-collapsible-trigger').click();
        }

        $('html, body').animate({
            scrollTop: anchorElement.offset().top - 100
        }, 100);
    }
});