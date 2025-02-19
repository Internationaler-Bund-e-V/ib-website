import $ from 'jquery';

class IBNewsSlider {
    constructor(element:NodeListOf<Element>|HTMLElement|JQuery<HTMLElement>) {
    $(element).slick({
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

    }
}

export default IBNewsSlider;
