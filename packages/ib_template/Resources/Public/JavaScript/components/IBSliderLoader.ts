import $ from 'jquery';

class IBSliderLoader
{
    constructor() {
        //startpage slider
        this.ibSliderLoader($('.ib-startpage-slider'), $('.ib-slider-container'));
        //news
        this.ibSliderLoader($('.ib-news-slider'), $('.ibNewsSliderContainer'));
        //location/product header slider
        this.ibSliderLoader($('.ib-startpageslider'), $('.ibLocSliderContainer'));
        //location/product video slider
        this.ibSliderLoader($('#ibVideoSlider'), $('.ibLocVideoContainer'));
        //location/product video slider
        this.ibSliderLoader($('.ib-dbGallery-slider'), $('.ibLocGalerieContainer'));
        //bubble slider
        this.ibSliderLoader($('.ib-bubble-slider'), $('.ibBubbleSliderContainer'));
        //content slilder
        this.ibSliderLoader($('.ib-contentslider-slider'), $('.contentSliderContainer'));

    }
    ibSliderLoader(slider:any, sliderContainer:any) {

        slider.each((index:number, element:any) => {
            $(element).on('init', function (event, slick) {
                var loaderID = $(this).data('loaderid');
                $('#' + loaderID).hide();
                $(sliderContainer[index]).removeClass('loading');

            })
        })


    }

}

export default IBSliderLoader;
