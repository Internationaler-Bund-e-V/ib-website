import $ from 'jquery';

$(document).ready(function() {
    $('.ib-bubble-slider').slick({
        centerMode: true,
        centerPadding: 0,
        prevArrow: $('#prevButton'),
        nextArrow: $('#nextButton'),
        asNavFor: '.ib-bubble-slider-main-stage',
        focusOnSelect: true,
        dots: true,
        slidesToShow: 3,
        responsive: [{
            breakpoint: 900,
            settings: {
                arrows: false,
                centerMode: true,
                centerPadding: '40px',
                slidesToShow: 1
            }
        }, {
            breakpoint: 641,
            settings: {
                arrows: false,
                centerMode: true,
                centerPadding: '20px',
                slidesToShow: 1
            }
        }]
    });
    $('.ib-bubble-slider-main-stage').slick({
        arrows: false,
        asNavFor: '.ib-bubble-slider',
    });
});
