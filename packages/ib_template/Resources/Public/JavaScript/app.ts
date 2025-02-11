'use strict'

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/foundation.scss';
import '../Css/app.scss';
import './config/readSpeakerConfig';

import $ from 'jquery';
(window as any).jQuery = $;
(window as any).$ = $;
import 'friendly-challenge/widget';
import Foundation from 'foundation-sites';
import Headroom from 'headroom.js';
import Shariff from 'shariff';
import 'slick-carousel';
require('select2');

import IBAnchorHandler from './components/IBAnchorHandler';
import IBContactOverlayHandler from './components/IBContactOverlayHandler';
import IBCookieBot from './components/IBCookieBot';
//import IBMenu from './components/IBMenu';
import IBClipboard from './components/IBClipboard';
import IBStartPageSlider from './components/IBStartPageSlider';

import './components/matomoEvents';

require('./components/jetmenu.js');
require('./components/fab.js');
require('./components/foundation-accordion.js')
require('./components/headerSearchBar.js')
require('./components/nav-tab-detection.js')
require('./components/newsCategory.js')
require('./components/newsSlider.js')
require('./components/obfuscateEmail.js')
require('./components/sliderLoader.js')
require('./components/social-content-sticky-nav.js')
require('./components/tabsBar.js')
require('./components/videoSlider.js')

if (Foundation) {
  // if `Foundation` is left as an unused variable webpack will exclude it from the build output;
  // therefore, any expression that uses it will work.
}

$(() => {
    $(document).foundation();

    const headroom = new Headroom(document.body, {
        'offset': 100,
        'tolerance': 3,
        'classes': {
            'initial': 'headroom',
            'pinned': 'headroom--expanded',
            'unpinned': 'headroom--collapsed'
        }
    });
    headroom.init();

    $('.ib-content-module .download img, .ib-content-module .internal-link img, .ib-content-module .internal-link-new-window img, .ib-content-module .external-link img, .ib-content-module .external-link-new-window img').parent()
      .removeClass('download internal-link internal-link-new-window external-link external-link-new-window');


    new IBAnchorHandler();
    new IBClipboard();
    new IBCookieBot();
    new IBContactOverlayHandler();

    if (document.querySelector('.shariff')) {
        new Shariff($('.shariff'));
    }
    ($() as any).jetmenu();

    if (document.querySelector('.jetmenu')) {
        // new IBMenu(document.querySelector('.jetmenu') as HTMLElement, { indicator: false });
    }

    if (document.querySelector('.ib-startpage-slider')) {
        new IBStartPageSlider(document.querySelector('.ib-startpage-slider') as HTMLElement);
    }

    $('.twoColLayout .ib-news-slider').slick({
        prevArrow : $('#newsPrevButton'),
        nextArrow : $('#newsNextButton'),
        slidesToShow : 2,
        dots: true,
        responsive : [
        {
            breakpoint : 900,
            settings : {
                slidesToShow : 2,
                slidesToScroll : 2
            }
        }, {
            breakpoint : 480,
            settings : {
                slidesToShow : 1,
                slidesToScroll : 1
            }
        }]
    });
//    Foundation.reInit([ 'equalizer' ]);
});
