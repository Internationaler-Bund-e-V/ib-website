import $ from 'jquery';
import 'slick-carousel';

class IBStartPageSlider {
    protected element: HTMLElement | null;
    protected sliderElement: HTMLElement | null;

    constructor(element: Element|HTMLElement, options = {}) {
        this.element = element as HTMLElement;
        this.element.style.display = 'block';
        this.sliderElement = this.element.querySelector('.slides') as HTMLElement;

        let preset: JQuerySlickOptions = {
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
        };
        $.extend(true, preset, options);

        $(this.sliderElement).slick(preset);

        $('#ib-slider-controls-toggle').on('click', (event) => {

            if ($(event.target).hasClass('fa-play-circle')) {
                $(this.sliderElement!).slick('slickPlay')
                $(event.target).removeClass('fa-play-circle');
                $(event.target).addClass('fa-pause-circle');
            } else {
                $(this.sliderElement!).slick('slickPause')
                $(event.target).removeClass('fa-pause-circle');
                $(event.target).addClass('fa-play-circle');
            }
        });

        $('#ib-partial-startpageslider div.slick-arrow').on('click', () => {
            $(this.sliderElement!).slick('slickPause');
        });
        if ($('#ib-partial-startpageslider .slick-slide:not(.slick-cloned)').length <= 1) {
            $('#ib-slider-controls-toggle').css('display', 'none');
        }


    }
}

export default IBStartPageSlider;
