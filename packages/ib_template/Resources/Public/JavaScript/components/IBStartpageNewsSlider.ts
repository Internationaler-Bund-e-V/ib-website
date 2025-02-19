import $ from 'jquery';

class IBStartpageNewsSlider {
    constructor(elements:Element|HTMLElement|JQuery<HTMLElement>) {
        const slidesToShow = 3;
        const arrows = true;
        const childElements = $(elements).children().length;

        $(elements).each((index:number, element:Element|HTMLElement) => {
            const sliderID = $(element).data('sliderid');
            $(element).slick({
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
}

export default IBStartpageNewsSlider;
