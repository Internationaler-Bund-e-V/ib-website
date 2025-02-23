import $ from 'jquery';
import 'slick-carousel';

class IBJobsSlider {
    protected element: HTMLElement|null;
    protected sliderElement: HTMLElement|null;

    constructor(element: Element | HTMLElement, options = {}) {
        this.element = element as HTMLElement;
        this.sliderElement = this.element.querySelector('.ib-jobs-slider') as HTMLElement;

        let preset: JQuerySlickOptions = {
            prevArrow: this.element.querySelector('.ib-bubble-slider-control-prev')!,
            nextArrow: this.element.querySelector('.ib-bubble-slider-control-next')!,
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
        };
        $.extend(true, preset, options);

        $(this.sliderElement).slick(preset);
    }
}

export default IBJobsSlider;
