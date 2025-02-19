'use strict'

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/foundation.scss';
import '../Css/app.scss';
import './config/readSpeakerConfig';
import $ from 'jquery';

(window as any).jQuery = (window as any).$ = $;
import 'friendly-challenge/widget';
import Foundation from 'foundation-sites';
import Headroom from 'headroom.js';
import Shariff from 'shariff';
import 'slick-carousel';

import IBAnchorHandler from './components/IBAnchorHandler';
import IBClipboard from './components/IBClipboard';
import IBContactOverlayHandler from './components/IBContactOverlayHandler';
import IBCookieBot from './components/IBCookieBot';
import IBEmailProtection from './components/IBEmailProtection';
import IBFloatingActionButton from './components/IBFloatingActionButton';
import IBHeaderSearchBar from './components/IBHeaderSearchBar';
import IBMenu from './components/IBMenu';
import IBNewsCategoryFilter from './components/IBNewsCategoryFilter';
import IBNewsSlider from './components/IBNewsSlider';
import IBSliderLoader from './components/IBSliderLoader';
import IBStartpageNewsSlider from './components/IBStartpageNewsSlider';
import IBStartPageSlider from './components/IBStartPageSlider';
import IBTabsBar from './components/IBTabsBar';
import IBVideoSlider from './components/IBVideoSlider';

import './components/matomoEvents';

import './components/foundation-accordion.js';

if (Foundation) {
  // if `Foundation` is left as an unused variable webpack will exclude it from the build output;
  // therefore, any expression that uses it will work.
}

$(function() {
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
    new IBContactOverlayHandler();
    new IBCookieBot();
    new IBFloatingActionButton();
    new IBHeaderSearchBar();
    new IBNewsCategoryFilter();
    new IBSliderLoader();
    new IBTabsBar();

    if (document.querySelectorAll('.ibOEmail')) {
        new IBEmailProtection(document.querySelectorAll('.ibOEmail'));
    }

    if (document.querySelectorAll('.shariff').length > 0) {
        new Shariff(document.querySelectorAll('.shariff'));
    }

    if (document.querySelector('.jetmenu')) {
        new IBMenu(document.querySelector('.jetmenu') as HTMLElement, { indicator: false });
    }

    if (document.querySelector('.ib-startpage-slider')) {
        new IBStartPageSlider(document.querySelector('.ib-startpage-slider') as HTMLElement);
    }

    if (document.querySelector('.startPage .ib-news-slider')) {
        new IBStartpageNewsSlider(document.querySelector('.startPage .ib-news-slider')!);
    }

    if (document.querySelectorAll('.twoColLayout .ib-news-slider').length > 0) {
        new IBNewsSlider(document.querySelectorAll('.twoColLayout .ib-news-slider'));
    }

    if (document.querySelector('#ibVideoSlider')) {
        new IBVideoSlider(document.querySelector('#ibVideoSlider')!);
    }

    //    Foundation.reInit([ 'equalizer' ]);
});
