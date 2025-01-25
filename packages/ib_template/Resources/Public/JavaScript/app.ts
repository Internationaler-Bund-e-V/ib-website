'use strict'

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/foundation.scss';
import '../Css/app.scss';
import './config/readSpeakerConfig';

import 'friendly-challenge/widget';
import Foundation from 'foundation-sites';
import Headroom from 'headroom.js';
const Shariff = require('shariff');
const $ = require('jquery');

require('./components/anchor.js')
require('./components/contactOverlay.js')
require('./components/cookie.js')
require('./components/customCookiebot.js')
require('./components/fab.js')
require('./components/foundation-accordion.js')
require('./components/headerSearchBar.js')
require('./components/ibClipboard.js')
require('./components/matomoEvents.js')
require('./components/nav-tab-detection.js')
require('./components/newsCategory.js')
require('./components/newsSlider.js')
require('./components/obfuscateEmail.js')
require('./components/sliderLoader.js')
require('./components/social-content-sticky-nav.js')
require('./components/tabsBar.js')
require('./components/videoSlider.js')
// 'node_modules/clipboard/dist/clipboard.min.js',
// 'node_modules/motion-ui/dist/motion-ui.min.js'


require('select2');
require('slick-carousel');
require('./jetmenu.js');
$(document).ready(function () {  
    const options = {
        'offset': 100,
        'tolerance': 3,
        'classes': {
            'initial': 'headroom',
            'pinned': 'headroom--expanded',
            'unpinned': 'headroom--collapsed'
        }
    };
  
    const headroom = new Headroom(document.body, options);
    headroom.init();
  
    $('.ib-content-module .download img, .ib-content-module .internal-link img, .ib-content-module .internal-link-new-window img, .ib-content-module .external-link img, .ib-content-module .external-link-new-window img').parent()
      .removeClass('download internal-link internal-link-new-window external-link external-link-new-window');
  
    Foundation.reInit([ 'equalizer' ]);

    new Shariff($('.shariff'));

    $(document).foundation();
    $().jetmenu({
        indicator: false
    });
    
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
});



/*
'node_modules/foundation-sites/js/foundation.core.js',
'node_modules/foundation-sites/js/foundation.abide.js',
'node_modules/foundation-sites/js/foundation.tabs.js',
'node_modules/foundation-sites/js/foundation.reveal.js',
'node_modules/foundation-sites/js/foundation.util.box.js',
'node_modules/foundation-sites/js/foundation.util.mediaQuery.js',
'node_modules/foundation-sites/js/foundation.util.imageLoader.js',
'node_modules/foundation-sites/js/foundation.util.triggers.js',
'node_modules/foundation-sites/js/foundation.util.touch.js',
'node_modules/foundation-sites/js/foundation.equalizer.js',
'node_modules/foundation-sites/js/foundation.util.keyboard.js',
'node_modules/foundation-sites/js/foundation.util.motion.js',
'node_modules/foundation-sites/js/foundation.util.timerAndImageLoader.js',

'node_modules/jquery.appear/jquery.appear.js',
'node_modules/slick-carousel/slick/slick.js',

'node_modules/select2/dist/js/select2.full.js',
'node_modules/clipboard/dist/clipboard.min.js',
'node_modules/motion-ui/dist/motion-ui.min.js'

ibcontent/Resources/Public/js/startPageSlider.js


}

*/