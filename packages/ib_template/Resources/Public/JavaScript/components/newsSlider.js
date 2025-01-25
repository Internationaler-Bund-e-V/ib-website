$(document).ready(function () {
    
    /**
     * initialize startbpage newsslider
     */
    var ibNewsSliders = $('.startPage .ib-news-slider');
    if (ibNewsSliders.length > 0) {
        var slidesToShow = 3;
        var arrows = true;
        var childElements = $('.startPage .ib-news-slider').children().length;
        ibNewsSliders.each(function (index) {
            var sliderID = $(this).data('sliderid');
            $(this).slick({
                prevArrow: $('.ibSliderPrev-' + sliderID),
                nextArrow: $('.ibSliderNext-' + sliderID),
                slidesToShow: slidesToShow,
                arrows: arrows,
                responsive: [
                    {
                        breakpoint: 900,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            arrows: true
                        }
                    }, {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            arrows: true
                        }
                    }]
            });
        });
    }

});