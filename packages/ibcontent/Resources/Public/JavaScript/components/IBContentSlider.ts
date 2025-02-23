import $ from 'jquery';
import 'slick-carousel';

class IBContentSlider
{
    protected element: HTMLElement|null;
    protected sliderElement: HTMLElement|null;
    protected sliderContainer: HTMLElement | null;

    constructor(element: Element | HTMLElement, options = {}) {
        this.element = element as HTMLElement;
        this.element.style.display = 'block';
        this.sliderElement = this.element.querySelector('.slides') as HTMLElement;
        this.sliderContainer = this.element.querySelector('.contentSliderContainer') as HTMLElement;

        let preset: JQuerySlickOptions = {
            prevArrow: this.element.querySelector('.ib-bubble-slider-control-prev')!,
            nextArrow: this.element.querySelector('.ib-bubble-slider-control-next')!,
            slidesToShow: 1,
            dots: true,
            responsive: [
                {
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
        }
        $.extend(true, preset, options);

        $(this.sliderElement).on('init', () => {
            let loaderID = this.sliderElement!.dataset.loaderid;
            $('#' + loaderID).hide();
            $(this.sliderContainer!).removeClass('loading');
        });

        $(this.sliderElement).slick(preset);
    }
}

export default IBContentSlider;
