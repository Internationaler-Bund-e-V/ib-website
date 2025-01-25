/**
 * handle clicks on accordions for PDB pages and TYPO3 pages
 *
 *
 * replacement of functions in
 * htdocs/typo3conf/ext/ibcontent/Resources/Public/js/db.js
 *
 * @see /typo3conf/ext/ibcontent/Resources/Private/Templates/MyContent/Accordion.html
 *
 */
$(document).ready(function () {

    $('.ib-collapsible-trigger').on('click', function () {
        var collapsibleID = $(this).data('ibcollapsibleid');
        var content = $("#ib-collapsible-content-" + collapsibleID + " iframe");

        $("#ib-collapsible-content-" + collapsibleID).slideToggle(300);
        $("#ib-collapsible-" + collapsibleID + " i").toggleClass('ib-icon-arrow-right ib-icon-arrow-down');

        if (content.data('loaded') != 'true') {
            var flexContainer = $("#ib-collapsible-content-" + collapsibleID + " .flex-video");
            var src = content.attr("src");

            content.on('load', function () {
                flexContainer.css('visibility', 'visible');
            });
            content.attr("src", src);
            content.data('loaded', 'true');
        }
        /**
         * resize/reint slick slider/masonry if present
         */
        var tmpSlickContainer = $("#ib-collapsible-" + collapsibleID + " .ext-ib-slider-main-stage-mobile");
        tmpSlickContainer.slick('unslick').slick({
            dots: true,
            slidesToShow: 1,
            arrows: false
        });
        tmpSlickContainer.data('slickInitialized', 'open');
        $("#ib-collapsible-" + collapsibleID + " .grid").masonry('layout');

        /**
         * reint masonry layout/slickslider due to hidden state in collapsible
         */
        $("#ib-collapsible-content-" + collapsibleID + " .grid").masonry('layout');
        $('.ext-ib-slider-main-stage-mobile').slick('unslick').slick({
            dots: true,
            slidesToShow: 1,
            arrows: false
        });

    });

    if ($(location).attr('hash') != "") {
        var anchor = $(location).attr('hash');
        anchor = anchor.replace(/ /g, '');
        $(anchor).click();
        $("html, body").animate({
            scrollTop: $(anchor).offset().top - 100
        }, 1000);
    }
});