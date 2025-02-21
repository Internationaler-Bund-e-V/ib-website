$(document).ready(function() {
    $('.startPage .ib-jobs-slider').slick({
        prevArrow: $('#jobsPrevButton'),
        nextArrow: $('#jobsNextButton'),
        slidesToShow: 3,
        dots: true,
        responsive: [{
            breakpoint: 900,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        }, {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });
    $('.twoColLayout .ib-jobs-slider').slick({
            prevArrow : $('#jobsPrevButton'),
            nextArrow : $('#jobsNextButton'),
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
