$(document).ready(function () {  
  // ---------------------------------------------
  // headroom for detection of scroll behavior
  // ---------------------------------------------
  $("body").headroom({
    "offset": 100,
    "tolerance": 3,
    "classes": {
      "initial": "headroom",
      "pinned": "headroom--expanded",
      "unpinned": "headroom--collapsed"
    }
  });

  $('.ib-content-module .download img, .ib-content-module .internal-link img, .ib-content-module .internal-link-new-window img, .ib-content-module .external-link img, .ib-content-module .external-link-new-window img').parent()
    .removeClass('download internal-link internal-link-new-window external-link external-link-new-window');



  Foundation.reInit('equalizer');



});

$(window).load(function () {
  Foundation.reInit('equalizer');
});