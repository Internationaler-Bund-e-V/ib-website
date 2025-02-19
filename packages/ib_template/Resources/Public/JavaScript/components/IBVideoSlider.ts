import $ from 'jquery';
import 'slick-carousel';

class IBVideoSlider
{
    constructor(element:Element|HTMLElement|JQuery<HTMLElement>) {
        $(element).slick({
            dots: true,
            slidesToShow: 1,
            appendDots: $('#ibVideoSliderDots'),
            prevArrow: $('#ibVideoSliderPevious'),
            nextArrow: $('#ibVideoSliderNext')
        });

    }
}

export default IBVideoSlider;
