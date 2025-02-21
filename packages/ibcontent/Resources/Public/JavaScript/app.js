// import '../Css/app.scss';

import $ from 'jquery';

require('./components/db.js');
require('./components/webReader.js');


$(() => {
    $('.ib-contentslider-slider').each(function(){
        var sliderID = $(this).data('sliderid');
        $(this).slick({
            prevArrow : $("#contentsliderPrevButton-"+sliderID),
            nextArrow : $("#contentsliderNextButton-"+sliderID),
            slidesToShow : 1,
            dots: true,
            responsive : [
                {
                    breakpoint : 900,
                    settings : {
                        slidesToShow : 1,
                        slidesToScroll : 1
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
