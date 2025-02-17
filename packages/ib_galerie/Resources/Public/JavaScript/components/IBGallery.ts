/**
*/
import $ from 'jquery';
import Masonry from 'masonry-layout';
import 'slick-carousel';

class IBGallery {
    constructor(elements:any) {
        let currentGallery:string;
        let mainStage:JQuery<HTMLElement>;
        let navStage:JQuery<HTMLElement>;
        let gotoSlide:string;
        let mainID:string = "#ext-ibg-main-";
        let navID:string = "#ext-ibg-nav-";
        let overlayContainer:JQuery<HTMLElement> = $('#ext-ibg-overlay-container-0');
        let overlay:JQuery<HTMLElement> = $('#ext-ibg-overlay-0');

        overlayContainer.appendTo('html');
        overlay.appendTo('html');

        $(elements).on('click', function () {

            currentGallery = $(this).data('galleryid');
            mainStage = $(mainID + currentGallery);
            navStage = $(navID + currentGallery);
            gotoSlide = $(this).data('gotoslide');

            $("#ext-ibgc-" + currentGallery + " .dummyContainer").appendTo(overlay).show();

            overlayContainer.show();
            overlay.show();


            navStage.slick({
                asNavFor: mainID + currentGallery,
                focusOnSelect: true,
                dots: false,
                slidesToShow: 10,
                arrows: false,
                infinite: true,
                initialSlide: parseInt(gotoSlide),

            });

            mainStage.slick({
                prevArrow: $('#ext-ibg-nav-left-0'),
                nextArrow: $('#ext-ibg-nav-right-0'),
                asNavFor: navID + currentGallery,
                initialSlide: parseInt(gotoSlide),
                dots: true,
                infinite: true,
                rows: 0
            });





            $('#ext-ibg-close-0').unbind();
            $('#ext-ibg-close-0').on('click', function () {
                overlayContainer.hide();
                overlay.hide();
                mainStage.slick('unslick');
                navStage.slick('unslick');
                $('#ext-ibg-overlay-0 .dummyContainer').appendTo("#ext-ibgc-" + currentGallery);
            });



        });


        $('.ext-ib-slider-main-stage-mobile').slick({
            dots: true,
            slidesToShow: 1,
            arrows: false
        });


        document.querySelectorAll('.grid').forEach((gridElement:Element) => {
            new Masonry( gridElement, {
                percentPosition: true,
                itemSelector: '.grid-item'
            });

        });
    }
}

export default IBGallery;
