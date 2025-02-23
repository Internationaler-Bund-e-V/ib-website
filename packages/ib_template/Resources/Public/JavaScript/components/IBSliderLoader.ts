import $ from 'jquery';

class IBSliderLoader
{
    constructor() {
        //news
        this.ibSliderLoader($('.ib-news-slider'), $('.ibNewsSliderContainer'));
        //location/product video slider
        this.ibSliderLoader($('#ibVideoSlider'), $('.ibLocVideoContainer'));
        //location/product video slider
        this.ibSliderLoader($('.ib-dbGallery-slider'), $('.ibLocGalerieContainer'));

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
