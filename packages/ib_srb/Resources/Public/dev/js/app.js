$(document).ready(function () {
  $(document).foundation();

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