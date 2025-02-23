import $ from 'jquery';
import 'slick-carousel';

class IBContentSlider
{
    protected element: HTMLElement|null;
    protected sliderElement: HTMLElement | null;
    protected sliderContainer: HTMLElement | null;

    constructor(element: Element | HTMLElement, options = {}) {
        this.element = element as HTMLElement;
        this.element.style.display = 'block';
        this.sliderElement = this.element.querySelector('.slides') as HTMLElement;
        this.sliderContainer = this.element.querySelector('.ibBubbleSliderContainer') as HTMLElement;

        let preset: JQuerySlickOptions = {
            centerMode: true,
            centerPadding: '0',
            prevArrow: this.element.querySelector('.ib-bubble-slider-control-prev')!,
            nextArrow: this.element.querySelector('.ib-bubble-slider-control-next')!,
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
        };

        $.extend(true, preset, options);

        $(this.sliderElement).on('init', () => {
            let loaderID = this.sliderElement!.dataset.loaderid;
            $('#' + loaderID).hide();
            $(this.sliderContainer!).removeClass('loading');
        });

        $(this.sliderElement).slick(preset);

        this.element.querySelectorAll('.ib-bubble-slider-main-stage').forEach((element) => {
            $(element).slick({
                arrows: false,
                asNavFor: '.ib-bubble-slider',
            });
        });
    }
}

export default IBContentSlider;
