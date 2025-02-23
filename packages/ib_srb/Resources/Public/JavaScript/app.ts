'use strict'

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/foundation.scss';
import '../Css/app.scss';

import $ from 'jquery';
(window as any).jQuery = $;
(window as any).$ = $;
import Foundation from 'foundation-sites';
import Shariff from 'shariff';
import 'slick-carousel';
import 'select2';
import lightbox from 'lightbox2'

/* Libs
node_modules/foundation-sites/dist/js/foundation.min.js
node_modules/slick-carousel/slick/slick.min.js
node_modules/shariff/dist/shariff.min.js
dev/js/customCookiebot.js
dev/js/lightbox.min.js
dev/js/obfuscateEmail.js
*/

if (Foundation) {
    // if `Foundation` is left as an unused variable webpack will exclude it from the build output;
    // therefore, any expression that uses it will work.
}

$(() => {
    $(document).foundation();

    if (document.querySelector('.shariff')) {
        new Shariff($('.shariff'));
    }

    //slickslider
    $('.SrbSlider').slick({
        dots: true,
        prevArrow: $('.SrbSliderNavigationArrowsPrev'),
        nextArrow: $('.SrbSliderNavigationArrowsNext'),
        appendDots: $('.SrbSliderNavigationDots')
    });

    //slickslider news
    $(".SrbNewsListSlider").each(function (index) {
        var newssliderid = $(this).attr('id');
        var slider = $('#' + newssliderid + ' .SrbNewsSlider');
        $(slider).slick({
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            dots: true,
            appendDots: $('#' + newssliderid + ' .SrbNewsSliderNavigationDots'),
            prevArrow: $('#' + newssliderid + ' .SrbNewsSliderNavigationArrowsPrev'),
            nextArrow: $('#' + newssliderid + ' .SrbNewsSliderNavigationArrowsNext'),
            responsive: [
                {
                    breakpoint: 769,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 640,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
    });

    //lightbox options
    lightbox.option({
        'albumLabel': "Bild %1 von %2"
    });


    //accordion dataprivacy
    $('.ib-collapsible-trigger').on('click', function () {
        var collapsibleID = $(this).data('ibcollapsibleid');
        $("#ib-collapsible-content-" + collapsibleID).slideToggle(300);
        $("#ib-collapsible-" + collapsibleID + " i").toggleClass('fa-chevron-right fa-chevron-down');

    });

})
