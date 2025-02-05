import $ from 'jquery';

//startpage slider
ibSliderLoader($('.ib-startpage-slider'), $('.ib-slider-container'));
//news
ibSliderLoader($('.ib-news-slider'), $('.ibNewsSliderContainer'));
//location/product header slider
ibSliderLoader($('.ib-startpageslider'), $('.ibLocSliderContainer'));
//location/product video slider
ibSliderLoader($('#ibVideoSlider'), $('.ibLocVideoContainer'));
//location/product video slider
ibSliderLoader($('.ib-dbGallery-slider'), $('.ibLocGalerieContainer'));
//bubble slider
ibSliderLoader($('.ib-bubble-slider'), $('.ibBubbleSliderContainer'));
//content slilder
ibSliderLoader($('.ib-contentslider-slider'), $('.contentSliderContainer'));


function ibSliderLoader(slider, sliderContainer) {

  slider.each(function (index) {
    $(this).on('init', function (event, slick) {
      var loaderID = $(this).data('loaderid');
      $('#' + loaderID).hide();
      $(sliderContainer[index]).removeClass('loading');

    })
  })


}
