$(document).ready(function () {


  $('.ib-startpage-slider').slick({
    prevArrow: $('#startpagesliderPrevButton'),
    nextArrow: $('#startpagesliderNextButton'),
    lazyLoad: 'progressive',
    slidesToShow: 1,
    dots: true,
    autoplay: true,
    autoplaySpeed: 6000,
    responsive: [{
      breakpoint: 900,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }, {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }]
  });

  $('#ib-partial-startpageslider div.slick-arrow').click(function (e) {
    $('.ib-startpage-slider').slick('slickPause');
  });

  //check slide count
  if ($('#ib-partial-startpageslider .slick-slide:not(.slick-cloned)').length <= 1) {
    $('#ib-slider-controls-toggle').css('display', 'none');
  }

  $('#ib-slider-controls-toggle').click(function (e) {

    if ($(this).hasClass('fa-play-circle')) {
      $('.ib-startpage-slider').slick('slickPlay')
      $(this).removeClass('fa-play-circle');
    } else {
      $('.ib-startpage-slider').slick('slickPause')
      $(this).addClass('fa-play-circle');
    }
  });



});